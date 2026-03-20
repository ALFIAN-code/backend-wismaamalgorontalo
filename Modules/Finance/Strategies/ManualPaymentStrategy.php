<?php

namespace Modules\Finance\Strategies;

use Modules\Finance\Contracts\PaymentStrategyInterface;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

class ManualPaymentStrategy implements PaymentStrategyInterface
{
    public function process(Invoice $invoice, array $data): Payment
    {
        $file = $data['payment_proof'];
        $path = $file->store('payments/manual', 'public');

        return Payment::create([
            'invoice_id'         => $invoice->id,
            'payment_method'     => 'manual',
            'payment_proof_path' => $path,
            'status'             => PaymentStatus::PENDING,
        ]);
    }
}
