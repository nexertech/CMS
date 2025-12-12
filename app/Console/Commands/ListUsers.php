<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users in the database';

    public function handle()
    {
        $this->info('All Users in Database:');
        $this->info('====================');
        
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->warn('No users found in database.');
            return;
        }
        
        $this->table(
            ['ID', 'Username', 'Email', 'Phone', 'Role ID', 'Status', 'Created At'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->username,
                    $user->email ?? 'N/A',
                    $user->phone ?? 'N/A',
                    $user->role_id ?? 'N/A',
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s')
                ];
            })
        );
        
        $this->info('Total users: ' . $users->count());
    }
}
