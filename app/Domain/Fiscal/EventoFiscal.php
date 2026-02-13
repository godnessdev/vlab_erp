<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Fiscal\Enums\TipoEventoFiscalEnum;

class EventoFiscal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'evento_fiscal';

    protected $fillable = [
        'nfse_id',
        'tipo_evento',
        'data_evento',
        'motivo',
        'usuario_id',
        'nfse_substituta_id',
        'protocolo_autorizacao',
        'xml_evento',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
        'tipo_evento' => TipoEventoFiscalEnum::class,
    ];
}
