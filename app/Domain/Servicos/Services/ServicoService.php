<?php

namespace App\Domain\Servicos\Services;

use App\Domain\Servicos\Enums\StatusServico;
use App\Domain\Servicos\Enums\UnidadeMedida;
use App\Domain\Servicos\Models\CodigoServicoMunicipal;
use App\Domain\Servicos\Models\RegraTributacao;
use App\Domain\Servicos\Models\Servico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicoService
{
    /**
     * Listar serviços com filtros e paginação
     */
    public function listar(
        string $empresaId,
        array $filtros = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Servico::query()
            ->where('empresa_id', $empresaId)
            ->with(['codigosMunicipais', 'regrasTributacao']);

        // Aplicar filtros
        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (! empty($filtros['classificacao_fiscal'])) {
            $query->where('classificacao_fiscal', 'LIKE', "%{$filtros['classificacao_fiscal']}%");
        }

        if (! empty($filtros['descricao'])) {
            $query->where('descricao', 'ILIKE', "%{$filtros['descricao']}%");
        }

        if (! empty($filtros['unidade_medida'])) {
            $query->where('unidade_medida', $filtros['unidade_medida']);
        }

        if (! empty($filtros['preco_min'])) {
            $query->where('preco_base', '>=', $filtros['preco_min']);
        }

        if (! empty($filtros['preco_max'])) {
            $query->where('preco_base', '<=', $filtros['preco_max']);
        }

        return $query->orderBy('descricao')
            ->paginate($perPage);
    }

    /**
     * Criar novo serviço
     */
    public function criar(string $empresaId, array $dados): Servico
    {
        $this->validarDados($dados);

        return DB::transaction(function () use ($empresaId, $dados) {
            $servico = Servico::create([
                'empresa_id' => $empresaId,
                'descricao' => $dados['descricao'],
                'unidade_medida' => UnidadeMedida::from($dados['unidade_medida']),
                'preco_base' => $dados['preco_base'],
                'aliquota_iss_default' => $dados['aliquota_iss_default'],
                'classificacao_fiscal' => $dados['classificacao_fiscal'],
                'observacoes' => $dados['observacoes'] ?? null,
                'status' => StatusServico::ATIVO,
            ]);

            // Criar códigos municipais se fornecidos
            if (! empty($dados['codigos_municipais'])) {
                foreach ($dados['codigos_municipais'] as $codigoData) {
                    $servico->codigosMunicipais()->create($codigoData);
                }
            }

            // Criar regras de tributação se fornecidas
            if (! empty($dados['regras_tributacao'])) {
                foreach ($dados['regras_tributacao'] as $regraData) {
                    $servico->regrasTributacao()->create($regraData);
                }
            }

            return $servico;
        });
    }

    /**
     * Buscar serviço por ID
     */
    public function buscar(string $empresaId, string $servicoId): Servico
    {
        $servico = Servico::where('empresa_id', $empresaId)
            ->where('id', $servicoId)
            ->with(['codigosMunicipais', 'regrasTributacao'])
            ->first();

        if (! $servico) {
            throw ValidationException::withMessages([
                'servico' => 'Serviço não encontrado.',
            ]);
        }

        return $servico;
    }

    /**
     * Atualizar serviço
     */
    public function atualizar(string $empresaId, string $servicoId, array $dados): Servico
    {
        $this->validarDados($dados, true);

        $servico = $this->buscar($empresaId, $servicoId);

        return DB::transaction(function () use ($servico, $dados) {
            $servico->update([
                'descricao' => $dados['descricao'] ?? $servico->descricao,
                'unidade_medida' => isset($dados['unidade_medida'])
                    ? UnidadeMedida::from($dados['unidade_medida'])
                    : $servico->unidade_medida,
                'preco_base' => $dados['preco_base'] ?? $servico->preco_base,
                'aliquota_iss_default' => $dados['aliquota_iss_default'] ?? $servico->aliquota_iss_default,
                'classificacao_fiscal' => $dados['classificacao_fiscal'] ?? $servico->classificacao_fiscal,
                'observacoes' => array_key_exists('observacoes', $dados)
                    ? $dados['observacoes']
                    : $servico->observacoes,
            ]);

            return $servico->fresh();
        });
    }

    /**
     * Inativar serviço
     */
    public function inativar(string $empresaId, string $servicoId): Servico
    {
        $servico = $this->buscar($empresaId, $servicoId);
        $servico->inativar();

        return $servico;
    }

    /**
     * Ativar serviço
     */
    public function ativar(string $empresaId, string $servicoId): Servico
    {
        $servico = $this->buscar($empresaId, $servicoId);
        $servico->ativar();

        return $servico;
    }

    /**
     * Excluir serviço (soft delete)
     */
    public function excluir(string $empresaId, string $servicoId): bool
    {
        $servico = $this->buscar($empresaId, $servicoId);

        // Verificar se pode ser excluído (não tem ordens de serviço, por exemplo)
        // Esta validação seria expandida com outros módulos

        return $servico->delete();
    }

    /**
     * Adicionar código municipal
     */
    public function adicionarCodigoMunicipal(
        string $empresaId,
        string $servicoId,
        array $dadosCodigo
    ): CodigoServicoMunicipal {
        $servico = $this->buscar($empresaId, $servicoId);

        // Encerrar código vigente anterior se existir
        $codigoAnterior = $servico->codigosMunicipais()
            ->where('codigo_municipio_ibge', $dadosCodigo['codigo_municipio_ibge'])
            ->vigentes()
            ->first();

        if ($codigoAnterior) {
            $codigoAnterior->encerrarVigencia(
                now()->subDay()->toDateString()
            );
        }

        return $servico->codigosMunicipais()->create($dadosCodigo);
    }

    /**
     * Adicionar regra de tributação
     */
    public function adicionarRegraTributacao(
        string $empresaId,
        string $servicoId,
        array $dadosRegra
    ): RegraTributacao {
        $servico = $this->buscar($empresaId, $servicoId);

        // Verificar se já existe regra para este regime
        $regraExistente = $servico->regrasTributacao()
            ->where('regime_tributario', $dadosRegra['regime_tributario'])
            ->first();

        if ($regraExistente) {
            throw ValidationException::withMessages([
                'regime_tributario' => 'Já existe uma regra para este regime tributário.',
            ]);
        }

        return $servico->regrasTributacao()->create($dadosRegra);
    }

    /**
     * Calcular tributação de um serviço
     */
    public function calcularTributacao(
        string $empresaId,
        string $servicoId,
        float $valorBase,
        string $regimeTributario,
        ?string $codigoMunicipio = null
    ): array {
        $servico = $this->buscar($empresaId, $servicoId);

        return $servico->calcularValorComTributacao(
            $valorBase,
            $regimeTributario,
            $codigoMunicipio
        );
    }

    /**
     * Buscar serviços ativos para seleção
     */
    public function buscarParaSelecao(string $empresaId, ?string $termo = null): Collection
    {
        $query = Servico::where('empresa_id', $empresaId)
            ->where('status', StatusServico::ATIVO);

        if ($termo) {
            $query->where('descricao', 'ILIKE', "%{$termo}%");
        }

        return $query->select(['id', 'descricao', 'unidade_medida', 'preco_base'])
            ->orderBy('descricao')
            ->limit(20)
            ->get();
    }

    /**
     * Obter estatísticas dos serviços
     */
    public function obterEstatisticas(string $empresaId): array
    {
        $baseQuery = Servico::where('empresa_id', $empresaId);

        return [
            'total' => (clone $baseQuery)->count(),
            'ativos' => (clone $baseQuery)->where('status', StatusServico::ATIVO)->count(),
            'inativos' => (clone $baseQuery)->where('status', StatusServico::INATIVO)->count(),
            'descontinuados' => (clone $baseQuery)->where('status', StatusServico::DESCONTINUADO)->count(),
            'preco_medio' => (clone $baseQuery)->avg('preco_base'),
            'por_unidade_medida' => (clone $baseQuery)->groupBy('unidade_medida')
                ->selectRaw('unidade_medida, count(*) as total')
                ->pluck('total', 'unidade_medida')
                ->toArray(),
        ];
    }

    /**
     * Validar dados do serviço
     */
    private function validarDados(array $dados, bool $isUpdate = false): void
    {
        $regras = [
            'descricao' => 'required|string|max:500|min:3',
            'unidade_medida' => 'required|in:'.implode(',', array_column(UnidadeMedida::cases(), 'value')),
            'preco_base' => 'required|numeric|min:0',
            'aliquota_iss_default' => 'required|numeric|min:0|max:20',
            'classificacao_fiscal' => 'required|string|max:20',
            'observacoes' => 'nullable|string|max:1000',
        ];

        if ($isUpdate) {
            // Para updates, tornar campos opcionais
            foreach ($regras as $campo => $regra) {
                $regras[$campo] = str_replace('required|', 'sometimes|', $regra);
            }
        }

        validator($dados, $regras)->validate();

        // Validações específicas
        if (! empty($dados['classificacao_fiscal'])) {
            $this->validarClassificacaoFiscal($dados['classificacao_fiscal']);
        }

        if (! empty($dados['aliquota_iss_default'])) {
            $this->validarAliquotaISS($dados['aliquota_iss_default']);
        }
    }

    /**
     * Validar classificação fiscal (CNAE)
     */
    private function validarClassificacaoFiscal(string $classificacao): void
    {
        // Validação básica do formato CNAE (7 dígitos + hífen + 2 dígitos)
        if (! preg_match('/^\d{4}-?\d{1}\/?\d{2}$/', $classificacao)) {
            throw ValidationException::withMessages([
                'classificacao_fiscal' => 'Classificação fiscal deve seguir o formato CNAE (ex: 6201-5/00)',
            ]);
        }
    }

    /**
     * Validar alíquota ISS
     */
    private function validarAliquotaISS(float $aliquota): void
    {
        if ($aliquota < 2.0 || $aliquota > 5.0) {
            // A maioria dos municípios trabalha entre 2% e 5%
            // Esta é uma validação orientativa, pode ser customizada
        }
    }
}
