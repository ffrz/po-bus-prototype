<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DemoPlot;
use App\Models\Interaction;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('report_type');

        $currentUser = Auth::user();
        $users = [];
        if ($currentUser->role == User::Role_Agronomist) {
            $users = User::query()->where('role', '=', User::Role_BS)
                ->where('parent_id', '=', $currentUser->id)
                ->orderBy('name', 'asc')
                ->get();
        } else if ($currentUser->role == User::Role_Admin) {
            $users = User::query()->orderBy('name', 'asc')->get();
        }

        return inertia('admin/report/Index', [
            'report_type' => $type,
            'users' => $users,
            'products' => Product::where('active', true)
                ->orderBy('name')
                ->select('id', 'name')
                ->get(),
        ]);
    }

    public function demoPlotDetail(Request $request)
    {
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            } else if ($current_user->role == User::Role_Admin) {
                if ($user_id != 'all') {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Demo Plot', $user_id);

            return $this->generatePdfReport('report.demo-plot-detail', 'landscape', compact(
                'items',
                'title',
                'user'
            ));
        }
    }

    public function demoPlotWithPhoto(Request $request)
    {
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->leftJoin(
                    DB::raw('
                        (
                            SELECT dpv1.demo_plot_id, dpv1.image_path
                            FROM demo_plot_visits dpv1
                            INNER JOIN (
                                SELECT demo_plot_id, MAX(created_at) AS max_created_at
                                FROM demo_plot_visits
                                GROUP BY demo_plot_id
                            ) dpv2 ON dpv1.demo_plot_id = dpv2.demo_plot_id AND dpv1.created_at = dpv2.max_created_at
                        ) AS latest_visits
                    '),
                    'latest_visits.demo_plot_id',
                    '=',
                    'demo_plots.id'
                )
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            } else if ($current_user->role == User::Role_Admin) {
                if ($user_id != 'all') {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Foto Demo Plot', $user_id);

            return $this->generatePdfReport('report.demo-plot-with-photo', 'landscape', compact(
                'items',
                'title',
                'user'
            ));
        }
    }

    public function newDemoPlotDetail(Request $request)
    {
        [$start_date, $end_date] = resolve_period(
            $request->get('period'),
            $request->get('start_date'),
            $request->get('end_date')
        );
        $user_id = $request->get('user_id');

        if (isset($user_id)) {
            $current_user = Auth::user();

            $q = DemoPlot::select('demo_plots.*')
                ->leftJoin('users', 'users.id', '=', 'demo_plots.user_id')
                ->leftJoin('products', 'products.id', '=', 'demo_plots.product_id')
                ->with([
                    'user:id,username,name',
                    'product:id,name',
                ]);

            if ($current_user->role == User::Role_Agronomist) {
                if ($user_id == 'all') {
                    $q->whereHas('user', function ($query) use ($current_user) {
                        $query->where('parent_id', $current_user->id);
                    });
                } else {
                    $q->where('demo_plots.user_id', $user_id);
                }
            }

            $items = $q->where('demo_plots.active', true)
                ->whereBetween('plant_date', [$start_date, $end_date])
                ->orderBy('users.name', 'asc')
                ->orderBy('products.name', 'asc')
                ->get();

            [$title, $user] = $this->resolveTitle('Laporan Demo Plot Baru', $user_id);

            return $this->generatePdfReport('report.new-demo-plot-detail', 'landscape', compact(
                'items',
                'title',
                'user',
                'start_date',
                'end_date',
            ));
        }
    }

    // public function salesActivity(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period(
    //         $request->get('period'),
    //         $request->get('start_date'),
    //         $request->get('end_date')
    //     );
    //     $user_id = $request->get('user_id');

    //     // Interactions
    //     $interactions = DB::table('interactions')
    //         ->select(
    //             DB::raw('DATE(date) as date'),
    //             'user_id',
    //             DB::raw('COUNT(*) as total_interactions')
    //         )
    //         ->where('status', 'done')
    //         ->whereBetween('date', [$start_date, $end_date])
    //         ->when($user_id, fn($q) => $q->where('user_id', $user_id))
    //         ->groupBy('date', 'user_id');

    //     // Closings
    //     $closings = DB::table('closings')
    //         ->select(
    //             DB::raw('DATE(date) as date'),
    //             'user_id',
    //             DB::raw('COUNT(*) as total_closings')
    //         )
    //         ->whereBetween('date', [$start_date, $end_date])
    //         ->when($user_id, fn($q) => $q->where('user_id', $user_id))
    //         ->groupBy('date', 'user_id');

    //     // New Customers
    //     $new_customers = DB::table('customers')
    //         ->select(
    //             DB::raw('DATE(created_at) as date'),
    //             'created_by',
    //             DB::raw('COUNT(*) as total_new_customers')
    //         )
    //         ->whereBetween('created_at', [$start_date, $end_date])
    //         ->when($user_id, fn($q) => $q->where('created_by', $user_id))
    //         ->groupBy('date', 'created_by');

    //     // Gabungkan semua dengan LEFT JOIN
    //     $items = DB::query()
    //         ->fromSub($interactions, 'i')
    //         ->leftJoinSub($closings, 'c', function ($join) {
    //             $join->on('i.date', '=', 'c.date')
    //                 ->on('i.user_id', '=', 'c.user_id');
    //         })
    //         ->leftJoinSub($new_customers, 'n', function ($join) {
    //             $join->on('i.date', '=', 'n.date')
    //                 ->on('i.user_id', '=', 'n.created_by');
    //         })
    //         ->join('users', 'i.user_id', '=', 'users.id')
    //         ->select(
    //             'i.date',
    //             'users.name as sales_name',
    //             'i.total_interactions',
    //             DB::raw('COALESCE(c.total_closings, 0) as total_closings'),
    //             DB::raw('COALESCE(n.total_new_customers, 0) as total_new_customers')
    //         )
    //         ->orderBy('i.date')
    //         ->orderBy('sales_name')
    //         ->get();

    //     [$title, $user] = $this->resolveTitle('Laporan Aktivitas Sales', $user_id);

    //     return $this->generatePdfReport('report.sales-activity', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //         'user'
    //     ));
    // }

    // public function salesActivityDetail(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period(
    //         $request->get('period'),
    //         $request->get('start_date'),
    //         $request->get('end_date')
    //     );

    //     $user_id = $request->get('user_id');

    //     if (!$user_id) {
    //         abort(400, 'user_id harus diisi');
    //     }

    //     $interactions = Interaction::with('customer')
    //         ->where('user_id', $user_id)
    //         ->where('status', 'done')
    //         ->whereBetween('date', [$start_date, $end_date])
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'date'   => $item->date,
    //                 'type'   => 'Interaksi',
    //                 'client' => $item->customer->name . ' - ' . $item->customer->company,
    //                 'detail' => Interaction::Types[$item->type] . ' | ' . $item->service->name . ' | ' .  Interaction::EngagementLevels[$item->engagement_level] . ' | ' . $item->notes,
    //                 'data_1' => 0,
    //             ];
    //         });

    //     $closings = Closing::with(['customer', 'service'])
    //         ->where('user_id', $user_id)
    //         ->whereBetween('date', [$start_date, $end_date])
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'date'   => $item->date,
    //                 'type'   => 'Closing',
    //                 'client' => $item->customer->name . ' - ' . $item->customer->company,
    //                 'detail' => ($item->service->name ?? '-') . ' - ' . ($item->description ?? '-') . ': Rp ' . number_format($item->amount, 0, ',', '.'),
    //                 'data_1' => $item->amount,
    //             ];
    //         });

    //     $items = $interactions
    //         ->merge($closings)
    //         ->sortBy('date')
    //         ->values()
    //         ->map(function ($item, $index) {
    //             return [
    //                 'no'     => $index + 1,
    //                 'date'   => $item['date'],
    //                 'type'   => $item['type'],
    //                 'client' => $item['client'],
    //                 'detail' => $item['detail'],
    //                 'data_1' => $item['data_1'],
    //             ];
    //         });

    //     $user = User::find($user_id);
    //     $title = 'Laporan Aktivitas per Sales';
    //     $subtitles = ['Sales: ' . ($user->name ?? '-')];

    //     return $this->generatePdfReport('report.sales-activity-detail', 'landscape', compact(
    //         'items',
    //         'title',
    //         'subtitles',
    //         'start_date',
    //         'end_date'
    //     ));
    // }

    // public function closingDetail(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));
    //     $user_id = $request->get('user_id');

    //     $q = Interaction::with([
    //         'user:id,username,name',
    //         'customer:id,name,company,address,business_type',
    //         'service:id,name'
    //     ]);

    //     if ($user_id !== 'all') {
    //         $q->where('user_id', $user_id);
    //     }

    //     $items = DB::table('closings')
    //         ->join('users', 'closings.user_id', '=', 'users.id')
    //         ->join('services', 'closings.service_id', '=', 'services.id')
    //         ->join('customers', 'closings.customer_id', '=', 'customers.id')
    //         ->select(
    //             'closings.id',
    //             'closings.date',
    //             'users.name as sales_name',
    //             'customers.name as customer_name',
    //             'customers.company as company',
    //             'customers.address as address',
    //             'services.name as service_name',
    //             'closings.description',
    //             'closings.amount',
    //             'closings.notes'
    //         )
    //         ->whereBetween('closings.date', [$start_date, $end_date])
    //         ->orderBy('closings.date', 'asc')
    //         ->get();

    //     [$title, $user] = $this->resolveTitle('Laporan Detail Closing', $user_id);

    //     return $this->generatePdfReport('report.closing-detail', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //         'user'
    //     ));
    // }

    // public function closingBySales(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $items = DB::table('closings')
    //         ->join('users', 'closings.user_id', '=', 'users.id')
    //         ->select(
    //             'users.id as sales_id',
    //             'users.name as sales_name',
    //             DB::raw('COUNT(*) as total_closings'),
    //             DB::raw('SUM(closings.amount) as total_amount')
    //         )
    //         ->whereBetween('closings.date', [$start_date, $end_date])
    //         ->groupBy('users.id', 'users.name')
    //         ->orderBy('total_closings', 'desc')
    //         ->get();

    //     $title = 'Laporan Rekapitulasi Closing per Sales';

    //     return $this->generatePdfReport('report.closing-by-sales', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function closingByServices(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $items = DB::table('closings')
    //         ->join('services', 'closings.service_id', '=', 'services.id')
    //         ->select(
    //             'services.name as service_name',
    //             DB::raw('COUNT(*) as total_closings'),
    //             DB::raw('SUM(closings.amount) as total_amount')
    //         )
    //         ->whereBetween('closings.date', [$start_date, $end_date])
    //         ->groupBy('services.name')
    //         ->orderBy('total_closings', 'desc')
    //         ->get();

    //     $title = 'Laporan Rekap Closing per Layanan';

    //     return $this->generatePdfReport('report.closing-by-services', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function customerServicesActive(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $items = CustomerService::with(['customer', 'service'])
    //         ->where(function ($query) use ($start_date, $end_date) {
    //             $query->where('start_date', '<=', $end_date)
    //                 ->where(function ($q) use ($start_date) {
    //                     $q->where('end_date', '>=', $start_date)
    //                         ->orWhereNull('end_date');
    //                 });
    //         })
    //         ->get();

    //     $title = 'Laporan Layanan Pelanggan Aktif';

    //     return $this->generatePdfReport('report.customer-services-active', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function customerServicesNew(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $items = CustomerService::with(['customer', 'service', 'closing', 'closing.user'])
    //         ->whereNotNull('start_date')
    //         ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('start_date', [$start_date, $end_date]);
    //         })
    //         ->get();

    //     $title = 'Laporan Layanan Pelanggan Baru';

    //     return $this->generatePdfReport('report.customer-services-new', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function customerServicesEnded(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $items = CustomerService::with(['customer', 'service'])
    //         ->whereIn('status', ['churned', 'cancelled'])
    //         ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('end_date', [$start_date, $end_date]);
    //         })
    //         ->get();


    //     $title = 'Laporan Layanan Pelanggan Berakhir';

    //     return $this->generatePdfReport('report.customer-services-ended', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function clientNew(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));
    //     $user_id = $request->get('user_id');

    //     $q = Customer::with('assigned_user');
    //     if ($user_id !== 'all') {
    //         $q->where('assigned_user_id', $user_id);
    //     }
    //     $items = $q->whereBetween('created_at', [$start_date, $end_date])
    //         ->orderByDesc('created_at')
    //         ->get();

    //     [$title, $user] = $this->resolveTitle('Laporan Klien Baru', $user_id);

    //     return $this->generatePdfReport('report.customer-new', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //         'user'
    //     ));
    // }

    // public function clientActiveInactive(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));
    //     $user_id = $request->get('user_id');
    //     $q = Customer::with([
    //         'assigned_user',
    //         'interactions' => function ($q) use ($start_date, $end_date) {
    //             $q->where('status', 'done')
    //                 ->whereBetween('date', [$start_date, $end_date])
    //                 ->latest('date');
    //         },
    //         'closings' => function ($q) use ($start_date, $end_date) {
    //             $q->whereBetween('date', [$start_date, $end_date])
    //                 ->latest('date');
    //         }
    //     ]);
    //     $q->whereDate('created_at', '<=', $end_date);

    //     if ($user_id !== 'all') {
    //         $q->where('assigned_user_id', $user_id);
    //     }

    //     $items = $q->get()
    //         ->map(function ($customer) {
    //             return [
    //                 'id'                => $customer->id,
    //                 'client'            => $customer->name . ' - ' . $customer->company,
    //                 'status'            => $customer->active ? 'Aktif' : 'Tidak Aktif',
    //                 'last_interaction'  => optional($customer->interactions->first())->date ?? '-',
    //                 'engagement_level'  => optional($customer->interactions->first())->engagement_level ?? '-',
    //                 'last_closing'      => optional($customer->closings->first())->date ?? '-',
    //                 'sales'             => $customer->assigned_user ? $customer->assigned_user->name : '-',
    //             ];
    //         });

    //     [$title, $user] = $this->resolveTitle('Laporan Klien Aktif - Tidak Aktif', $user_id);

    //     return $this->generatePdfReport('report.customer-active-inactive', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //         'user'
    //     ));
    // }

    // public function clientHistory(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period($request->get('period'), $request->get('start_date'), $request->get('end_date'));

    //     $client = Customer::with([
    //         'interactions' => function ($q) use ($start_date, $end_date) {
    //             $q->where('status', 'done')
    //                 ->whereBetween('date', [$start_date, $end_date])
    //                 ->with('user');
    //         },
    //         'closings' => function ($q) use ($start_date, $end_date) {
    //             $q->whereBetween('date', [$start_date, $end_date])
    //                 ->with(['user', 'service']);
    //         },
    //         'services' => function ($q) use ($start_date, $end_date) {
    //             $q->whereBetween('start_date', [$start_date, $end_date])
    //                 ->with('service');
    //         },
    //     ])->findOrFail($request->client_id);

    //     // Gabungkan semua aktivitas
    //     $items = collect();

    //     // Interaksi
    //     foreach ($client->interactions as $item) {
    //         if ($item->status !== 'done') continue;

    //         $items->push([
    //             'date'   => $item->date,
    //             'type'   => 'Interaksi',
    //             'detail' => $item->notes,
    //             'sales'  => $item->user->name ?? '-',
    //         ]);
    //     }

    //     // Closing
    //     foreach ($client->closings as $item) {
    //         $items->push([
    //             'date'   => $item->date,
    //             'type'   => 'Closing',
    //             'detail' => ($item->service->name ?? '-') . ', Rp' . number_format($item->amount, 0, ',', '.'),
    //             'sales'  => $item->user->name ?? '-',
    //         ]);
    //     }

    //     // Layanan
    //     foreach ($client->services as $item) {
    //         $items->push([
    //             'date'   => $item->start_date,
    //             'type'   => 'Layanan',
    //             'detail' => ucfirst($item->status) . ' – ' . ($item->service->name ?? '-'),
    //             'sales'  => '-', // tidak ada sales langsung untuk layanan aktif
    //         ]);
    //     }

    //     // Urutkan berdasarkan tanggal
    //     $items = $items->sortBy('date')->values()->map(function ($item, $index) {
    //         return [
    //             'no'     => $index + 1,
    //             'date'   => $item['date'],
    //             'type'   => $item['type'],
    //             'detail' => $item['detail'],
    //             'sales'  => $item['sales'],
    //         ];
    //     });

    //     $title = 'Laporan Riwayat Klien';
    //     $subtitles = ['Client: ' . $client->name . ' - ' . $client->company];

    //     return $this->generatePdfReport('report.customer-history', 'landscape', compact(
    //         'items',
    //         'title',
    //         'subtitles',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    // public function salesPerformance(Request $request)
    // {
    //     [$start_date, $end_date] = resolve_period(
    //         $request->get('period'),
    //         $request->get('start_date'),
    //         $request->get('end_date')
    //     );

    //     $items = User::withCount([
    //         'interactions as interactions_count' => function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('date', [$start_date, $end_date]);
    //         },
    //         'closings as closings_count' => function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('date', [$start_date, $end_date]);
    //         },
    //         'customers as new_clients_count' => function ($query) use ($start_date, $end_date) {
    //             $query->whereBetween('created_at', [$start_date, $end_date]);
    //         }
    //     ])
    //         ->withSum([
    //             'closings as total_amount' => function ($query) use ($start_date, $end_date) {
    //                 $query->whereBetween('date', [$start_date, $end_date]);
    //             },
    //         ], 'amount')
    //         ->get();

    //     $title = 'Laporan Rekapitulasi Kinerja Sales';

    //     return $this->generatePdfReport('report.sales-performance', 'landscape', compact(
    //         'items',
    //         'title',
    //         'start_date',
    //         'end_date',
    //     ));
    // }

    protected function resolveTitle(string $baseTitle, $user_id): array
    {
        $user = null;
        if ($user_id !== 'all') {
            $user = User::find($user_id);
            $title = "$baseTitle - $user->name ($user->username)";
        } else {
            $title = "$baseTitle - All BS";
        }
        return [$title, $user];
    }


    protected function generatePdfReport($view, $orientation, $data, $response = 'pdf')
    {
        $filename = env('APP_NAME') . ' - ' . $data['title'];

        if (isset($data['start_date']) || isset($data['end_date'])) {
            if (empty($data['subtitles'])) {
                $data['subtitles'] = [];
            }
            $data['subtitles'][] = 'Periode ' . format_date($data['start_date']) . ' s/d ' . format_date($data['end_date']);
        }

        // return view($view, $data);

        if ($response == 'pdf') {
            return Pdf::loadView($view, $data)
                ->setPaper('a4', $orientation)
                ->download($filename . '.pdf');
        }

        if ($response == 'html') {
            return view($view, $data);
        }

        throw new Exception('Unknown response type!');
    }
}
