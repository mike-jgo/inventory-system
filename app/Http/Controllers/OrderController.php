<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'type']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['orderItems.item', 'user']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function create()
    {
        $categories = Category::with([
            'items'
        ])->get();

        return Inertia::render('Orders/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'type' => 'required|in:dine_in,takeout,online',
            'status' => 'required|in:pending,completed,cancelled', // Allow setting status
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $itemsToProcess = [];

            // Calculate total and prepare items
            foreach ($validated['items'] as $itemData) {
                $item = Item::lockForUpdate()->find($itemData['id']);

                if ($item->quantity < $itemData['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Not enough stock for item: {$item->name}"],
                    ]);
                }

                $totalAmount += $item->price * $itemData['quantity'];
                $itemsToProcess[] = ['model' => $item, 'quantity' => $itemData['quantity'], 'price' => $item->price];
            }

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'type' => $validated['type'],
                'status' => $validated['status'], // Use validated status
                'total_amount' => $totalAmount,
            ]);

            // Create Order Items and Update Stock
            foreach ($itemsToProcess as $processItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $processItem['model']->id,
                    'quantity' => $processItem['quantity'],
                    'price' => $processItem['price'],
                ]);

                $processItem['model']->decrement('quantity', $processItem['quantity']);
            }

            // activity()
            //     ->causedBy(Auth::user())
            //     ->performedOn($order)
            //     ->log("Created order #{$order->id} for \${$totalAmount} ({$order->status})");
            // Removed for automated logging

            return redirect()->route('orders.index');
        });
    }

    public function edit(Order $order)
    {
        if ($order->status === 'cancelled' && !$order->wastage && !request()->user()->hasRole('Super Admin')) {
            return redirect()->route('orders.index')->withErrors('Cannot edit cancelled orders.');
        }

        $order->load(['orderItems.item']);

        $categories = Category::with([
            'items' => function ($query) {
            }
        ])->get();

        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        // Cancel Logic
        if ($request->has('wastage')) {
            $validated = $request->validate([
                'wastage' => 'required|boolean',
                'status' => 'required|in:cancelled'
            ]);

            if ($order->status === 'cancelled') {
                return back()->withErrors(['status' => 'Order is already cancelled']);
            }

            DB::transaction(function () use ($order, $validated) {
                $order->update([
                    'status' => 'cancelled',
                    'wastage' => $validated['wastage']
                ]);

                if (!$validated['wastage']) {
                    $orderItems = $order->orderItems;
                    foreach ($orderItems as $orderItem) {
                        $item = Item::lockForUpdate()->find($orderItem->item_id);
                        if ($item) {
                            $item->increment('quantity', $orderItem->quantity);
                        }
                    }
                }

                // activity()
                //     ->causedBy(Auth::user())
                //     ->performedOn($order)
                //     ->withProperties(['wastage' => $validated['wastage']])
                //     ->log("Cancelled order #{$order->id}");
            });

            return redirect()->back();
        }

        // UPDATE Logic (General)
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'type' => 'required|in:dine_in,takeout,online',
            'status' => 'required|in:pending,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($order, $validated) {

            // 1. Sync Items
            $currentItems = $order->orderItems->keyBy('item_id');
            $newItemsData = collect($validated['items'])->keyBy('id');

            $totalAmount = 0;

            // Handle updates and additions
            foreach ($newItemsData as $itemId => $data) {
                $newQty = $data['quantity'];
                $itemModel = Item::lockForUpdate()->find($itemId);
                $price = $itemModel->price; // Use current price? Or keep old price? Usually current price for new/updated items.

                if (isset($currentItems[$itemId])) {
                    // Existing item in order
                    $oldQty = $currentItems[$itemId]->quantity;
                    $diff = $newQty - $oldQty;

                    if ($diff > 0) {
                        // Increased quantity: check stock
                        if ($itemModel->quantity < $diff) {
                            throw ValidationException::withMessages([
                                'items' => ["Not enough stock to increase {$itemModel->name}"],
                            ]);
                        }
                        $itemModel->decrement('quantity', $diff);
                    } elseif ($diff < 0) {
                        // Decreased quantity: return to stock
                        $itemModel->increment('quantity', abs($diff));
                    }

                    // Update OrderItem
                    $currentItems[$itemId]->update([
                        'quantity' => $newQty,
                        'price' => $price
                    ]);
                } else {
                    // New item
                    if ($itemModel->quantity < $newQty) {
                        throw ValidationException::withMessages([
                            'items' => ["Not enough stock for new item: {$itemModel->name}"],
                        ]);
                    }
                    $itemModel->decrement('quantity', $newQty);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $itemId,
                        'quantity' => $newQty,
                        'price' => $price
                    ]);
                }
                $totalAmount += $price * $newQty;
            }

            // Handle removals
            foreach ($currentItems as $itemId => $orderItem) {
                if (!isset($newItemsData[$itemId])) {
                    // Item removed entirely
                    $itemModel = Item::lockForUpdate()->find($itemId);
                    $itemModel->increment('quantity', $orderItem->quantity);
                    $orderItem->delete();
                }
            }

            // 2. Update Order Details
            $order->update([
                'customer_name' => $validated['customer_name'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'total_amount' => $totalAmount
            ]);

            // activity()
            //     ->causedBy(Auth::user())
            //     ->performedOn($order)
            //     ->log("Updated order #{$order->id}");

            return redirect()->route('orders.index');
        });
    }

    public function complete(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending orders can be completed.']);
        }

        $order->update(['status' => 'completed']);

        // activity()
        //     ->causedBy(Auth::user())
        //     ->performedOn($order)
        //     ->log("Completed order #{$order->id}");

        return redirect()->back()->with('success', 'Order completed successfully.');
    }
}
