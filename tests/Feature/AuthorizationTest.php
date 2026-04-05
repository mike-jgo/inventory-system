<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(RolePermissionSeeder::class);
});

// [2.2.2] Guests are redirected, never silently allowed
test('guest is redirected to login when accessing items', function () {
    $this->get(route('items.index'))->assertRedirect(route('login'));
});

test('guest is redirected to login when accessing orders', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

// [2.2.3] Customer forbidden routes
test('customer cannot GET /items (403)', function () {
    $this->actingAs(customerUser())
        ->get(route('items.index'))
        ->assertStatus(403);
});

test('customer cannot POST /items (403)', function () {
    $this->actingAs(customerUser())
        ->post(route('items.store'), [])
        ->assertStatus(403);
});

test('customer cannot PUT /items/{id} (403)', function () {
    $category = Category::factory()->create();
    $item = Item::factory()->create(['category_id' => $category->id]);

    $this->actingAs(customerUser())
        ->put(route('items.update', $item), [])
        ->assertStatus(403);
});

test('customer cannot DELETE /items/{id} (403)', function () {
    $category = Category::factory()->create();
    $item = Item::factory()->create(['category_id' => $category->id]);

    $this->actingAs(customerUser())
        ->delete(route('items.destroy', $item))
        ->assertStatus(403);
});

test('customer cannot GET /categories (403)', function () {
    $this->actingAs(customerUser())
        ->get(route('categories.index'))
        ->assertStatus(403);
});

// [2.2.3, 2.4.4] Customer cannot access activity log
test('customer cannot GET /activity-log (403)', function () {
    $this->actingAs(customerUser())
        ->get(route('activity-log.index'))
        ->assertStatus(403);
});

// [2.2.3] Customer cannot access inventory
test('customer cannot GET /inventory (403)', function () {
    $this->actingAs(customerUser())
        ->get(route('inventory.index'))
        ->assertStatus(403);
});

// [2.2.3] Customer cannot access user management
test('customer cannot GET /users (403)', function () {
    $this->actingAs(customerUser())
        ->get(route('users.index'))
        ->assertStatus(403);
});

// [2.2.3] Customer CAN access orders
test('customer can GET /orders (200)', function () {
    $this->actingAs(customerUser())
        ->get(route('orders.index'))
        ->assertStatus(200);
});

// [2.2.3] Customer can only see their own orders
test('customer can only see their own orders in index', function () {
    $customer = customerUser();
    $other    = customerUser();

    $ownOrder   = Order::factory()->create(['user_id' => $customer->id]);
    $otherOrder = Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($customer)
        ->get(route('orders.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $ownOrder->id)
        );
});

// [2.2.3] Customer cannot view another user's order detail
test('customer cannot view another user order show (403)', function () {
    $customer = customerUser();
    $other    = customerUser();
    $order    = Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($customer)
        ->get(route('orders.show', $order))
        ->assertStatus(403);
});

// [2.2.3] Product Manager routes
test('product manager can GET /items (200)', function () {
    $this->actingAs(managerUser())
        ->get(route('items.index'))
        ->assertStatus(200);
});

test('product manager can POST /items (redirect, not 403)', function () {
    $category = Category::factory()->create();

    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'name'        => 'Test Item',
            'category_id' => $category->id,
            'quantity'    => 10,
            'price'       => 9.99,
        ])
        ->assertRedirect();
});

test('product manager cannot GET /inventory (403)', function () {
    $this->actingAs(managerUser())
        ->get(route('inventory.index'))
        ->assertStatus(403);
});

test('product manager cannot GET /users (403)', function () {
    $this->actingAs(managerUser())
        ->get(route('users.index'))
        ->assertStatus(403);
});

// [2.4.4] Product Manager cannot access activity log
test('product manager cannot GET /activity-log (403)', function () {
    $this->actingAs(managerUser())
        ->get(route('activity-log.index'))
        ->assertStatus(403);
});

// [2.2.3] Super Admin routes
test('super admin can GET /inventory (200)', function () {
    $this->actingAs(adminUser())
        ->get(route('inventory.index'))
        ->assertStatus(200);
});

test('super admin can GET /users (200)', function () {
    $this->actingAs(adminUser())
        ->get(route('users.index'))
        ->assertStatus(200);
});

// [2.4.4] Super Admin can access activity log
test('super admin can GET /activity-log (200)', function () {
    $this->actingAs(adminUser())
        ->get(route('activity-log.index'))
        ->assertStatus(200);
});
