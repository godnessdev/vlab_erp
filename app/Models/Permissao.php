<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permissao extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'permissoes';

    protected $fillable = [
        'nome',
        'descricao',
        'modulo',
        'acao',
        'recurso',
    ];

    /**
     * Relacionamentos
     */
    public function papeis(): BelongsToMany
    {
        return $this->belongsToMany(Papel::class, 'papel_permissao')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    public function scopePorRecurso($query, string $recurso)
    {
        return $query->where('recurso', $recurso);
    }

    public function scopePorNome($query, string $nome)
    {
        return $query->where('nome', $nome);
    }

    /**
     * Métodos de negócio
     */
    public function getIdentificacao(): string
    {
        return "{$this->modulo}.{$this->acao}.{$this->recurso}";
    }

    public function getGrupoModulo(): string
    {
        return ucfirst($this->modulo);
    }

    /**
     * Métodos estáticos para criação de permissões
     */
    public static function criarPermissao(
        string $nome,
        string $descricao,
        string $modulo,
        string $acao,
        string $recurso
    ): self {
        return static::create([
            'nome' => $nome,
            'descricao' => $descricao,
            'modulo' => strtolower($modulo),
            'acao' => strtolower($acao),
            'recurso' => strtolower($recurso),
        ]);
    }

    public static function buscarPorIdentificacao(string $identificacao): ?self
    {
        [$modulo, $acao, $recurso] = explode('.', $identificacao);

        return static::where('modulo', $modulo)
            ->where('acao', $acao)
            ->where('recurso', $recurso)
            ->first();
    }

    /**
     * Métodos para criação em lote de permissões CRUD
     */
    public static function criarPermissoesCrud(string $modulo, string $recurso, ?array $acoes = null): array
    {
        $acoes = $acoes ?? ['criar', 'visualizar', 'editar', 'excluir'];
        $permissoes = [];

        foreach ($acoes as $acao) {
            $nome = "{$modulo}.{$acao}";
            $descricao = ucfirst($acao).' '.ucfirst($recurso);

            $permissoes[] = static::criarPermissao($nome, $descricao, $modulo, $acao, $recurso);
        }

        return $permissoes;
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($permissao) {
            // Validar nome único
            if (static::where('nome', $permissao->nome)->exists()) {
                throw new \InvalidArgumentException('Nome de permissão já existe.');
            }

            // Validar combinação única módulo+ação+recurso
            if (static::where('modulo', $permissao->modulo)
                ->where('acao', $permissao->acao)
                ->where('recurso', $permissao->recurso)->exists()) {
                throw new \InvalidArgumentException('Permissão já existe para este módulo/ação/recurso.');
            }
        });

        static::updating(function ($permissao) {
            // Validar nome único (exceto o próprio registro)
            if (static::where('nome', $permissao->nome)
                ->where('id', '!=', $permissao->id)->exists()) {
                throw new \InvalidArgumentException('Nome de permissão já existe.');
            }

            // Validar combinação única módulo+ação+recurso (exceto o próprio registro)
            if (static::where('modulo', $permissao->modulo)
                ->where('acao', $permissao->acao)
                ->where('recurso', $permissao->recurso)
                ->where('id', '!=', $permissao->id)->exists()) {
                throw new \InvalidArgumentException('Permissão já existe para este módulo/ação/recurso.');
            }
        });
    }
}
