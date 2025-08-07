<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Vehicle Model
 */
class Vehicle extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'name',
        'plat_number',
        'type',
        'capacity',
        'active',
        'notes',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    const Type_BigBus = 'big-bus';
    const Type_MediumBus = 'medium-bus';

    const Types = [
        self::Type_BigBus => 'Big Bus',
        self::Type_MediumBus => 'Medium Bus',
    ];

    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(VehicleCategory::class);
    }

}
