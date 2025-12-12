<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestUserCreation extends Command
{
    protected $signature = 'test:user-creation';
    protected $description = 'Test user creation to debug registration issue';

    public function handle()
    {
        $this->info('Testing user creation...');
        
        try {
            // Check if user already exists
            $existingUser = User::where('username', 'testuser')->first();
            if ($existingUser) {
                $this->info('Test user already exists. Deleting...');
                $existingUser->delete();
            }
            
            // Create a test user
            $user = User::create([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'password' => Hash::make('password123'),
                'role_id' => 4, // Client role
                'status' => 'active',
            ]);
            
            $this->info('User created successfully!');
            $this->info('User ID: ' . $user->id);
            $this->info('Username: ' . $user->username);
            $this->info('Email: ' . $user->email);
            
            // Verify the user was saved
            $savedUser = User::find($user->id);
            if ($savedUser) {
                $this->info('User verification: SUCCESS - User found in database');
            } else {
                $this->error('User verification: FAILED - User not found in database');
            }
            
            // Show total user count
            $totalUsers = User::count();
            $this->info('Total users in database: ' . $totalUsers);
            
        } catch (\Exception $e) {
            $this->error('Error creating user: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
