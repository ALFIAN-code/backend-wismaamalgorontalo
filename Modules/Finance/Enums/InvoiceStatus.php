<?php

namespace Modules\Finance\Enums;

enum InvoiceStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'padi';
    case CANCELLED = 'cancelled';
}
