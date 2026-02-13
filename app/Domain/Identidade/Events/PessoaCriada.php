<?php

namespace App\Domain\Identidade\Events;

use App\Domain\Identidade\Models\Pessoa;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PessoaCriada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Pessoa $pessoa
    ) {
        //
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'pessoa',
            'identidade',
            "pessoa:{$this->pessoa->id}",
            "tipo:{$this->pessoa->tipo->value}",
        ];
    }
}
