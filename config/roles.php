<?php

use App\Models\User;

return [
    User::Role_Operator => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',

        'admin.vehicle-category.index',
        'admin.vehicle-category.data',
        'admin.vehicle-category.add',
        'admin.vehicle-category.edit',
        'admin.vehicle-category.save',
        'admin.vehicle-category.delete',

        'admin.vehicle.index',
        'admin.vehicle.data',
        'admin.vehicle.add',
        'admin.vehicle.edit',
        'admin.vehicle.save',
        'admin.vehicle.delete',
        'admin.vehicle.detail',

        'admin.customer.index',
        'admin.customer.data',
        'admin.customer.detail',
        'admin.customer.add',
        'admin.customer.edit',
        'admin.customer.save',
        'admin.customer.duplicate',
    ],
    
];
