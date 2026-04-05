<?php

use App\Models\Category;
use App\Models\Item;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(RolePermissionSeeder::class);
});

// [2.3.1] Required fields
test('item with missing name is rejected', function () {
    $category = Category::factory()->create();

    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'category_id' => $category->id,
            'quantity'    => 10,
            'price'       => 9.99,
        ])
        ->assertSessionHasErrors('name');
});

test('item with non-existent category_id is rejected', function () {
    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'name'        => 'Widget',
            'category_id' => 99999,
            'quantity'    => 10,
            'price'       => 9.99,
        ])
        ->assertSessionHasErrors('category_id');
});

test('category with duplicate name is rejected', function () {
    Category::factory()->create(['name' => 'Electronics']);

    $this->actingAs(managerUser())
        ->post(route('categories.store'), ['name' => 'Electronics'])
        ->assertSessionHasErrors('name');
});

// [2.3.2] Range / numeric validation
test('item with negative quantity is rejected', function () {
    $category = Category::factory()->create();

    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'name'        => 'Widget',
            'category_id' => $category->id,
            'quantity'    => -1,
            'price'       => 9.99,
        ])
        ->assertSessionHasErrors('quantity');
});

test('item with negative price is rejected', function () {
    $category = Category::factory()->create();

    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'name'        => 'Widget',
            'category_id' => $category->id,
            'quantity'    => 10,
            'price'       => -5,
        ])
        ->assertSessionHasErrors('price');
});

test('order with item quantity less than 1 is rejected', function () {
    $category = Category::factory()->create();
    $item     = Item::factory()->create(['category_id' => $category->id, 'quantity' => 100]);

    $this->actingAs(customerUser())
        ->post(route('orders.store'), [
            'type'           => 'dine_in',
            'status'         => 'pending',
            'payment_method' => 'cash',
            'amount_paid'    => 100,
            'items'          => [['id' => $item->id, 'quantity' => 0]],
        ])
        ->assertSessionHasErrors('items.0.quantity');
});

test('order with no items is rejected', function () {
    $this->actingAs(customerUser())
        ->post(route('orders.store'), [
            'type'           => 'dine_in',
            'status'         => 'pending',
            'payment_method' => 'cash',
            'amount_paid'    => 0,
            'items'          => [],
        ])
        ->assertSessionHasErrors('items');
});

// [2.3.3] Length validation
test('item with name exceeding 255 characters is rejected', function () {
    $category = Category::factory()->create();

    $this->actingAs(managerUser())
        ->post(route('items.store'), [
            'name'        => str_repeat('a', 256),
            'category_id' => $category->id,
            'quantity'    => 10,
            'price'       => 9.99,
        ])
        ->assertSessionHasErrors('name');
});
