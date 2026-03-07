<?php

namespace Modules\Finance\Services;

use ManualPaymentStrategy;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Repositories\Contracts\InvoiceRepositoryInterface;
use Modules\Finance\Strategies\MidtransPaymentStrategy;
use Modules\Setting\Services\SettingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FinanceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly SettingService $settingService,
    ) {}

    public function processPayment(int $invoiceId, array $data)
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if ($invoice->status === InvoiceStatus::PAID) {
            throw new HttpException(422, 'Tagihan ini sudah lunas.');
        }

        $method = $data['payment_method'];

        if ($method === 'midtrans') {
            if (!$this->settingService->isMidtransEnabled()) {
                throw new HttpException(403, 'Mohon maaf, metode pembayaran saat ini sedang dinonaktifkan oleh admin');
            }
            $strategy = new MidtransPaymentStrategy();
        } elseif ($method === 'manual') {
            $strategy = new ManualPaymentStrategy();
        } else {
            throw new HttpException(422, 'Metode pembayaran tidak didukung');
        }

        return $strategy->process($invoice, $data);
    }
}
