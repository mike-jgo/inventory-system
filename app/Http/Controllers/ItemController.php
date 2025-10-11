<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')
            ->orderBy('name')
            ->paginate()
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return Inertia::render('ItemList/Index', [
            'items' => $items->items(),
            'pagination' => $items->toArray(),
            'categories' => $categories,
        ]);
    }

    public function store(ItemRequest $request)
    {
        Gate::authorize('manage-inventory');

        $validated = $request->validated();

        Item::create($validated);

        return redirect()->route('items.index');
    }

    public function update(ItemRequest $request, Item $item)
    {
        Gate::authorize('manage-inventory');

        $validated = $request->validated();

        $item->update($validated);

        return redirect()->route('items.index');
    }

    public function destroy(Item $item)
    {
        Gate::authorize('manage-inventory');

        $item->delete();
        return redirect()->route('items.index');
    }
}
