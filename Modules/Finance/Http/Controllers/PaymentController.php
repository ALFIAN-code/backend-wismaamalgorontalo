<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Modules\Finance\Http\Requests\PayInvoiceRequest;
use Modules\Finance\Services\FinanceService;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FinanceService $financeService
    ) {}

    public function pay(PayInvoiceRequest $request, int $invoiceId)
    {
        $data = $request->validated();

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof');
        }

        $payment = $this->financeService->processPayment($invoiceId, $data);

        return $this->apiSuccess($payment, 'Proses pembayaran berhasil diinisialisasi', 201);
    }
}
