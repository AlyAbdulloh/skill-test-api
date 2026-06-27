<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentBill extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tr_payment_bills';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'house_resident_id',
        'fee_type_id',
        'billing_month',
        'amount',
        'status',
        'paid_at',
        'payment_group_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'billing_month' => 'date',
        'paid_at' => 'date',
        'amount' => 'float',
    ];

    /**
     * Get the house-resident association.
     */
    public function houseResident(): BelongsTo
    {
        return $this->belongsTo(HouseResident::class, 'house_resident_id');
    }

    /**
     * Get the fee type.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }
}
