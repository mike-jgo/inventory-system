<?php

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order creation deducts stock', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = Item::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'price' => 100
    ]);

    $response = $this->actingAs($user)->post('/orders', [
        'customer_name' => 'Test Customer',
        'type' => 'dine_in',
        'items' => [
            ['id' => $item->id, 'quantity' => 2]
        ]
    ]);

    $response->assertRedirect('/orders');

    expect($item->fresh()->quantity)->toBe(8);
    expect(Order::count())->toBe(1);
    expect((float) Order::first()->total_amount)->toBe(200.0);
});

test('cancelling order without wastage restocks items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = Item::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'price' => 100
    ]);

    // Create order logic manually or via factory to speed up
    $order = Order::create([
        'user_id' => $user->id,
        'type' => 'dine_in',
        'status' => 'completed',
        'total_amount' => 200
    ]);

    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => 2,
        'price' => 100
    ]);

    // Simulate stock deduction
    $item->update(['quantity' => 8]);

    $response = $this->actingAs($user)->put("/orders/{$order->id}", [
        'status' => 'cancelled',
        'wastage' => false
    ]);

    $response->assertRedirect();
    expect($item->fresh()->quantity)->toBe(10); // Restocoked
    expect($order->fresh()->status)->toBe('cancelled');
});

test('cancelling order WITH wastage DOES NOT restock items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = Item::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'price' => 100
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'type' => 'dine_in',
        'status' => 'completed',
        'total_amount' => 200
    ]);

    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => 2,
        'price' => 100
    ]);

    // Simulate stock deduction
    $item->update(['quantity' => 8]);

    $response = $this->actingAs($user)->put("/orders/{$order->id}", [
        'status' => 'cancelled',
        'wastage' => true
    ]);

    $response->assertRedirect();
    expect($item->fresh()->quantity)->toBe(8); // NOT Restocked
    expect($order->fresh()->status)->toBe('cancelled');
});
