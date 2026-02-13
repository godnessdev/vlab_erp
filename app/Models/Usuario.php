<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class Usuario extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'usuarios';

    protected $fillable = [
        'pessoa_id',
        'email',
        'password',
        'ultimo_login',
        'tentativas_login_falhadas',
        'status',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'timestamp',
        'ultimo_login' => 'timestamp',
        'password' => 'hashed',
        'status' => StatusUsuarioEnum::class,
    ];

    /**
     * Relacionamentos
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'usuario_empresa_papel')
            ->withPivot(['papel_id', 'data_inicio', 'data_fim', 'status'])
            ->withTimestamps();
    }

    public function papeis(): BelongsToMany
    {
        return $this->belongsToMany(Papel::class, 'usuario_empresa_papel')
            ->withPivot(['empresa_id', 'data_inicio', 'data_fim', 'status'])
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', StatusUsuarioEnum::ATIVO);
    }

    public function scopePorEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->whereHas('empresas', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        });
    }

    /**
     * Métodos de negócio
     */
    public function isAtivo(): bool
    {
        return $this->status === StatusUsuarioEnum::ATIVO;
    }

    public function isBloqueado(): bool
    {
        return $this->status === StatusUsuarioEnum::BLOQUEADO;
    }

    public function isPendente(): bool
    {
        return $this->status === StatusUsuarioEnum::PENDENTE;
    }

    public function podeLogar(): bool
    {
        return $this->isAtivo() && $this->email_verified_at !== null;
    }

    public function incrementarTentativasFalhadas(): void
    {
        $this->increment('tentativas_login_falhadas');
        
        // Bloquear após 5 tentativas falhadas
        if ($this->tentativas_login_falhadas >= 5) {
            $this->status = StatusUsuarioEnum::BLOQUEADO;
            $this->save();
        }
    }

    public function resetarTentativasFalhadas(): void
    {
        $this->tentativas_login_falhadas = 0;
        $this->ultimo_login = now();
        $this->save();
    }

    public function temPermissao(string $permissao, string $empresaId): bool
    {
        // TODO: Implementar verificação de permissão via RBAC
        // Verifica se o usuário tem a permissão específica na empresa
        return true; // Placeholder
    }

    public function temPapel(string $papel, string $empresaId): bool
    {
        return $this->empresas()
            ->where('empresa_id', $empresaId)
            ->whereHas('papeis', function ($q) use ($papel) {
                $q->where('tipo_papel', $papel);
            })
            ->exists();
    }

    public function getNomeCompleto(): string
    {
        return $this->pessoa->nome ?? $this->email;
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($usuario) {
            // Validar email único
            if (static::where('email', $usuario->email)->exists()) {
                throw new \InvalidArgumentException('Email já cadastrado no sistema.');
            }

            // Validar pessoa única
            if (static::where('pessoa_id', $usuario->pessoa_id)->exists()) {
                throw new \InvalidArgumentException('Esta pessoa já possui um usuário cadastrado.');
            }
        });

        static::updating(function ($usuario) {
            // Validar email único (exceto o próprio registro)
            if (static::where('email', $usuario->email)
                ->where('id', '!=', $usuario->id)->exists()) {
                throw new \InvalidArgumentException('Email já cadastrado no sistema.');
            }

            // Validar pessoa única (exceto o próprio registro)
            if (static::where('pessoa_id', $usuario->pessoa_id)
                ->where('id', '!=', $usuario->id)->exists()) {
                throw new \InvalidArgumentException('Esta pessoa já possui um usuário cadastrado.');
            }
        });
    }
}
