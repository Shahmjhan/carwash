<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Enums\JobStatus;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    public function index()
    {
        $readyForPayment = Job::with(['customer', 'vehicle', 'invoice'])
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cashier.index', compact('readyForPayment'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $jobs = Job::with(['customer', 'vehicle', 'invoice'])
            ->where('status', JobStatus::READY_FOR_PAYMENT->value)
            ->whereHas('vehicle', function ($q) use ($query) {
                $q->where('registration_number', 'like', '%' . $query . '%');
            })
            ->orWhereHas('customer', function ($q) use ($query) {
                $q->where('full_name', 'like', '%' . $query . '%');
            })
            ->orWhere('job_number', 'like', '%' . $query . '%')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cashier.search', compact('jobs', 'query'));
    }

    public function payment(Job $job)
    {
        $job->load(['customer', 'vehicle', 'services.service', 'parts.product', 'invoice']);
        
        return view('cashier.payment', compact('job'));
    }

    public function processPayment(Request $request, Job $job)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount_received' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:none,apply,amount,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_apply_to' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $amountReceived = $request->amount_received;
        $subtotal = $job->invoice ? $job->invoice->subtotal : 0;
        $tax = $job->invoice ? $job->invoice->tax : 0;
        
        // Calculate discount based on the new discount structure
        $discountAmount = 0;
        if ($request->filled('discount_type') && $request->discount_type !== 'none') {
            $discountValue = $request->discount_value ?? 0;
            $discountApplyTo = $request->discount_apply_to ?? 'total';
            
            // Get services and parts totals for calculations
            $servicesTotal = $job->services->sum(fn($s) => $s->unit_price * $s->quantity);
            $partsTotal = $job->parts->sum(fn($p) => $p->unit_price * $p->quantity);
            
            if ($request->discount_type === 'apply') {
                // Apply Discount - full discount on selected category
                if ($discountApplyTo === 'services') {
                    $discountAmount = $servicesTotal;
                } elseif ($discountApplyTo === 'parts') {
                    $discountAmount = $partsTotal;
                } elseif ($discountApplyTo === 'individual_services' || $discountApplyTo === 'individual_parts') {
                    // For individual discounts, the discount_value is already the calculated total discount
                    $discountAmount = floatval($discountValue);
                }
            } elseif ($request->discount_type === 'amount' || $request->discount_type === 'percentage') {
                // Fixed Amount or Percentage - calculate based on apply_to option
                $discountBase = $subtotal; // Default to subtotal (total amount before tax)
                
                if ($discountApplyTo === 'services') {
                    $discountBase = $servicesTotal;
                } elseif ($discountApplyTo === 'parts') {
                    $discountBase = $partsTotal;
                }
                
                if ($request->discount_type === 'amount') {
                    $discountAmount = min($discountValue, $discountBase);
                } elseif ($request->discount_type === 'percentage') {
                    $discountAmount = ($discountBase * $discountValue) / 100;
                }
            }
        }
        
        $finalTotal = ($subtotal - $discountAmount) + $tax;
        $balance = $amountReceived - $finalTotal;

        // Update invoice with discount and payment details
        if ($job->invoice) {
            $totalPaid = $job->invoice->paid + $amountReceived;
            
            // Log for debugging
            \Log::info('Payment Processing', [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
                'amount_received' => $amountReceived,
                'total_paid' => $totalPaid,
                'balance' => $finalTotal - $totalPaid,
            ]);
            
            $job->invoice->update([
                'discount' => $discountAmount,
                'total' => $finalTotal,
                'paid' => $totalPaid,
                'balance' => $finalTotal - $totalPaid,
            ]);
            
            // Force reload from database to ensure we get the updated values
            $job->invoice->refresh();
        }

        // Only transition if not already paid
        if ($job->status !== \App\Enums\JobStatus::PAID) {
            $job->transitionTo(\App\Enums\JobStatus::PAID, auth()->user(), "Payment processed via {$request->payment_method}. Amount received: Rs. {$amountReceived}, Discount: Rs. {$discountAmount}, Balance: Rs. {$balance}");
        }

        return redirect()->route('cashier.print-options', $job)->with('success', 'Payment processed successfully. Balance to return: Rs. ' . number_format($balance, 2));
    }

    public function printOptions(Job $job)
    {
        // Reload job and invoice from database to get latest values including discount
        $job = Job::with(['customer', 'vehicle', 'services.service', 'parts.product', 'invoice'])->find($job->id);
        
        // Log for debugging
        if ($job->invoice) {
            \Log::info('Print Options', [
                'job_id' => $job->id,
                'invoice_id' => $job->invoice->id,
                'subtotal' => $job->invoice->subtotal,
                'discount' => $job->invoice->discount,
                'tax' => $job->invoice->tax,
                'total' => $job->invoice->total,
                'paid' => $job->invoice->paid,
                'balance' => $job->invoice->balance,
            ]);
        }
        
        if (!$job->invoice) {
            return redirect()->route('cashier.index')->with('error', 'No invoice found for this job.');
        }

        // Get the last payment transaction details from status history
        $lastPayment = $job->statusHistory()
            ->where('to_status', \App\Enums\JobStatus::PAID->value)
            ->latest()
            ->first();

        $currentPaymentAmount = 0;
        $currentBalance = 0;

        if ($lastPayment && $lastPayment->reason) {
            // Parse the reason to extract payment details
            if (preg_match('/Amount received: Rs\. ([\d.]+)/', $lastPayment->reason, $matches)) {
                $currentPaymentAmount = floatval($matches[1]);
            }
            if (preg_match('/Balance: Rs\. ([\d.]+)/', $lastPayment->reason, $matches)) {
                $currentBalance = floatval($matches[1]);
            }
        }

        return view('cashier.print-options', compact('job', 'currentPaymentAmount', 'currentBalance'));
    }
}
