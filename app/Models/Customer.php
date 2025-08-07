<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'type',
        'address',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    const Type_Personal = 'Perorangan';
    const Type_Company = 'Perusahaan';

    const Types = [
        self::Type_Personal => 'Perorangan',
        self::Type_Company => 'Perusahaan',
    ];

}
