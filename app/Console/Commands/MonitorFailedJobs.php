<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class MonitorFailedJobs extends Command
{
    protected $signature = 'monitor:failed-jobs';
    protected $description = 'Monitor failed jobs and send alerts';

    public function handle()
    {
        $failedCount = DB::table('failed_jobs')->count();

        if ($failedCount > 10) {
            // Notify admin user (first user or specific admin email)
            $adminEmail = config('app.admin_email');
            
            if ($adminEmail) {
                $admin = User::where('email', $adminEmail)->first();
                
                if ($admin) {
                    $admin->notify(new SystemAlertNotification(
                        'High Failed Jobs Count',
                        "There are {$failedCount} failed jobs in the queue.",
                        'warning'
                    ));
                }
            }

            $this->warn("Alert sent: {$failedCount} failed jobs");
        }

        return ConsoleCommand::SUCCESS;
    }
}