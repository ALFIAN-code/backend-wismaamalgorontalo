<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Rental\Models\Lease;

// use Modules\Finance\Database\Factories\InvoiceFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'invoice_number',
        'amount',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => InvoiceStatus::class,
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class, 'lease_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
