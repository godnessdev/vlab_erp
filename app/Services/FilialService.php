<?php

namespace App\Services;

use App\Models\Filial;
use App\Models\Empresa;
use App\Models\StatusFilialEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FilialService
{
    public function listarFiliais(string $empresaId, array $filtros = []): Collection
    {
        $query = Filial::with(['empresa', 'endereco'])
            ->where('empresa_id', $empresaId);

        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (isset($filtros['busca'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('nome', 'ILIKE', "%{$filtros['busca']}%")
                  ->orWhere('cnpj_filial', 'LIKE', "%{$filtros['busca']}%");
            });
        }

        return $query->orderBy('nome')->get();
    }

    public function buscarFilialPorId(string $id): ?Filial
    {
        return Filial::with(['empresa', 'endereco', 'configuracoesFiscais'])->find($id);
    }

    public function criarFilial(string $empresaId, array $dados): Filial
    {
        DB::beginTransaction();
        
        try {
            // Verificar se empresa existe e está ativa
            $empresa = Empresa::findOrFail($empresaId);
            if (!$empresa->isAtiva()) {
                throw new ValidationException('Não é possível criar filial para empresa inativa.');
            }

            // Validar CNPJ da filial se informado
            if (isset($dados['cnpj_filial'])) {
                $this->validarCnpjFilial($dados['cnpj_filial']);
            }

            // Validar nome único por empresa
            if (Filial::where('empresa_id', $empresaId)
                ->where('nome', $dados['nome'])->exists()) {
                throw new ValidationException('Nome de filial já existe para esta empresa.');
            }

            $filial = Filial::create([
                'empresa_id' => $empresaId,
                'nome' => $dados['nome'],
                'cnpj_filial' => $dados['cnpj_filial'] ?? null,
                'endereco_id' => $dados['endereco_id'],
                'status' => StatusFilialEnum::ATIVO,
            ]);

            DB::commit();
            return $filial->load(['empresa', 'endereco']);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function atualizarFilial(string $id, array $dados): Filial
    {
        $filial = Filial::findOrFail($id);
        
        // Validar CNPJ se foi alterado
        if (isset($dados['cnpj_filial']) && $dados['cnpj_filial'] !== $filial->cnpj_filial) {
            $this->validarCnpjFilial($dados['cnpj_filial'], $id);
        }

        // Validar nome único por empresa se foi alterado
        if (isset($dados['nome']) && $dados['nome'] !== $filial->nome) {
            if (Filial::where('empresa_id', $filial->empresa_id)
                ->where('nome', $dados['nome'])
                ->where('id', '!=', $id)->exists()) {
                throw new ValidationException('Nome de filial já existe para esta empresa.');
            }
        }

        $filial->update($dados);
        
        return $filial->load(['empresa', 'endereco']);
    }

    public function ativarFilial(string $id): Filial
    {
        $filial = Filial::findOrFail($id);
        $filial->update(['status' => StatusFilialEnum::ATIVO]);
        return $filial;
    }

    public function inativarFilial(string $id): Filial
    {
        $filial = Filial::findOrFail($id);
        
        if ($filial->isMatriz()) {
            throw new ValidationException('Não é possível inativar a filial matriz.');
        }
        
        $filial->update(['status' => StatusFilialEnum::INATIVO]);
        return $filial;
    }

    public function excluirFilial(string $id): bool
    {
        $filial = Filial::findOrFail($id);
        
        if (!$filial->podeSerExcluida()) {
            throw new ValidationException('Filial não pode ser excluída.');
        }

        return $filial->delete();
    }

    public function obterFilialMatriz(string $empresaId): ?Filial
    {
        return Filial::where('empresa_id', $empresaId)
            ->where('nome', 'MATRIZ')
            ->first();
    }

    public function obterEstatisticas(string $empresaId): array
    {
        return [
            'total_filiais' => Filial::where('empresa_id', $empresaId)->count(),
            'filiais_ativas' => Filial::where('empresa_id', $empresaId)
                ->where('status', StatusFilialEnum::ATIVO)->count(),
            'filiais_inativas' => Filial::where('empresa_id', $empresaId)
                ->where('status', StatusFilialEnum::INATIVO)->count(),
            'tem_matriz' => Filial::where('empresa_id', $empresaId)
                ->where('nome', 'MATRIZ')->exists(),
        ];
    }

    private function validarCnpjFilial(string $cnpj, ?string $filialIdIgnorar = null): void
    {
        $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
        
        // Validação matemática do CNPJ (reutilizar do EmpresaService)
        $empresaService = new EmpresaService();
        $reflection = new \ReflectionClass($empresaService);
        $method = $reflection->getMethod('validarCnpjMatematico');
        $method->setAccessible(true);
        
        if (!$method->invoke($empresaService, $cnpjLimpo)) {
            throw new ValidationException('CNPJ da filial inválido.');
        }

        // Verificar unicidade
        $query = Filial::where('cnpj_filial', $cnpjLimpo);
        if ($filialIdIgnorar) {
            $query->where('id', '!=', $filialIdIgnorar);
        }

        if ($query->exists()) {
            throw new ValidationException('CNPJ da filial já cadastrado no sistema.');
        }
    }
}
