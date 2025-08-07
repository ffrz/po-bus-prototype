<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        return inertia('admin/vehicle/Index', [
            'categories' => VehicleCategory::query()->orderBy('name', 'asc')->get(),
        ]);
    }

    public function detail($id = 0)
    {
        $item = Vehicle::with([
            'category:id,name',
            'createdBy:id,username,name',
            'updatedBy:id,username,name'
        ])->findOrFail($id);
        
        return inertia('admin/vehicle/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Vehicle::with(['category']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id']) && $filter['category_id'] != 'all') {
            $q->where('category_id', '=', $filter['category_id']);
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        $items->getCollection()->transform(function ($item) {
            $item->description = strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description;
            return $item;
        });

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Vehicle::findOrFail($id);
        $item->id = null;
        return inertia('admin/vehicle/Editor', [
            'data' => $item,
            'categories' => VehicleCategory::all(['id', 'name']),
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? Vehicle::findOrFail($id) : new Vehicle(
            ['active' => 1]
        );
        return inertia('admin/vehicle/Editor', [
            'data' => $item,
            'categories' => VehicleCategory::all(['id', 'name']),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'nullable',
                Rule::exists('vehicle_categories', 'id'),
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique('vehicles', 'name')->ignore($request->id), // agar saat update tidak dianggap duplikat sendiri
            ],
            'plate_number' => 'nullable|max:20',
            'type' => ['required', 'string', Rule::in(Vehicle::Types)],
            'capacity' => 'nullable|numeric',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? Vehicle::findOrFail($request->id) : new Vehicle();
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.vehicle.index'))
            ->with('success', "Varietas $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = Vehicle::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Varietas $item->name telah dihapus."
        ]);
    }

}
