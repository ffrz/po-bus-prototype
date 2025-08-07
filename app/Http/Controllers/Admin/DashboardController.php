<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // For other roles, show the default dashboard
        return inertia('admin/dashboard/Index', [
            'data' => [],
            'period' => [
                'label' => 'This Month',
                'start_date' => '',
                'end_date' => '',
            ],
        ]);
    }
}
