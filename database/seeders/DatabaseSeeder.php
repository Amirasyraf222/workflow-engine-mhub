<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Project;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $salesManagerRole = Role::create(['name' => 'Sales Manager', 'code' => 'sales_manager']);
        $financeManagerRole = Role::create(['name' => 'Finance Manager', 'code' => 'finance_manager']);
        $salesCoordinatorRole = Role::create(['name' => 'Sales Coordinator', 'code' => 'sales_coordinator']);

        $salesManager = User::create([
            'name' => 'Beckham - Sales Manager',
            'email' => 'beckham@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $financeManager = User::create([
            'name' => 'Ronaldinho - Finance Manager',
            'email' => 'ronaldinho@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $coordinator = User::create([
            'name' => 'Beyonce - Coordinator',
            'email' => 'beyonce@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $backupManager = User::create([
            'name' => 'Justine Bieber - Junior Sales Manager',
            'email' => 'bieber@example.com',
            'password' => Hash::make('password'),
        ]);

        $salesManager->roles()->attach($salesManagerRole);
        $backupManager->roles()->attach($salesManagerRole);
        $financeManager->roles()->attach($financeManagerRole);
        $coordinator->roles()->attach($salesCoordinatorRole);

        $projectA = Project::create(['name' => 'Seri Harmoni']);
        $projectB = Project::create(['name' => 'Taman Mutiara']);

        $units = collect();

        foreach (range(1, 5) as $i) {
            $units->push(Unit::create([
                'project_id' => $projectA->id,
                'unit_no' => 'A-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'status' => $i <= 3 ? 'booked' : 'available',
            ]));
        }

        foreach (range(1, 5) as $i) {
            $units->push(Unit::create([
                'project_id' => $projectB->id,
                'unit_no' => 'B-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'status' => $i <= 2 ? 'booked' : 'available',
            ]));
        }

        $agent1 = Agent::create(['name' => 'Jessie Agent', 'email' => 'jessie@example.com']);
        $agent2 = Agent::create(['name' => 'Salman Agent', 'email' => 'salman@example.com']);
        $agent3 = Agent::create(['name' => 'Ah Chong Agent', 'email' => 'chong@example.com']);

        Booking::create([
            'booking_no' => 'BK-001',
            'unit_id' => $units[0]->id,
            'agent_id' => $agent1->id,
            'requested_by_user_id' => $coordinator->id,
            'status' => 'confirmed',
            'buyer_name' => 'Buyer One',
        ]);

        Booking::create([
            'booking_no' => 'BK-002',
            'unit_id' => $units[1]->id,
            'agent_id' => $agent2->id,
            'requested_by_user_id' => $coordinator->id,
            'status' => 'confirmed',
            'buyer_name' => 'Buyer Two',
        ]);

        Booking::create([
            'booking_no' => 'BK-003',
            'unit_id' => $units[2]->id,
            'agent_id' => $agent3->id,
            'requested_by_user_id' => $coordinator->id,
            'status' => 'confirmed',
            'buyer_name' => 'Buyer Three',
        ]);

        Booking::create([
            'booking_no' => 'BK-004',
            'unit_id' => $units[5]->id,
            'agent_id' => $agent1->id,
            'requested_by_user_id' => $coordinator->id,
            'status' => 'confirmed',
            'buyer_name' => 'Buyer Four',
        ]);

        Booking::create([
            'booking_no' => 'BK-005',
            'unit_id' => $units[6]->id,
            'agent_id' => $agent2->id,
            'requested_by_user_id' => $coordinator->id,
            'status' => 'confirmed',
            'buyer_name' => 'Buyer Five',
        ]);
    }
}