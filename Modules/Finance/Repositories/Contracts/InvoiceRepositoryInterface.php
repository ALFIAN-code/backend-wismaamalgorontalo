<?php

namespace Modules\Finance\Repositories\Contracts;

use Modules\Finance\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;
    public function updateStatus(Invoice $invoice, string $status): Invoice;
}
