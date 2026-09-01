<?php

namespace App\Http\Controllers;

use App\Models\{Customer, Vehicle, Job, Service};
use App\Enums\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceptionController extends Controller
{
    public function index()
    {
        return view('reception.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        // Only search by vehicle registration number
        $vehicles = Vehicle::where('registration_number', 'like', "%{$query}%")
            ->with(['customer:id,full_name'])
            ->limit(10)
            ->get()
            ->map(function ($vehicle) {
                return [
                    'vehicle_id' => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'category' => $vehicle->category,
                    'customer_id' => $vehicle->customer_id,
                    'customer_name' => $vehicle->customer->full_name ?? 'Unknown',
                    'image' => $vehicle->image,
                    'image_url' => $vehicle->image
                        ? asset('storage/' . $vehicle->image)
                        : null,
                ];
            });

        return response()->json([
            'found' => $vehicles->isNotEmpty(),
            'vehicles' => $vehicles,
        ]);
    }

    public function createJob(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'service_ids' => 'required|array',
                'service_ids.*' => 'exists:services,id',
                'notes' => 'nullable|string',
                'vehicle_image' => 'nullable|image|max:5120', // max 5MB
            ]);

            // If a new vehicle image was uploaded from the job form, save it
            if ($request->hasFile('vehicle_image')) {
                $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

                // Delete old image if it exists
                if ($vehicle->image) {
                    Storage::disk('public')->delete($vehicle->image);
                }

                $path = $request->file('vehicle_image')->store('vehicles', 'public');
                $vehicle->update(['image' => $path]);
            }

            $job = Job::create([
                'business_id' => auth()->user()->business_id,
                'branch_id' => auth()->user()->branch_id,
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'status' => JobStatus::CHECKED_IN,
                'checked_in_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Attach services
            foreach ($validated['service_ids'] as $serviceId) {
                $service = Service::find($serviceId);
                $job->services()->create([
                    'service_id' => $serviceId,
                    'name_snapshot' => $service->name,
                    'unit_price' => $service->base_price,
                    'quantity' => 1,
                ]);
            }

            return response()->json([
                'success' => true,
                'job_id' => $job->id,
                'job_number' => $job->job_number,
                'redirect' => route('jobs.show', $job),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getServices()
    {
        $services = Service::where('business_id', auth()->user()->business_id)
            ->with('category')
            ->orderBy('name')
            ->get();

        return response()->json($services);
    }
}