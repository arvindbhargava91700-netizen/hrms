<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schema = \Illuminate\Support\Facades\Schema::getColumnListing('bookings');
        $this->info("Bookings columns: " . implode(', ', $schema));

        $schema = \Illuminate\Support\Facades\Schema::getColumnListing('invoices');
        $this->info("Invoices columns: " . implode(', ', $schema));
    }
}
