<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        // Check if user is superadmin
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $inventories = Inventory::orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
        ]);
    }

    public function store(Request $request)
    {
        // Check if user is superadmin
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $inventory = Inventory::create($validated);

        return redirect()->route('inventory.index')->with('success', "Inventory item '{$inventory->name}' created successfully.");
    }

    public function update(Request $request, Inventory $inventory)
    {
        // Check if user is superadmin
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', "Inventory item '{$inventory->name}' updated successfully.");
    }

    public function destroy(Inventory $inventory)
    {
        // Check if user is superadmin
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $inventoryName = $inventory->name;
        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', "Inventory item '{$inventoryName}' deleted successfully.");
    }
}
