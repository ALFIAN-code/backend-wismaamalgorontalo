<?php

use Illuminate\Http\UploadedFile;
use Modules\Finance\Contracts\PaymentStrategyInterface;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ManualPaymentStrategy implements PaymentStrategyInterface
{
    public function process(Invoice $invoice, array $data): Payment
    {
        if (!isset($data['payment_proof']) || !$data['payment_proof'] instanceof UploadedFile) {
            throw new HttpException(422, 'Bukti transfer wajib diunggah untuk metode manual');
        }

        $path = $data['payment_proof']->store('payment', 'public');

        return Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'manual',
            'payment_proof_path' => $path,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }
}
