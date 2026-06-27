<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mst_houses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'house_number',
        'address',
        'occupancy_status',
    ];

    /**
     * Get the residents associated with the house.
     */
    public function houseResidents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HouseResident::class, 'house_id');
    }
}
