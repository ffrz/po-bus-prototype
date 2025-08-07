<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        return inertia('admin/vehicle/Index');
    }

    public function detail($id)
    {
        $item = Vehicle::with([
            'createdBy:id,username,name',
            'updatedBy:id,username,name'
        ])->findOrFail($id);

        return inertia('admin/vehicle/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'created_at');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Vehicle::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('code', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('plate_number', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $q->where('status', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        $items->getCollection()->transform(function ($item) {
            $item->description = strlen($item->description) > 50
                ? substr($item->description, 0, 50) . '...'
                : $item->description;
            return $item;
        });

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Vehicle::findOrFail($id);
        $item->id = null;
        $item->code = $item->code . '_copy';

        return inertia('admin/vehicle/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id
            ? Vehicle::findOrFail($id)
            : new Vehicle(['status' => 'active']);

        return inertia('admin/vehicle/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'max:20',
                Rule::unique('vehicles', 'code')->ignore($request->id),
            ],
            'description' => [
                'required',
                'max:100',
            ],
            'plate_number' => 'required|max:20',
            'type' => ['nullable', 'string', Rule::in(array_keys(Vehicle::Types))],
            'capacity' => 'nullable|integer|min:0|max:255',
            'status' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:40',
            'model' => 'nullable|string|max:40',
            'year' => 'nullable|integer|min:1900|max:' . now()->year,
            'notes' => 'nullable|string|max:1000',
        ]);

        $item = $request->id
            ? Vehicle::findOrFail($request->id)
            : new Vehicle();

        $item->fill($validated);
        $item->created_by = $item->exists ? $item->created_by : auth()->id();
        $item->updated_by = auth()->id();
        $item->save();

        return redirect(route('admin.vehicle.index'))
            ->with('success', "Armada $item->code telah disimpan.");
    }

    public function delete($id)
    {
        $item = Vehicle::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Armada $item->code telah dihapus."
        ]);
    }
}
