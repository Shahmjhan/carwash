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
            'discount_type' => 'nullable|in:none,amount,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string',
        ]);

        $amountReceived = $request->amount_received;
        $totalDue = $job->invoice ? $job->invoice->total : 0;
        
        // Calculate discount
        $discountAmount = 0;
        if ($request->filled('discount_type') && $request->discount_type !== 'none') {
            $discountValue = $request->discount_value ?? 0;
            if ($request->discount_type === 'amount') {
                $discountAmount = min($discountValue, $totalDue);
            } elseif ($request->discount_type === 'percentage') {
                $discountAmount = ($totalDue * $discountValue) / 100;
            }
        }
        
        $finalTotal = $totalDue - $discountAmount;
        $balance = $amountReceived - $finalTotal;

        // Update invoice with discount and payment details
        if ($job->invoice) {
            $totalPaid = $job->invoice->paid + $amountReceived;
            $job->invoice->update([
                'discount' => $discountAmount,
                'total' => $finalTotal,
                'paid' => $totalPaid,
                'balance' => $finalTotal - $totalPaid,
            ]);
        }

        // Only transition if not already paid
        if ($job->status !== \App\Enums\JobStatus::PAID) {
            $job->transitionTo(\App\Enums\JobStatus::PAID, auth()->user(), "Payment processed via {$request->payment_method}. Amount received: Rs. {$amountReceived}, Discount: Rs. {$discountAmount}, Balance: Rs. {$balance}");
        }

        return redirect()->route('cashier.print-options', $job)->with('success', 'Payment processed successfully. Balance to return: Rs. ' . number_format($balance, 2));
    }

    public function printOptions(Job $job)
    {
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
