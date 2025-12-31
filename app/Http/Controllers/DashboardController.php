<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get summary statistics
        $totalItems = Item::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStockItems = Item::where('quantity', '<=', 10)->count();

        // Get recent orders
        $recentOrders = Order::with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalItems' => $totalItems,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'lowStockItems' => $lowStockItems,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }
}
