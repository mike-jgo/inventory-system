<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->paginate(10);
        $categories = Category::all();

        return Inertia::render('ItemList/Index', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $item = Item::create($validated);

        return redirect()->route('items.index')->with('success', "Item '{$item->name}' created successfully.");
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('success', "Item '{$item->name}' updated successfully.");
    }

    public function destroy(Item $item)
    {
        $itemName = $item->name;
        $item->delete();

        return redirect()->route('items.index')->with('success', "Item '{$itemName}' deleted successfully.");
    }
}
