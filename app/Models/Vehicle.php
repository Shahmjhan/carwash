<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class Vehicle extends Model {use HasFactory; protected $guarded=[]; public function customer(){return $this->belongsTo(Customer::class);} public function jobs(){return $this->hasMany(Job::class);} }
