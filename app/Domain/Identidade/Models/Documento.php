<?php

namespace App\Domain\Identidade\Models;

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'documentos';

    protected $fillable = [
        'pessoa_id',
        'tipo',
        'valor',
        'data_emissao',
        'orgao_emissor',
        'valido',
    ];

    protected $casts = [
        'tipo' => TipoDocumento::class,
        'data_emissao' => 'date',
        'valido' => 'boolean',
        'data_criacao' => 'datetime',
    ];

    public const CREATED_AT = 'data_criacao';
    public const UPDATED_AT = null;

    /**
     * Relacionamentos
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Scopes
     */
    public function scopeValidos($query)
    {
        return $query->where('valido', true);
    }

    public function scopeInvalidos($query)
    {
        return $query->where('valido', false);
    }

    public function scopePrincipais($query)
    {
        return $query->whereIn('tipo', [TipoDocumento::CPF, TipoDocumento::CNPJ]);
    }

    public function scopePorTipo($query, TipoDocumento $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorValor($query, string $valor)
    {
        return $query->where('valor', $this->limparDocumento($valor));
    }

    /**
     * Accessors & Mutators
     */
    public function getValorFormatadoAttribute(): string
    {
        return $this->formatarDocumento($this->valor);
    }

    public function getValorMascaradoAttribute(): string
    {
        $valor = $this->valor;
        
        return match ($this->tipo) {
            TipoDocumento::CPF => substr($valor, 0, 3) . '.***.***-' . substr($valor, -2),
            TipoDocumento::CNPJ => substr($valor, 0, 2) . '.***.***/****-' . substr($valor, -2),
            TipoDocumento::RG => substr($valor, 0, 2) . '.***.***-' . substr($valor, -1),
            default => '***',
        };
    }

    public function setValorAttribute($value): void
    {
        $this->attributes['valor'] = $this->limparDocumento($value);
    }

    /**
     * Business Methods
     */
    public function isPrincipal(): bool
    {
        return $this->tipo->isPrincipal();
    }

    public function isValido(): bool
    {
        return $this->valido;
    }

    public function isObrigatorio(): bool
    {
        return $this->tipo->isObrigatorio();
    }

    public function validar(): bool
    {
        $validator = new DocumentoValidator();
        $resultado = $validator->validar($this->tipo, $this->valor);
        
        $this->update(['valido' => $resultado]);
        
        return $resultado;
    }

    public function consultarReceita(): ?array
    {
        // Implementar consulta na Receita Federal
        // Integração com APIs públicas ou serviços terceirizados
        return null;
    }

    public function marcarComoInvalido(string $motivo = null): void
    {
        $this->update([
            'valido' => false,
            // Pode adicionar campo 'motivo_invalidacao' se necessário
        ]);
    }

    public function marcarComoValido(): void
    {
        $this->update(['valido' => true]);
    }

    public function isVencido(): bool
    {
        if (!$this->data_emissao) {
            return false;
        }

        // Apenas RG tem validade (10 anos no Brasil)
        if ($this->tipo === TipoDocumento::RG) {
            return $this->data_emissao->addYears(10)->isPast();
        }

        return false;
    }

    public function getDataVencimento(): ?Carbon
    {
        if ($this->tipo === TipoDocumento::RG && $this->data_emissao) {
            return $this->data_emissao->addYears(10);
        }

        return null;
    }

    /**
     * Private Methods
     */
    private function formatarDocumento(string $documento): string
    {
        $documento = $this->limparDocumento($documento);
        
        return match ($this->tipo) {
            TipoDocumento::CPF => $this->formatarCpf($documento),
            TipoDocumento::CNPJ => $this->formatarCnpj($documento),
            TipoDocumento::RG => $this->formatarRg($documento),
            default => $documento,
        };
    }

    private function limparDocumento(string $documento): string
    {
        return preg_replace('/[^0-9]/', '', $documento);
    }

    private function formatarCpf(string $cpf): string
    {
        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return sprintf('%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    private function formatarCnpj(string $cnpj): string
    {
        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }

        return sprintf('%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    private function formatarRg(string $rg): string
    {
        if (strlen($rg) < 7) {
            return $rg;
        }

        $digitoVerificador = substr($rg, -1);
        $numero = substr($rg, 0, -1);

        // Formatar com pontos a cada 3 dígitos da direita para esquerda
        $numeroFormatado = strrev(chunk_split(strrev($numero), 3, '.'));
        $numeroFormatado = rtrim($numeroFormatado, '.');

        return $numeroFormatado . '-' . $digitoVerificador;
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return \App\Domain\Identidade\Factories\DocumentoFactory::new();
    }
}
