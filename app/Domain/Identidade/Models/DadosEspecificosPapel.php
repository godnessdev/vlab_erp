<?php

namespace App\Domain\Identidade\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioEmpresaPapel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model para dados específicos de papéis
 * 
 * Permite extensibilidade EAV (Entity-Attribute-Value) para papéis específicos
 * Exemplo: Clientes podem ter limite_credito, categoria, etc.
 * 
 * @property string $id
 * @property string $papel_id
 * @property string $chave
 * @property mixed $valor
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property-read UsuarioEmpresaPapel $papel
 */
class DadosEspecificosPapel extends Model
{
    use HasUuids;

    protected $table = 'dados_especificos_papel';

    protected $fillable = [
        'papel_id',
        'chave',
        'valor',
    ];

    protected $casts = [
        'valor' => 'json',
    ];

    /**
     * Relacionamento com papel
     */
    public function papel(): BelongsTo
    {
        return $this->belongsTo(UsuarioEmpresaPapel::class, 'papel_id');
    }

    /**
     * Accessor para valor limpo
     */
    public function getValorLimpoAttribute()
    {
        return $this->valor;
    }
}