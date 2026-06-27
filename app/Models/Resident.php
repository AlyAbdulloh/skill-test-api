<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resident extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mst_residents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'id_card_photo',
        'resident_status',
        'phone_number',
        'is_married',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_married' => 'boolean',
    ];

    /**
     * Get the full URL for the resident's ID card photo.
     *
     * @return string|null
     */
    public function getIdCardPhotoUrlAttribute(): ?string
    {
        if (!$this->id_card_photo) {
            return null;
        }

        // If the path is already a full URL, return it
        if (filter_var($this->id_card_photo, FILTER_VALIDATE_URL)) {
            return $this->id_card_photo;
        }

        return Storage::disk('public')->url($this->id_card_photo);
    }

    /**
     * Get the house associations for this resident.
     */
    public function houseResidents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HouseResident::class, 'resident_id');
    }
}
