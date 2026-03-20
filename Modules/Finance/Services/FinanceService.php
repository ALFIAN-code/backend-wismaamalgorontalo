<?php

namespace Modules\Finance\Services;

use Carbon\Carbon;
use ManualPaymentStrategy;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\Contracts\ExpenseRepositoryInterface;
use Modules\Finance\Repositories\Contracts\InvoiceRepositoryInterface;
use Modules\Finance\Strategies\MidtransPaymentStrategy;
use Modules\Rental\Services\RentalService;
use Modules\Setting\Services\SettingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FinanceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly SettingService $settingService,
        private readonly RentalService $rentalService,
        private readonly ExpenseRepositoryInterface $expenseRepository,
    ) {}

    public function processPayment(int $invoiceId, array $data): Payment
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if ($invoice->status === InvoiceStatus::PAID) {
            throw new HttpException(422, 'Tagihan ini sudah lunas.');
        }

        $strategy = match ($data['payment_method']) {
            'midtrans' => $this->resolveMidtransStrategy(),
            'manual' => app(ManualPaymentStrategy::class),
            default => throw new HttpException(422, 'Metode pembayaran tidak didukung')
        };

        return $strategy->process($invoice, $data);
    }

    public function generateInvoiceForLease(int $leaseId, float $amount, Carbon $dueDate)
    {
        $InvoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($leaseId, 4, '0', STR_PAD_LEFT);

        return $this->invoiceRepository->create([
            'lease_id' => $leaseId,
            'invoice_number' => $InvoiceNumber,
            'amount' => $amount,
            'status' => InvoiceStatus::UNPAID->value,
            'due_date' => $dueDate,
        ]);
    }

    public function verifyPayment(int $paymentId, bool $isApproved, ?string $adminNotes = null): Payment
    {
        $payment = Payment::findOrFail($paymentId);

        $payment->update([
            'status' => $isApproved ? PaymentStatus::VERIFIED->value : PaymentStatus::REJECTED->value,
            'admin_notes' => $adminNotes,
        ]);

        if ($isApproved) {
            $invoice = $this->invoiceRepository->findById($payment->invoice_id);
            $this->invoiceRepository->updateStatus($invoice, InvoiceStatus::PAID->value);
            $this->rentalService->activateLease($invoice->lease_id);
        }

        return $payment;
    }

    private function resolveMidtransStrategy(): MidtransPaymentStrategy
    {
        if (!$this->settingService->isMidtransEnabled()) {
            throw new HttpException(403, 'Mohon maaf, metode pembayaran saat ini sedang dinonaktifkan oleh admin');
        }
        return app(MidtransPaymentStrategy::class);
    }

    public function recordExpense(array $data)
    {
        $data['expense_date'] = $data['expense_date'] ?? now()->toDateString();

        return $this->expenseRepository->create($data);
    }

    public function getAllExpenses(int $perPage = 15)
    {
        return $this->expenseRepository->getPaginated($perPage);
    }

    public function syncExpenseByReference(int $refId, string $refType, array $data)
    {
        $expense = $this->expenseRepository->findByReference($refId, $refType);

        if ($expense) {
            if (empty($data['amount']) || $data['amount'] <= 0) {
                return $this->expenseRepository->delete($expense);
            }
            return $this->expenseRepository->update($expense, $data);
        } else {
            if (!empty($data['amount']) && $data['amount'] > 0) {
                $data['reference_id'] = $refId;
                $data['reference_type'] = $refType;
                return $this->recordExpense($data);
            }
        }

        return null;
    }

    public function removeExpenseByReference(int $refId, string $refType): bool
    {
        $expense = $this->expenseRepository->findByReference($refId, $refType);
        if ($expense) {
            return $this->expenseRepository->delete($expense);
        }
        return false;
    }

    public function createManualExpense(array $data): Expense
    {
        $data['reference_id'] = null;
        $data['reference_type'] = null;

        return $this->expenseRepository->create($data);
    }

    public function updateManualExpense(Expense $expense, array $data): Expense
    {
        if ($expense->reference_type !== null) {
            throw new HttpException(403, 'Pengeluaran ini terintegrasi dengan modul lain (Inventory). Silakan edit dari data barang terkait.');
        }

        return $this->expenseRepository->update($expense, $data);
    }

    public function deleteManualExpense(Expense $expense): bool
    {
        if ($expense->reference_type !== null) {
            throw new HttpException(403, 'Pengeluaran ini terintegrasi dengan modul lain (Inventory). Silakan hapus barang terkait jika ingin membatalkan pengeluaran.');
        }

        return $this->expenseRepository->delete($expense);
    }
}
