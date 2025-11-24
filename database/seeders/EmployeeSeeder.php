<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee; // Import the Employee model

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '09123456789',
            'address' => '123 Main St, Anytown',
            'role' => 'teacher',
        ]);

        Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '09987654321',
            'address' => '456 Oak Ave, Anytown',
            'role' => 'staff',
        ]);

        Employee::create([
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'name' => 'Dr. Alice Brown',
            'email' => 'alice.brown@example.com',
            'phone' => '09001112222',
            'address' => '789 Pine Rd, Anytown',
            'role' => 'academic_head',
        ]);

        Employee::create([
            'first_name' => 'Robert',
            'last_name' => 'Davis',
            'name' => 'Mr. Robert Davis',
            'email' => 'robert.davis@example.com',
            'phone' => '09334445555',
            'address' => '101 Cedar Ln, Anytown',
            'role' => 'hr',
        ]);

        Employee::create([
            'first_name' => 'Emily',
            'last_name' => 'White',
            'name' => 'Ms. Emily White',
            'email' => 'emily.white@example.com',
            'phone' => '09667778888',
            'address' => '202 Birch Blvd, Anytown',
            'role' => 'admin',
        ]);
    }
}