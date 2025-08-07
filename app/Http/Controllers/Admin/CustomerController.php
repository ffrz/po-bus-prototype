<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return inertia('admin/customer/Index');
    }

    public function detail($id = 0)
    {
        $item = Customer::with([
            'createdBy:id,username,name',
            'updatedBy:id,username,name',
        ])->findOrFail($id);
        $this->authorize('view', $item);
        return inertia('admin/customer/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Customer::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && (in_array($filter['status'], ['active', 'inactive']))) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }
        if (!empty($filter['type']) && $filter['type'] != 'all' && (in_array($filter['type'], array_keys(Customer::Types)))) {
            $q->where('type', '=', $filter['type']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Customer::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $user = Auth::user();
        $item = $id ? Customer::findOrFail($id) : new Customer(['active' => true]);

        $this->authorize('update', $item);

        return inertia('admin/customer/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated =  $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'type'           => ['required', 'string', Rule::in(Customer::Types)],
            'address'        => 'nullable|string|max:500',
            'active'         => 'required|boolean',
            'notes'          => 'nullable|string',
        ]);

        $item = !$request->id ? new Customer() : Customer::findOrFail($request->post('id', 0));

        $this->authorize('update', $item);

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.customer.detail', ['id' => $item->id]))->with('success', "Pelanggan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = Customer::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Pelanggan $item->name telah dihapus."
        ]);
    }
}
