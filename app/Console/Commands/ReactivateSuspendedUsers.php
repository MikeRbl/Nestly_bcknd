<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReactivateSuspendedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reactivates users whose suspension period has ended.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
{
    $users = User::where('status', 'suspendido')
                 ->where('suspension_ends_at', '<=', now())
                 ->get();

    foreach ($users as $user) {
        $user->status = 'activo';
        $user->suspension_ends_at = null;
        $user->save();
        $this->info("User {$user->email} has been reactivated.");
    }

    $this->info('Suspended users checked and reactivated successfully.');
}
}
