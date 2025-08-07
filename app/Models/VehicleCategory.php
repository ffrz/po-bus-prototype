<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleCategory extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the vehicles for the category.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
