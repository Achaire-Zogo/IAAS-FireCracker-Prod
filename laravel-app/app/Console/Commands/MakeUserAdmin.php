<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email : The email of the user to make admin}';
    protected $description = 'Make a user an administrator';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }

        $user->update(['role' => 'admin']);
        $this->info("User {$user->name} ({$user->email}) is now an administrator");

        return 0;
    }
}
