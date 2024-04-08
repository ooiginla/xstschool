<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestLog extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'client_ref','client_request'];
  
}