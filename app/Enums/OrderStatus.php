<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}