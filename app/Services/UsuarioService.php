<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Pessoa;
use App\Models\Papel;
use App\Models\StatusUsuarioEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UsuarioService
{
    public function listarUsuarios(array $filtros = []): Collection
    {
        $query = Usuario::with(['pessoa', 'empresas', 'papeis']);

        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (isset($filtros['empresa_id'])) {
            $query->whereHas('empresas', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        if (isset($filtros['busca'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('email', 'ILIKE', "%{$filtros['busca']}%")
                  ->orWhereHas('pessoa', function ($q2) use ($filtros) {
                      $q2->where('nome', 'ILIKE', "%{$filtros['busca']}%");
                  });
            });
        }

        return $query->orderBy('email')->get();
    }

    public function buscarUsuarioPorId(string $id): ?Usuario
    {
        return Usuario::with(['pessoa', 'empresas.papeis'])->find($id);
    }

    public function buscarUsuarioPorEmail(string $email): ?Usuario
    {
        return Usuario::with(['pessoa', 'empresas.papeis'])->where('email', $email)->first();
    }

    public function criarUsuario(array $dados): Usuario
    {
        DB::beginTransaction();
        
        try {
            // Verificar se pessoa existe
            $pessoa = Pessoa::findOrFail($dados['pessoa_id']);
            
            // Verificar se pessoa já tem usuário
            if (Usuario::where('pessoa_id', $dados['pessoa_id'])->exists()) {
                throw new ValidationException('Esta pessoa já possui um usuário cadastrado.');
            }

            // Verificar email único
            if (Usuario::where('email', $dados['email'])->exists()) {
                throw new ValidationException('Email já cadastrado no sistema.');
            }

            $usuario = Usuario::create([
                'pessoa_id' => $dados['pessoa_id'],
                'email' => $dados['email'],
                'password' => Hash::make($dados['password']),
                'status' => $dados['status'] ?? StatusUsuarioEnum::PENDENTE,
            ]);

            DB::commit();
            return $usuario->load(['pessoa']);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function atualizarUsuario(string $id, array $dados): Usuario
    {
        $usuario = Usuario::findOrFail($id);
        
        // Validar email único se foi alterado
        if (isset($dados['email']) && $dados['email'] !== $usuario->email) {
            if (Usuario::where('email', $dados['email'])->exists()) {
                throw new ValidationException('Email já cadastrado no sistema.');
            }
        }

        // Hash da senha se foi fornecida
        if (isset($dados['password'])) {
            $dados['password'] = Hash::make($dados['password']);
        }

        $usuario->update($dados);
        
        return $usuario->load(['pessoa']);
    }

    public function vincularUsuarioEmpresa(string $usuarioId, string $empresaId, string $papelId, array $opcoes = []): void
    {
        DB::beginTransaction();
        
        try {
            $usuario = Usuario::findOrFail($usuarioId);
            $empresa = Empresa::findOrFail($empresaId);
            $papel = Papel::findOrFail($papelId);

            // Verificar se vínculo já existe
            $vinculoExistente = DB::table('usuario_empresa_papel')
                ->where('usuario_id', $usuarioId)
                ->where('empresa_id', $empresaId)
                ->where('papel_id', $papelId)
                ->exists();

            if ($vinculoExistente) {
                throw new ValidationException('Usuário já possui este papel nesta empresa.');
            }

            // Criar vínculo
            DB::table('usuario_empresa_papel')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'usuario_id' => $usuarioId,
                'empresa_id' => $empresaId,
                'papel_id' => $papelId,
                'data_inicio' => $opcoes['data_inicio'] ?? Carbon::now(),
                'data_fim' => $opcoes['data_fim'] ?? null,
                'status' => 'ATIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function desvincularUsuarioEmpresa(string $usuarioId, string $empresaId, string $papelId): void
    {
        $affected = DB::table('usuario_empresa_papel')
            ->where('usuario_id', $usuarioId)
            ->where('empresa_id', $empresaId)
            ->where('papel_id', $papelId)
            ->delete();

        if ($affected === 0) {
            throw new ValidationException('Vínculo não encontrado.');
        }
    }

    public function ativarUsuario(string $id): Usuario
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update([
            'status' => StatusUsuarioEnum::ATIVO,
            'email_verified_at' => now(),
        ]);
        return $usuario;
    }

    public function bloquearUsuario(string $id): Usuario
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['status' => StatusUsuarioEnum::BLOQUEADO]);
        return $usuario;
    }

    public function desbloquearUsuario(string $id): Usuario
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update([
            'status' => StatusUsuarioEnum::ATIVO,
            'tentativas_login_falhadas' => 0,
        ]);
        return $usuario;
    }

    public function alterarSenha(string $id, string $senhaAtual, string $novaSenha): Usuario
    {
        $usuario = Usuario::findOrFail($id);
        
        if (!Hash::check($senhaAtual, $usuario->password)) {
            throw new ValidationException('Senha atual incorreta.');
        }

        $usuario->update(['password' => Hash::make($novaSenha)]);
        
        return $usuario;
    }

    public function resetarSenha(string $id): string
    {
        $usuario = Usuario::findOrFail($id);
        $novaSenha = $this->gerarSenhaTemporaria();
        
        $usuario->update([
            'password' => Hash::make($novaSenha),
            'status' => StatusUsuarioEnum::PENDENTE, // Forçar troca de senha no próximo login
        ]);
        
        return $novaSenha;
    }

    public function obterPermissoesUsuario(string $usuarioId, string $empresaId): array
    {
        $usuario = Usuario::with(['papeis.permissoes'])->find($usuarioId);
        
        if (!$usuario) {
            return [];
        }

        $permissoes = [];
        
        // Buscar permissões através dos papéis na empresa específica
        $vinculos = DB::table('usuario_empresa_papel')
            ->where('usuario_id', $usuarioId)
            ->where('empresa_id', $empresaId)
            ->where('status', 'ATIVO')
            ->where(function ($query) {
                $query->whereNull('data_fim')
                      ->orWhere('data_fim', '>=', now());
            })
            ->pluck('papel_id');

        foreach ($vinculos as $papelId) {
            $papel = Papel::with(['permissoes'])->find($papelId);
            if ($papel) {
                foreach ($papel->permissoes as $permissao) {
                    $permissoes[$permissao->nome] = [
                        'modulo' => $permissao->modulo,
                        'acao' => $permissao->acao,
                        'recurso' => $permissao->recurso,
                        'descricao' => $permissao->descricao,
                    ];
                }
            }
        }

        return array_values($permissoes);
    }

    public function verificarPermissao(string $usuarioId, string $empresaId, string $permissao): bool
    {
        $permissoes = $this->obterPermissoesUsuario($usuarioId, $empresaId);
        
        return collect($permissoes)->contains(function ($p) use ($permissao) {
            return $p['modulo'] . '.' . $p['acao'] . '.' . $p['recurso'] === $permissao;
        });
    }

    public function obterEstatisticas(): array
    {
        return [
            'total_usuarios' => Usuario::count(),
            'usuarios_ativos' => Usuario::where('status', StatusUsuarioEnum::ATIVO)->count(),
            'usuarios_inativos' => Usuario::where('status', StatusUsuarioEnum::INATIVO)->count(),
            'usuarios_bloqueados' => Usuario::where('status', StatusUsuarioEnum::BLOQUEADO)->count(),
            'usuarios_pendentes' => Usuario::where('status', StatusUsuarioEnum::PENDENTE)->count(),
            'usuarios_verificados' => Usuario::whereNotNull('email_verified_at')->count(),
        ];
    }

    private function gerarSenhaTemporaria(): string
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $senha = '';
        
        for ($i = 0; $i < 12; $i++) {
            $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        
        return $senha;
    }
}
