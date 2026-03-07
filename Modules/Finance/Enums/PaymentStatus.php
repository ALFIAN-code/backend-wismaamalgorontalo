<?php

namespace Modules\Finance\Enums;

enum PaymentStatus: string
{
    case PENDING = 'penfing';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
}
