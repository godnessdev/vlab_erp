<?php

namespace App\Domain\Identidade\Observers;

use App\Domain\Identidade\Enums\StatusPessoa;
use App\Domain\Identidade\Events\PessoaAtualizada;
use App\Domain\Identidade\Events\PessoaCriada;
use App\Domain\Identidade\Events\PessoaExcluida;
use App\Domain\Identidade\Events\PessoaInativada;
use App\Domain\Identidade\Models\Pessoa;

class PessoaObserver
{
    /**
     * Handle the Pessoa "creating" event.
     */
    public function creating(Pessoa $pessoa): void
    {
        // Garantir que o status seja definido
        if (! $pessoa->status) {
            $pessoa->status = StatusPessoa::ATIVO;
        }

        // Validar coerência entre tipo e documentos será feita no service
    }

    /**
     * Handle the Pessoa "created" event.
     */
    public function created(Pessoa $pessoa): void
    {
        // Disparar evento de pessoa criada
        event(new PessoaCriada($pessoa));

        // Log da criação
        logger('Pessoa criada', [
            'pessoa_id' => $pessoa->id,
            'tipo' => $pessoa->tipo->value,
            'nome' => $pessoa->nome_razao_social,
        ]);
    }

    /**
     * Handle the Pessoa "updating" event.
     */
    public function updating(Pessoa $pessoa): void
    {
        // Verificar se o tipo foi alterado
        if ($pessoa->isDirty('tipo')) {
            // Validar se a mudança é permitida
            $this->validarMudancaTipo($pessoa);
        }

        // Atualizar data de atualização
        $pessoa->data_atualizacao = now();
    }

    /**
     * Handle the Pessoa "updated" event.
     */
    public function updated(Pessoa $pessoa): void
    {
        // Disparar evento de pessoa atualizada
        event(new PessoaAtualizada($pessoa));

        // Log da atualização
        logger('Pessoa atualizada', [
            'pessoa_id' => $pessoa->id,
            'campos_alterados' => array_keys($pessoa->getDirty()),
        ]);

        // Verificar se o status foi alterado para inativo
        if ($pessoa->wasChanged('status') && $pessoa->status->isInativo()) {
            event(new PessoaInativada($pessoa));
        }
    }

    /**
     * Handle the Pessoa "deleting" event.
     */
    public function deleting(Pessoa $pessoa): void
    {
        // Verificar se a pessoa pode ser excluída
        $this->validarExclusao($pessoa);

        // Inativar papéis relacionados
        $pessoa->papeis()->update([
            'status' => 'INATIVO',
            'data_fim' => now(),
        ]);
    }

    /**
     * Handle the Pessoa "deleted" event.
     */
    public function deleted(Pessoa $pessoa): void
    {
        // Disparar evento de pessoa excluída
        event(new PessoaExcluida($pessoa));

        // Log da exclusão
        logger('Pessoa excluída', [
            'pessoa_id' => $pessoa->id,
            'nome' => $pessoa->nome_razao_social,
        ]);
    }

    /**
     * Handle the Pessoa "force deleted" event.
     */
    public function forceDeleted(Pessoa $pessoa): void
    {
        // Log da exclusão permanente
        logger('Pessoa excluída permanentemente', [
            'pessoa_id' => $pessoa->id,
            'nome' => $pessoa->nome_razao_social,
        ]);
    }

    /**
     * Validar mudança de tipo de pessoa
     */
    private function validarMudancaTipo(Pessoa $pessoa): void
    {
        $tipoOriginal = $pessoa->getOriginal('tipo');
        $novoTipo = $pessoa->tipo;

        // Verificar se há documentos incompatíveis
        $documentosPessoa = $pessoa->documentos;

        foreach ($documentosPessoa as $documento) {
            if ($novoTipo->isFisica() && $documento->tipo->value === 'CNPJ') {
                throw new \InvalidArgumentException(
                    'Não é possível alterar para Pessoa Física quando há CNPJ cadastrado'
                );
            }

            if ($novoTipo->isJuridica() && $documento->tipo->value === 'CPF') {
                throw new \InvalidArgumentException(
                    'Não é possível alterar para Pessoa Jurídica quando há CPF cadastrado'
                );
            }
        }
    }

    /**
     * Validar se a pessoa pode ser excluída
     */
    private function validarExclusao(Pessoa $pessoa): void
    {
        // Verificar se há papéis ativos
        $papeisAtivos = $pessoa->papeisAtivos()->count();
        if ($papeisAtivos > 0) {
            throw new \InvalidArgumentException(
                'Não é possível excluir pessoa com papéis ativos'
            );
        }

        // Verificar se há relacionamentos críticos
        // Será implementado conforme outros domínios sejam criados
    }
}
