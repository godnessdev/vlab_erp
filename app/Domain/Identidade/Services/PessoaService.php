<?php

namespace App\Domain\Identidade\Services;

use App\Domain\Identidade\Enums\StatusPessoa;
use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Enums\TipoPessoa;
use App\Domain\Identidade\Models\Documento;
use App\Domain\Identidade\Models\Papel;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PessoaService
{
    public function __construct(
        private DocumentoValidator $documentoValidator
    ) {}

    /**
     * Criar uma nova pessoa com dados completos
     */
    public function criar(array $dadosPessoa): Pessoa
    {
        return DB::transaction(function () use ($dadosPessoa) {
            // Validar dados principais
            $this->validarDadosPessoa($dadosPessoa);

            // Criar pessoa
            $pessoa = Pessoa::create([
                'tipo' => $dadosPessoa['tipo'],
                'nome_razao_social' => $dadosPessoa['nome_razao_social'],
                'nome_fantasia' => $dadosPessoa['nome_fantasia'] ?? null,
                'data_nascimento_constituicao' => $dadosPessoa['data_nascimento_constituicao'] ?? null,
                'status' => StatusPessoa::ATIVO,
            ]);

            // Adicionar documentos
            if (isset($dadosPessoa['documentos'])) {
                $this->adicionarDocumentos($pessoa, $dadosPessoa['documentos']);
            }

            // Adicionar endereços
            if (isset($dadosPessoa['enderecos'])) {
                $this->adicionarEnderecos($pessoa, $dadosPessoa['enderecos']);
            }

            // Adicionar contatos
            if (isset($dadosPessoa['contatos'])) {
                $this->adicionarContatos($pessoa, $dadosPessoa['contatos']);
            }

            return $pessoa->load(['documentos', 'enderecos', 'contatos']);
        });
    }

    /**
     * Atualizar dados de uma pessoa
     */
    public function atualizar(Pessoa $pessoa, array $dadosPessoa): Pessoa
    {
        return DB::transaction(function () use ($pessoa, $dadosPessoa) {
            // Validar dados principais (sem validação de tipo pois não muda)
            if (isset($dadosPessoa['tipo'])) {
                $this->validarDadosPessoa($dadosPessoa, $pessoa);
            } else {
                // Validar apenas nome se tipo não fornecido
                if (isset($dadosPessoa['nome_razao_social']) &&
                    (empty($dadosPessoa['nome_razao_social']) || strlen($dadosPessoa['nome_razao_social']) < 2)) {
                    $validator = Validator::make($dadosPessoa, []);
                    $validator->errors()->add('nome_razao_social', 'Nome/Razão Social deve ter pelo menos 2 caracteres');
                    throw new ValidationException($validator);
                }
            }

            // Atualizar dados principais
            $updateData = array_filter([
                'nome_razao_social' => $dadosPessoa['nome_razao_social'] ?? null,
                'nome_fantasia' => $dadosPessoa['nome_fantasia'] ?? null,
                'data_nascimento_constituicao' => $dadosPessoa['data_nascimento_constituicao'] ?? null,
            ], fn ($value) => ! is_null($value));

            $pessoa->update($updateData);

            // Atualizar documentos se fornecidos
            if (isset($dadosPessoa['documentos'])) {
                $this->atualizarDocumentos($pessoa, $dadosPessoa['documentos']);
            }

            // Atualizar endereços se fornecidos
            if (isset($dadosPessoa['enderecos'])) {
                $this->atualizarEnderecos($pessoa, $dadosPessoa['enderecos']);
            }

            // Atualizar contatos se fornecidos
            if (isset($dadosPessoa['contatos'])) {
                $this->atualizarContatos($pessoa, $dadosPessoa['contatos']);
            }

            return $pessoa->fresh(['documentos', 'enderecos', 'contatos']);
        });
    }

    /**
     * Buscar pessoa por documento
     */
    public function buscarPorDocumento(string $documento): ?Pessoa
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);

        return Pessoa::whereHas('documentos', function ($query) use ($documento) {
            $query->where('valor', $documento);
        })->first();
    }

    /**
     * Buscar pessoas por nome
     */
    public function buscarPorNome(string $nome): Collection
    {
        return Pessoa::where(function ($query) use ($nome) {
            $query->where('nome_razao_social', 'like', "%{$nome}%")
                ->orWhere('nome_fantasia', 'like', "%{$nome}%");
        })
            ->where('status', StatusPessoa::ATIVO)
            ->get();
    }

    /**
     * Inativar pessoa
     */
    public function inativar(Pessoa $pessoa): bool
    {
        return DB::transaction(function () use ($pessoa) {
            // Inativar a pessoa
            $pessoa->inativar();

            // Inativar papéis ativos
            $pessoa->papeisAtivos()->update([
                'status' => 'INATIVO',
                'data_fim' => now(),
            ]);

            return true;
        });
    }

    /**
     * Ativar pessoa
     */
    public function ativar(Pessoa $pessoa): bool
    {
        $pessoa->ativar();

        return true;
    }

    /**
     * Adicionar papel à pessoa
     */
    public function adicionarPapel(
        Pessoa $pessoa,
        string $tipoPapel,
        string $empresaId,
        array $dadosEspecificos = []
    ): Papel {
        // Verificar se já possui este papel na empresa
        if ($pessoa->possuiPapel($tipoPapel, $empresaId)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('papel', 'Pessoa já possui este papel nesta empresa');
            throw new ValidationException($validator);
        }

        return $pessoa->adicionarPapel($tipoPapel, $empresaId, $dadosEspecificos);
    }

    /**
     * Remover papel da pessoa
     */
    public function removerPapel(
        Pessoa $pessoa,
        string $tipoPapel,
        string $empresaId
    ): bool {
        $papel = $pessoa->papeis()
            ->where('tipo_papel', $tipoPapel)
            ->where('empresa_id', $empresaId)
            ->where('status', 'ATIVO')
            ->first();

        if (! $papel) {
            return false;
        }

        $papel->inativar();

        return true;
    }

    /**
     * Validar dados da pessoa
     */
    private function validarDadosPessoa(array $dados, ?Pessoa $pessoa = null): void
    {
        // Para atualização, usar o tipo da pessoa existente se não fornecido
        if ($pessoa && ! isset($dados['tipo'])) {
            $tipo = $pessoa->tipo;
        } else {
            $tipo = TipoPessoa::from($dados['tipo']);
        }

        // Validar nome
        if (empty($dados['nome_razao_social']) || strlen($dados['nome_razao_social']) < 2) {
            $validator = Validator::make($dados, []);
            $validator->errors()->add('nome_razao_social', 'Nome/Razão Social deve ter pelo menos 2 caracteres');
            throw new ValidationException($validator);
        }

        // Validar coerência entre tipo e dados
        if ($tipo->isJuridica()) {
            // Para pessoa jurídica, pode ter nome fantasia
        } else {
            // Para pessoa física, não deve ter nome fantasia
            if (isset($dados['nome_fantasia']) && ! empty($dados['nome_fantasia'])) {
                $validator = Validator::make($dados, []);
                $validator->errors()->add('nome_fantasia', 'Pessoa física não pode ter nome fantasia');
                throw new ValidationException($validator);
            }
        }

        // Validar documentos se fornecidos
        if (isset($dados['documentos'])) {
            $this->validarDocumentos($dados['documentos'], $tipo, $pessoa);
        }
    }

    /**
     * Validar documentos
     */
    private function validarDocumentos(array $documentos, TipoPessoa $tipoPessoa, ?Pessoa $pessoa = null): void
    {
        foreach ($documentos as $documento) {
            // Validar formato do documento
            $tipoDocumento = TipoDocumento::from($documento['tipo']);

            if (! $this->documentoValidator->validar($tipoDocumento, $documento['valor'])) {
                $validator = Validator::make($documento, []);
                $validator->errors()->add('documentos', "Documento {$tipoDocumento->label()} inválido");
                throw new ValidationException($validator);
            }

            // Validar coerência tipo pessoa x tipo documento
            if ($tipoPessoa->isFisica() && $tipoDocumento->value === 'CNPJ') {
                $validator = Validator::make([], []);
                $validator->errors()->add('documentos', 'Pessoa física não pode ter CNPJ');
                throw new ValidationException($validator);
            }

            if ($tipoPessoa->isJuridica() && $tipoDocumento->value === 'CPF') {
                $validator = Validator::make([], []);
                $validator->errors()->add('documentos', 'Pessoa jurídica não pode ter CPF');
                throw new ValidationException($validator);
            }

            // Verificar unicidade do documento
            $this->verificarUnicidadeDocumento($documento['valor'], $tipoDocumento, $pessoa);
        }
    }

    /**
     * Verificar unicidade do documento
     */
    private function verificarUnicidadeDocumento(string $valor, TipoDocumento $tipo, ?Pessoa $pessoa = null): void
    {
        $valor = preg_replace('/[^0-9]/', '', $valor);

        $query = Documento::where('tipo', $tipo)->where('valor', $valor);

        if ($pessoa) {
            $query->where('pessoa_id', '!=', $pessoa->id);
        }

        if ($query->exists()) {
            $validator = Validator::make([], []);
            $validator->errors()->add('documento', "Documento {$tipo->label()} já está em uso");
            throw new ValidationException($validator);
        }
    }

    /**
     * Adicionar documentos
     */
    private function adicionarDocumentos(Pessoa $pessoa, array $documentos): void
    {
        foreach ($documentos as $documento) {
            $pessoa->documentos()->create([
                'tipo' => $documento['tipo'],
                'valor' => preg_replace('/[^0-9]/', '', $documento['valor']),
                'data_emissao' => $documento['data_emissao'] ?? null,
                'orgao_emissor' => $documento['orgao_emissor'] ?? null,
                'valido' => true,
            ]);
        }
    }

    /**
     * Adicionar endereços
     */
    private function adicionarEnderecos(Pessoa $pessoa, array $enderecos): void
    {
        foreach ($enderecos as $endereco) {
            $pessoa->enderecos()->create($endereco);
        }
    }

    /**
     * Adicionar contatos
     */
    private function adicionarContatos(Pessoa $pessoa, array $contatos): void
    {
        foreach ($contatos as $contato) {
            $pessoa->contatos()->create($contato);
        }
    }

    /**
     * Atualizar documentos
     */
    private function atualizarDocumentos(Pessoa $pessoa, array $documentos): void
    {
        // Por simplicidade, vamos remover e recriar
        $pessoa->documentos()->delete();
        $this->adicionarDocumentos($pessoa, $documentos);
    }

    /**
     * Atualizar endereços
     */
    private function atualizarEnderecos(Pessoa $pessoa, array $enderecos): void
    {
        // Por simplicidade, vamos remover e recriar
        $pessoa->enderecos()->delete();
        $this->adicionarEnderecos($pessoa, $enderecos);
    }

    /**
     * Atualizar contatos
     */
    private function atualizarContatos(Pessoa $pessoa, array $contatos): void
    {
        // Por simplicidade, vamos remover e recriar
        $pessoa->contatos()->delete();
        $this->adicionarContatos($pessoa, $contatos);
    }

    /**
     * Validar se pessoa pode ser excluída
     */
    public function podeExcluir(Pessoa $pessoa): bool
    {
        // Não pode excluir se tem papéis ativos
        if ($pessoa->papeisAtivos()->count() > 0) {
            return false;
        }

        // Outras validações serão implementadas conforme outros domínios
        return true;
    }

    /**
     * Excluir pessoa (soft delete)
     */
    public function excluir(Pessoa $pessoa): bool
    {
        if (! $this->podeExcluir($pessoa)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('exclusao', 'Não é possível excluir esta pessoa pois possui vínculos ativos');
            throw new ValidationException($validator);
        }

        return (bool) $pessoa->delete();
    }
}
