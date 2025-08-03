<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

// Create Super Admin
$superAdmin = User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
$superAdmin->assignRole('Super Admin');

// Create Admin
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
$admin->assignRole('Admin');

// Create Editor
$editor = User::create([
    'name' => 'Editor User',
    'email' => 'editor@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
$editor->assignRole('Editor');

// Create Author
$author = User::create([
    'name' => 'Author User',
    'email' => 'author@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
$author->assignRole('Author');

// Create Subscriber
$subscriber = User::create([
    'name' => 'Subscriber User',
    'email' => 'subscriber@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
$subscriber->assignRole('Subscriber');

echo "All test users created successfully!\n";
echo "Super Admin: superadmin@test.com\n";
echo "Admin: admin@test.com\n";
echo "Editor: editor@test.com\n";
echo "Author: author@test.com\n";
echo "Subscriber: subscriber@test.com\n";
echo "All passwords: password123\n"; 