<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Papel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'papeis_sistema';

    protected $fillable = [
        'nome',
        'descricao',
        'nivel',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'nivel' => 'integer',
    ];

    /**
     * Relacionamento: Um papel pode ter muitas permissões
     */
    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'papel_permissoes');
    }

    /**
     * Relacionamento: Um papel pode estar associado a muitos usuários através da tabela pivot usuario_empresa_papel
     */
    public function usuarioEmpresaPapeis(): BelongsToMany
    {
        return $this->belongsToMany(UsuarioEmpresaPapel::class);
    }

    /**
     * Scope para papéis ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para papéis por nível
     */
    public function scopePorNivel($query, int $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    /**
     * Verificar se papel tem uma permissão específica
     */
    public function temPermissao(string $nomePermissao): bool
    {
        return $this->permissoes()->where('nome', $nomePermissao)->exists();
    }

    /**
     * Obter todas as permissões do papel como array
     */
    public function obterPermissoes(): array
    {
        return $this->permissoes()->pluck('nome')->toArray();
    }
}
