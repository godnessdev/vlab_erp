<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificadoDigital extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'certificado_digital';

    protected $fillable = [
        'empresa_id',
        'alias',
        'arquivo_pfx',
        'senha',
        'subject',
        'issuer',
        'serial_number',
        'data_validade_inicio',
        'data_validade_fim',
        'ativo',
        'data_criacao',
    ];

    protected $casts = [
        'data_validade_inicio' => 'datetime',
        'data_validade_fim' => 'datetime',
        'ativo' => 'boolean',
        'data_criacao' => 'datetime',
    ];
}
