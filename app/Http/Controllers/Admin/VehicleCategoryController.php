<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleCategoryController extends Controller
{
    public function index()
    {
        return inertia('admin/vehicle-category/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = VehicleCategory::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = VehicleCategory::findOrFail($id);
        $item->id = null;
        return inertia('admin/vehicle-category/Editor', [
            'data' => $item
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? VehicleCategory::findOrFail($id) : new VehicleCategory();
        return inertia('admin/vehicle-category/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = $request->id ? VehicleCategory::findOrFail($request->id) : new VehicleCategory();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('vehicle_categories', 'name')->ignore($item->id),
            ],
            'description' => 'nullable|max:1000',
        ]);

        $item->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        $item->save();

        return redirect()
            ->route('admin.vehicle-category.index')
            ->with('success', "Kategori $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = VehicleCategory::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Kategori $item->name telah dihapus."
        ]);
    }
}
