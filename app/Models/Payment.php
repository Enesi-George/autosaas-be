<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'email',
        'amount',
        'status',
        'purpose',
        'gateway_response',
        'user_id',
        'paid_at'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAmountInNairaAttribute()
    {
        return $this->amount / 100; // Convert kobo to naira
    }
}