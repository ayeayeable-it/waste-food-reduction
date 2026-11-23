<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservationsCommand extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Automatically expire overdue pending reservations and conditionally restore stock.';

    public function handle(ReservationService $reservationService): int
    {
        $count = $reservationService->expireOverdueReservations();
        $this->info("Processed {$count} overdue reservation(s).");

        return self::SUCCESS;
    }
}
