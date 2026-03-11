<?php

namespace Modules\Finance\Repositories\Contracts;

interface PaymentRepositoryInterface
{
    public function countPendingVerification(): int;
}
