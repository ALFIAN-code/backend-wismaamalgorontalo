<?php

namespace Modules\Finance\Strategies;

use Modules\Finance\Contracts\PaymentStrategyInterface;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

class MidtransPaymentStrategy implements PaymentStrategyInterface
{
    public function process(Invoice $invoice, array $data): Payment
    {
        $transactionId = 'MID-' . uniqid();

        return Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'midtrans',
            'transaction_id' => $transactionId,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }
}
