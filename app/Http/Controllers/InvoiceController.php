<?php namespace App\Http\Controllers; use App\Models\{Invoice,Payment}; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class InvoiceController extends Controller {public function index(){ $invoices=Invoice::with('customer')->latest()->paginate(20);return view('invoices.index',compact('invoices'));} public function show(Invoice $invoice){$invoice->load('customer','job.vehicle','items','payments');return view('invoices.show',compact('invoice'));}    public function printInvoice(Invoice $invoice,$format='a4'){
        // Reload invoice from database to get latest values including discount
        $invoice = Invoice::with('customer','job.vehicle','items','payments')->find($invoice->id);
        
        // Log for debugging
        \Log::info('Print Invoice', [
            'invoice_id' => $invoice->id,
            'subtotal' => $invoice->subtotal,
            'discount' => $invoice->discount,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'paid' => $invoice->paid,
            'balance' => $invoice->balance,
        ]);
        
        // Get the last payment transaction details from job status history
        $currentPaymentAmount = 0;
        $currentBalance = 0;
        
        if ($invoice->job) {
            $lastPayment = $invoice->job->statusHistory()
                ->where('to_status', \App\Enums\JobStatus::PAID->value)
                ->latest()
                ->first();

            if ($lastPayment && $lastPayment->reason) {
                if (preg_match('/Amount received: Rs\. ([\d.]+)/', $lastPayment->reason, $matches)) {
                    $currentPaymentAmount = floatval($matches[1]);
                }
                if (preg_match('/Balance: Rs\. ([\d.]+)/', $lastPayment->reason, $matches)) {
                    $currentBalance = floatval($matches[1]);
                }
            }
        }
        
        $view=$format==='thermal'?'invoices.print.thermal':'invoices.print.a4';
        return view($view,compact('invoice','currentPaymentAmount','currentBalance'));
    } public function pay(Request $r,Invoice $invoice){$d=$r->validate(['amount'=>'required|numeric|min:.01','method'=>'required']);DB::transaction(function()use($invoice,$d){if($d['amount']>$invoice->balance)abort(422,'Payment exceeds balance.');Payment::create(['invoice_id'=>$invoice->id,'method'=>$d['method'],'amount'=>$d['amount'],'received_by'=>auth()->id()]);$invoice->paid+=$d['amount'];$invoice->balance=$invoice->total-$invoice->paid;$invoice->status=$invoice->balance<=0?'paid':'partially_paid';$invoice->save();});return back()->with('success','Payment recorded.');}}
