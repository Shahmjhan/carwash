<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;

Route::get('/', fn()=>redirect()->route('dashboard'));
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'showLogin'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->name('login.perform');
});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function(){
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
 Route::resource('customers',CustomerController::class);
 Route::resource('vehicles',VehicleController::class);
 Route::resource('appointments',AppointmentController::class);
 Route::get('/jobs/board',[JobController::class,'board'])->name('jobs.board');
 Route::resource('jobs',JobController::class);
 Route::get('/jobs/{job}/inspection',[InspectionController::class,'edit'])->name('jobs.inspection.edit');
 Route::post('/jobs/{job}/inspection',[InspectionController::class,'update'])->name('jobs.inspection.update');
 Route::post('/jobs/{job}/status',[JobController::class,'status'])->name('jobs.status');
 Route::post('/jobs/{job}/additional-work',[JobController::class,'additionalWork'])->name('jobs.additional-work');
 Route::post('/jobs/{job}/approve',[JobController::class,'approve'])->name('jobs.approve');
 Route::post('/jobs/{job}/consume-part',[JobController::class,'consumePart'])->name('jobs.consume-part');
 Route::resource('inventory',InventoryController::class)->except(['show']);
 Route::post('/inventory/{product}/adjust',[InventoryController::class,'adjust'])->name('inventory.adjust');
 Route::resource('invoices',InvoiceController::class)->only(['index','show']);
 Route::post('/invoices/{invoice}/pay',[InvoiceController::class,'pay'])->name('invoices.pay');
 Route::get('/reports',[ReportController::class,'index'])->name('reports');
});
