<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('categories are seeded correctly', function () {
    $this->seed();

    $this->assertDatabaseCount('categories', 15);
    $this->assertDatabaseHas('categories', ['name' => 'Salary', 'type' => 'income']);
    $this->assertDatabaseHas('categories', ['name' => 'Groceries', 'type' => 'expense']);
});

test('user cannot create system categories', function () {
    // This test will be implemented when Category model and policies are created
    expect(true)->toBeTrue();
});

test('user can list all categories', function () {
    // This test will be implemented when Category routes and controller are created
    expect(true)->toBeTrue();
});
