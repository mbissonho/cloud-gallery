<?php

namespace App\Models;

enum PurchaseStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
}
