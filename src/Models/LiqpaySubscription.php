<?php

namespace Alyakin\LiqpayLaravel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $order_id
 * @property string $status
 * @property float $amount
 * @property string $currency
 * @property string $description
 * @property string $liqpay_order_id
 * @property string $payment_id
 * @property ?array<string, string|mixed> $info
 * @property ?string $last_paid_at
 * @property ?string $last_payment_id
 * @property ?string $started_at
 * @property ?string $expired_at
 * @property array $liqpay_data
 */
class LiqpaySubscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'status',
        'amount',
        'currency',
        'description',
        'liqpay_order_id',
        'payment_id',
        'info',
        'last_paid_at',
        'last_payment_id',

        'started_at',
        'expired_at',
        'liqpay_data',

    ];

    protected $casts = [
        'liqpay_data' => 'array',
        'info' => 'array',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'last_paid_at' => 'datetime',
    ];
}
