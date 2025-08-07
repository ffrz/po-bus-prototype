<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'code',
        'description',
        'plate_number',
        'type',
        'capacity',
        'status',
        'brand',
        'model',
        'year',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'capacity'    => 'integer',
        'year'        => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
    ];

    /**
     * Vehicle type constants.
     */
    const Type_BigBus    = 'big-bus';
    const Type_MediumBus = 'medium-bus';

    const Types = [
        self::Type_BigBus    => 'Big Bus',
        self::Type_MediumBus => 'Medium Bus',
    ];

    /**
     * Vehicle status constants.
     */
    const Status_Active      = 'active';
    const Status_Inactive    = 'inactive';
    const Status_Maintenance = 'maintenance';

    const Statuses = [
        self::Status_Active      => 'Aktif',
        self::Status_Inactive    => 'Tidak Aktif',
        self::Status_Maintenance => 'Maintenance',
    ];
}
