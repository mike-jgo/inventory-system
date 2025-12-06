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
        $items      = Item::with('category')->paginate(10);
        $categories = Category::all();

        return Inertia::render('ItemList/Index', [
            'items'      => $items,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'quantity'    => 'required|integer|min:0',
        ]);

        $item = Item::create($validated);

        //Log creation
        activity()
            ->causedBy(Auth::user())
            ->performedOn($item)
            ->withProperties(['attributes' => $validated, 'severity' => 'information'])
            ->log("Created a new item: {$item->name}");

        return redirect()->route('items.index');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'quantity'    => 'required|integer|min:0',
        ]);

        $old = $item->getOriginal();
        $item->update($validated);

        //Log update
        activity()
            ->causedBy(Auth::user())
            ->performedOn($item)
            ->withProperties([
                'old' => $old,
                'new' => $validated,
                'severity' => 'information',
            ])
            ->log("Updated item: {$item->name}");

        return redirect()->route('items.index');
    }

    public function destroy(Item $item)
    {
        $itemName = $item->name;
        $item->delete();

        //Log deletion
        activity()
            ->causedBy(Auth::user())
            ->performedOn($item)
            ->withProperties(['severity' => 'warning'])
            ->log("Deleted item: {$itemName}");

        return redirect()->route('items.index');
    }
}
