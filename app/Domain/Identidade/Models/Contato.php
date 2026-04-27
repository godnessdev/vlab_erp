<?php

namespace App\Domain\Identidade\Models;

use App\Domain\Identidade\Enums\TipoContato;
use App\Domain\Identidade\Factories\ContatoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contato extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contatos';

    protected $fillable = [
        'pessoa_id',
        'tipo',
        'valor',
        'principal',
        'verificado',
    ];

    protected $casts = [
        'tipo' => TipoContato::class,
        'principal' => 'boolean',
        'verificado' => 'boolean',
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
    public function scopePrincipais($query)
    {
        return $query->where('principal', true);
    }

    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function scopeEmails($query)
    {
        return $query->where('tipo', TipoContato::EMAIL);
    }

    public function scopeTelefones($query)
    {
        return $query->whereIn('tipo', [
            TipoContato::TELEFONE_FIXO,
            TipoContato::CELULAR,
            TipoContato::WHATSAPP,
        ]);
    }

    public function scopeCelulares($query)
    {
        return $query->whereIn('tipo', [TipoContato::CELULAR, TipoContato::WHATSAPP]);
    }

    public function scopePorTipo($query, TipoContato $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Accessors & Mutators
     */
    public function getValorFormatadoAttribute(): string
    {
        return match ($this->tipo) {
            TipoContato::EMAIL => $this->valor,
            TipoContato::TELEFONE_FIXO,
            TipoContato::CELULAR,
            TipoContato::WHATSAPP => $this->formatarTelefone($this->valor),
            default => $this->valor,
        };
    }

    public function setValorAttribute($value): void
    {
        if ($this->tipo?->isTelefone()) {
            $this->attributes['valor'] = $this->limparTelefone($value);
        } elseif ($this->tipo === TipoContato::EMAIL) {
            $this->attributes['valor'] = strtolower(trim($value));
        } else {
            $this->attributes['valor'] = $value;
        }
    }

    /**
     * Business Methods
     */
    public function isPrincipal(): bool
    {
        return $this->principal;
    }

    public function isVerificado(): bool
    {
        return $this->verificado;
    }

    public function isEmail(): bool
    {
        return $this->tipo === TipoContato::EMAIL;
    }

    public function isTelefone(): bool
    {
        return $this->tipo->isTelefone();
    }

    public function isCelular(): bool
    {
        return in_array($this->tipo, [TipoContato::CELULAR, TipoContato::WHATSAPP]);
    }

    public function definirComoPrincipal(): void
    {
        // Remove principal dos outros contatos do mesmo tipo
        $this->pessoa->contatos()
            ->where('tipo', $this->tipo)
            ->where('id', '!=', $this->id)
            ->update(['principal' => false]);

        $this->update(['principal' => true]);
    }

    public function marcarComoVerificado(): void
    {
        $this->update(['verificado' => true]);
    }

    public function enviarVerificacao(): bool
    {
        if ($this->verificado) {
            return true;
        }

        return match ($this->tipo) {
            TipoContato::EMAIL => $this->enviarVerificacaoEmail(),
            TipoContato::CELULAR, TipoContato::WHATSAPP => $this->enviarVerificacaoSms(),
            default => false,
        };
    }

    public function validar(): bool
    {
        return match ($this->tipo) {
            TipoContato::EMAIL => $this->validarEmail(),
            TipoContato::TELEFONE_FIXO,
            TipoContato::CELULAR,
            TipoContato::WHATSAPP => $this->validarTelefone(),
            default => true,
        };
    }

    /**
     * Private Methods
     */
    private function enviarVerificacaoEmail(): bool
    {
        // Implementar envio de email de verificação
        // Pode usar Job para processamento assíncrono
        return true;
    }

    private function enviarVerificacaoSms(): bool
    {
        // Implementar envio de SMS de verificação
        // Integração com Twilio, AWS SNS, etc.
        return true;
    }

    private function validarEmail(): bool
    {
        return filter_var($this->valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validarTelefone(): bool
    {
        $telefone = $this->limparTelefone($this->valor);

        // Validar formato brasileiro
        if ($this->tipo === TipoContato::TELEFONE_FIXO) {
            // (11) 1234-5678 = 10 dígitos
            return preg_match('/^[1-9]{2}[2-9][0-9]{7}$/', $telefone);
        } else {
            // (11) 91234-5678 = 11 dígitos
            return preg_match('/^[1-9]{2}9[0-9]{8}$/', $telefone);
        }
    }

    private function formatarTelefone(string $telefone): string
    {
        $telefone = $this->limparTelefone($telefone);

        if (strlen($telefone) === 10) {
            // Telefone fixo: (11) 1234-5678
            return sprintf('(%s) %s-%s',
                substr($telefone, 0, 2),
                substr($telefone, 2, 4),
                substr($telefone, 6, 4)
            );
        } elseif (strlen($telefone) === 11) {
            // Celular: (11) 91234-5678
            return sprintf('(%s) %s-%s',
                substr($telefone, 0, 2),
                substr($telefone, 2, 5),
                substr($telefone, 7, 4)
            );
        }

        return $telefone;
    }

    private function limparTelefone(string $telefone): string
    {
        return preg_replace('/[^0-9]/', '', $telefone);
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return ContatoFactory::new();
    }
}
