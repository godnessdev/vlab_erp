<?php

namespace App\Jobs;

use App\Domain\Financeiro\FinanceiroService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AtualizarStatusFinanceiroJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FinanceiroService $service): void
    {
        $service->atualizarStatusAutomatico();
    }
}
