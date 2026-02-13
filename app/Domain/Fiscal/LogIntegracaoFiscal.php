<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogIntegracaoFiscal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'log_integracao_fiscal';

    protected $fillable = [
        'empresa_id',
        'nfse_id',
        'lote_id',
        'data_log',
        'endpoint_url',
        'metodo_http',
        'headers_request',
        'request_body',
        'status_http',
        'headers_response',
        'response_body',
        'tempo_resposta_ms',
        'erro_interno',
    ];

    protected $casts = [
        'data_log' => 'datetime',
        'headers_request' => 'array',
        'headers_response' => 'array',
        'status_http' => 'integer',
        'tempo_resposta_ms' => 'integer',
    ];
}
