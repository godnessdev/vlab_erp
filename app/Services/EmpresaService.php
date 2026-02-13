<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Filial;
use App\Models\RegimeTributarioEnum;
use App\Models\StatusEmpresaEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmpresaService
{
    public function listarEmpresas(array $filtros = []): Collection
    {
        $query = Empresa::with(['filiais', 'parametrosOperacionais']);

        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (isset($filtros['regime_tributario'])) {
            $query->where('regime_tributario', $filtros['regime_tributario']);
        }

        if (isset($filtros['busca'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('nome', 'ILIKE', "%{$filtros['busca']}%")
                  ->orWhere('cnpj', 'LIKE', "%{$filtros['busca']}%");
            });
        }

        return $query->orderBy('nome')->get();
    }

    public function buscarEmpresaPorId(string $id): ?Empresa
    {
        return Empresa::with(['filiais', 'parametrosOperacionais', 'configuracoesFiscais'])->find($id);
    }

    public function buscarEmpresaPorCnpj(string $cnpj): ?Empresa
    {
        $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
        return Empresa::where('cnpj', $cnpjLimpo)->first();
    }

    public function criarEmpresa(array $dados): Empresa
    {
        DB::beginTransaction();
        
        try {
            // Validar CNPJ
            $this->validarCnpj($dados['cnpj']);
            
            // Criar empresa
            $empresa = Empresa::create([
                'nome' => $dados['nome'],
                'cnpj' => $dados['cnpj'],
                'ie' => $dados['ie'] ?? null,
                'im' => $dados['im'] ?? null,
                'regime_tributario' => $dados['regime_tributario'] ?? RegimeTributarioEnum::SIMPLES_NACIONAL,
                'data_constituicao' => $dados['data_constituicao'] ?? null,
                'email_contato' => $dados['email_contato'] ?? null,
                'telefone_contato' => $dados['telefone_contato'] ?? null,
                'status' => StatusEmpresaEnum::ATIVO,
            ]);

            // Criar filial matriz obrigatória
            $this->criarFilialMatriz($empresa, $dados);

            // Criar parâmetros operacionais padrão
            $this->criarParametrosPadrao($empresa);

            DB::commit();
            return $empresa->load(['filiais', 'parametrosOperacionais']);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function atualizarEmpresa(string $id, array $dados): Empresa
    {
        $empresa = Empresa::findOrFail($id);
        
        // Validar CNPJ se foi alterado
        if (isset($dados['cnpj']) && $dados['cnpj'] !== $empresa->cnpj) {
            $this->validarCnpj($dados['cnpj'], $id);
        }

        $empresa->update($dados);
        
        return $empresa->load(['filiais', 'parametrosOperacionais']);
    }

    public function ativarEmpresa(string $id): Empresa
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['status' => StatusEmpresaEnum::ATIVO]);
        return $empresa;
    }

    public function inativarEmpresa(string $id): Empresa
    {
        $empresa = Empresa::findOrFail($id);
        
        // Verificar se pode ser inativada
        if ($empresa->usuarios()->where('status', 'ATIVO')->exists()) {
            throw new ValidationException('Não é possível inativar empresa com usuários ativos.');
        }

        $empresa->update(['status' => StatusEmpresaEnum::INATIVO]);
        return $empresa;
    }

    public function excluirEmpresa(string $id): bool
    {
        $empresa = Empresa::findOrFail($id);
        
        if (!$empresa->podeSerExcluida()) {
            throw new ValidationException('Empresa não pode ser excluída pois possui dependências.');
        }

        return $empresa->delete();
    }

    public function obterEstatisticas(): array
    {
        return [
            'total_empresas' => Empresa::count(),
            'empresas_ativas' => Empresa::where('status', StatusEmpresaEnum::ATIVO)->count(),
            'empresas_inativas' => Empresa::where('status', StatusEmpresaEnum::INATIVO)->count(),
            'por_regime' => [
                'simples_nacional' => Empresa::where('regime_tributario', RegimeTributarioEnum::SIMPLES_NACIONAL)->count(),
                'lucro_presumido' => Empresa::where('regime_tributario', RegimeTributarioEnum::LUCRO_PRESUMIDO)->count(),
                'lucro_real' => Empresa::where('regime_tributario', RegimeTributarioEnum::LUCRO_REAL)->count(),
            ],
            'total_filiais' => Filial::count(),
        ];
    }

    private function validarCnpj(string $cnpj, ?string $empresaIdIgnorar = null): void
    {
        $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
        
        // Validação matemática do CNPJ
        if (!$this->validarCnpjMatematico($cnpjLimpo)) {
            throw new ValidationException('CNPJ inválido.');
        }

        // Verificar unicidade
        $query = Empresa::where('cnpj', $cnpjLimpo);
        if ($empresaIdIgnorar) {
            $query->where('id', '!=', $empresaIdIgnorar);
        }

        if ($query->exists()) {
            throw new ValidationException('CNPJ já cadastrado no sistema.');
        }
    }

    private function validarCnpjMatematico(string $cnpj): bool
    {
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $soma = 0;
        $multiplicador = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $dv1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $dv2 = ($resto < 2) ? 0 : 11 - $resto;

        return ($cnpj[12] == $dv1 && $cnpj[13] == $dv2);
    }

    private function criarFilialMatriz(Empresa $empresa, array $dados): Filial
    {
        // TODO: Integração com EnderecoService quando implementado
        // Por enquanto, assumir que endereco_id vem nos dados
        return Filial::create([
            'empresa_id' => $empresa->id,
            'nome' => 'MATRIZ',
            'cnpj_filial' => null, // Matriz usa CNPJ da empresa
            'endereco_id' => $dados['endereco_id'] ?? '00000000-0000-0000-0000-000000000000', // TODO: Remover quando endereço for implementado
            'status' => StatusFilialEnum::ATIVO,
        ]);
    }

    private function criarParametrosPadrao(Empresa $empresa): void
    {
        $parametrosPadrao = [
            [
                'chave' => 'max_usuarios',
                'valor' => 10,
                'descricao' => 'Número máximo de usuários permitidos',
                'tipo_valor' => TipoParametroEnum::INTEGER,
            ],
            [
                'chave' => 'backup_automatico',
                'valor' => true,
                'descricao' => 'Backup automático habilitado',
                'tipo_valor' => TipoParametroEnum::BOOLEAN,
            ],
            [
                'chave' => 'moeda_padrao',
                'valor' => 'BRL',
                'descricao' => 'Moeda padrão da empresa',
                'tipo_valor' => TipoParametroEnum::STRING,
            ],
        ];

        foreach ($parametrosPadrao as $parametro) {
            ParametroOperacional::setParametro(
                $empresa->id,
                $parametro['chave'],
                $parametro['valor'],
                $parametro['descricao'],
                $parametro['tipo_valor']
            );
        }
    }
}
