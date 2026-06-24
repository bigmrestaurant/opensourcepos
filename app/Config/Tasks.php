<?php

namespace Config;

use CodeIgniter\Tasks\Config\Tasks as BaseTasks;
use CodeIgniter\Tasks\Scheduler;

class Tasks extends BaseTasks
{
    /**
     * Register any tasks within this method for the application.
     * The Scheduler is provided for you.
     */
    public function init(Scheduler $schedule): void
    {
        /**
         * Retry any sales where the PRA or FBR fiscal API call failed.
         * singleInstance() prevents a second run from starting if the previous
         * one is still in progress (e.g. slow API response).
         * Runs every 30 minutes; the system cron fires tasks:run every minute
         * and the library handles the 30-minute throttle internally.
         */
        $schedule->command('fiscal:retry')
            ->everyThirtyMinutes()
            ->named('fiscal-retry')
            ->singleInstance();
    }
}
