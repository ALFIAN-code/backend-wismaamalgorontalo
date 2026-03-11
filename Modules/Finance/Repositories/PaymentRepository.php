<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function countPendingVerification(): int
    {
        return Payment::where('status', PaymentStatus::PENDING->value)->count();
    }
}
