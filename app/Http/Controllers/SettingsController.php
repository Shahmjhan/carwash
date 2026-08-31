<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        $settings = $business->getBillingSettings();
        
        return view('settings.billing', compact('settings'));
    }

    public function updateBilling(Request $request)
    {
        $business = auth()->user()->business;
        if (!$business) {
            return back()->with('error', 'No business associated with your account.');
        }
        
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:50',
            'invoice_prefix' => 'required|string|max:10',
            'receipt_prefix' => 'required|string|max:10',
            'footer_text' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'reception_background' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'default_format' => 'required|in:a4,thermal',
        ]);

        // Handle logo upload
        $currentSettings = $business->getBillingSettings();
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logos'), $filename);
            $validated['logo_path'] = 'uploads/logos/' . $filename;
        } elseif ($request->has('logo_path') && empty($request->input('logo_path'))) {
            // Logo was removed
            $validated['logo_path'] = '';
        } else {
            // Keep existing logo
            $validated['logo_path'] = $currentSettings['logo_path'] ?? '';
        }

        // Handle reception background upload
        if ($request->hasFile('reception_background')) {
            $file = $request->file('reception_background');
            $filename = time() . '_bg_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/backgrounds'), $filename);
            $validated['reception_background_image'] = 'uploads/backgrounds/' . $filename;
        } elseif ($request->has('reception_background_image') && empty($request->input('reception_background_image'))) {
            // Background was removed
            $validated['reception_background_image'] = '';
        } else {
            // Keep existing background
            $validated['reception_background_image'] = $currentSettings['reception_background_image'] ?? '';
        }

        // Handle checkbox values manually - if not present, set to false
        $validated['a4_enabled'] = $request->has('a4_enabled') ? true : false;
        $validated['thermal_enabled'] = $request->has('thermal_enabled') ? true : false;

        $business->saveBillingSettings($validated);

        return back()->with('success', 'Billing settings updated successfully.');
    }
}