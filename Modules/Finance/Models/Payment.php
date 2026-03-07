<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Finance\Enums\PaymentStatus;

// use Modules\Finance\Database\Factories\PaymentFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_method',
        'payment_proof_path',
        'transaction_id',
        'status',
        'admin_notes',
    ];

    protected $cast = [
        'status' => PaymentStatus::class,
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getPaymentProofUrlAttribute()
    {
        return $this->payment_proof_path ? url('/storage/' . $this->payment_proof_path) : null;
    }
}
