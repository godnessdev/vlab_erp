<?php

namespace App\Domain\Identidade\Models;

use App\Domain\Identidade\Enums\StatusPessoa;
use App\Domain\Identidade\Enums\TipoPessoa;
use App\Domain\Identidade\Models\Papel;
use App\Domain\Identidade\Observers\PessoaObserver;
use App\Models\UsuarioEmpresaPapel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[\Illuminate\Database\Eloquent\Attributes\ObservedBy([PessoaObserver::class])]
class Pessoa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pessoas';

    protected $fillable = [
        'tipo',
        'nome_razao_social',
        'nome_fantasia',
        'data_nascimento_constituicao',
        'status',
    ];

    protected $casts = [
        'tipo' => TipoPessoa::class,
        'status' => StatusPessoa::class,
        'data_nascimento_constituicao' => 'date',
        'data_criacao' => 'datetime',
        'data_atualizacao' => 'datetime',
    ];

    protected $dates = [
        'data_nascimento_constituicao',
        'data_criacao',
        'data_atualizacao',
    ];

    public const CREATED_AT = 'data_criacao';
    public const UPDATED_AT = 'data_atualizacao';

    /**
     * Relacionamentos
     */
    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }

    public function contatos(): HasMany
    {
        return $this->hasMany(Contato::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function papeis(): HasMany
    {
        return $this->hasMany(Papel::class, 'pessoa_id');
    }

    /**
     * Relacionamentos específicos
     */
    public function enderecoPrincipal(): HasMany
    {
        return $this->enderecos()->where('tipo', 'PRINCIPAL');
    }

    public function contatosPrincipais(): HasMany
    {
        return $this->contatos()->where('principal', true);
    }

    public function documentoPrincipal(): HasMany
    {
        return $this->documentos()->where('tipo', $this->tipo->documentoPrincipal());
    }

    public function papeisAtivos(): HasMany
    {
        return $this->papeis()->where('status', 'ATIVO')->whereNull('data_fim');
    }

    /**
     * Scopes
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', StatusPessoa::ATIVO);
    }

    public function scopeFisicas($query)
    {
        return $query->where('tipo', TipoPessoa::FISICA);
    }

    public function scopeJuridicas($query)
    {
        return $query->where('tipo', TipoPessoa::JURIDICA);
    }

    public function scopeComDocumento($query, string $documento)
    {
        return $query->whereHas('documentos', function ($q) use ($documento) {
            $q->where('valor', $documento);
        });
    }

    /**
     * Accessors & Mutators
     */
    public function getNomeCompleto(): string
    {
        if ($this->tipo === TipoPessoa::JURIDICA && $this->nome_fantasia) {
            return "{$this->nome_razao_social} ({$this->nome_fantasia})";
        }

        return $this->nome_razao_social;
    }

    public function getIdadeAttribute(): ?int
    {
        if (!$this->data_nascimento_constituicao || $this->tipo !== TipoPessoa::FISICA) {
            return null;
        }

        return $this->data_nascimento_constituicao->age;
    }

    public function getTempoConstituicaoAttribute(): ?int
    {
        if (!$this->data_nascimento_constituicao || $this->tipo !== TipoPessoa::JURIDICA) {
            return null;
        }

        return $this->data_nascimento_constituicao->diffInYears(now());
    }

    /**
     * Business Methods
     */
    public function isAtiva(): bool
    {
        return $this->status === StatusPessoa::ATIVO;
    }

    public function isInativa(): bool
    {
        return $this->status === StatusPessoa::INATIVO;
    }

    public function isFisica(): bool
    {
        return $this->tipo === TipoPessoa::FISICA;
    }

    public function isJuridica(): bool
    {
        return $this->tipo === TipoPessoa::JURIDICA;
    }

    public function ativar(): void
    {
        $this->update(['status' => StatusPessoa::ATIVO]);
    }

    public function inativar(): void
    {
        $this->update(['status' => StatusPessoa::INATIVO]);
    }

    public function getCpfCnpj(): ?string
    {
        $documento = $this->documentos()
            ->where('tipo', $this->tipo->documentoPrincipal()->value)
            ->first();

        return $documento?->valor;
    }

    public function getEmailPrincipal(): ?string
    {
        $contato = $this->contatos()
            ->where('tipo', 'EMAIL')
            ->where('principal', true)
            ->first();

        return $contato?->valor;
    }

    public function getTelefonePrincipal(): ?string
    {
        $contato = $this->contatos()
            ->whereIn('tipo', ['CELULAR', 'TELEFONE_FIXO'])
            ->where('principal', true)
            ->first();

        return $contato?->valor;
    }

    public function possuiPapel(string $tipoPapel, string $empresaId): bool
    {
        return $this->papeis()
            ->where('tipo_papel', $tipoPapel)
            ->where('empresa_id', $empresaId)
            ->where('status', 'ATIVO')
            ->whereNull('data_fim')
            ->exists();
    }

    public function adicionarPapel(string $tipoPapel, string $empresaId, array $dadosEspecificos = []): Papel
    {
        $papel = $this->papeis()->create([
            'empresa_id' => $empresaId,
            'tipo_papel' => $tipoPapel,
            'data_inicio' => now()->toDateString(),
            'status' => 'ATIVO',
        ]);

        if (!empty($dadosEspecificos)) {
            foreach ($dadosEspecificos as $chave => $valor) {
                $papel->dadosEspecificos()->create([
                    'chave' => $chave,
                    'valor' => $valor,
                ]);
            }
        }

        return $papel;
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return \App\Domain\Identidade\Factories\PessoaFactory::new();
    }
}
