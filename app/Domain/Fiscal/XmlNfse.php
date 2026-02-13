<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class XmlNfse extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'xml_nfse';

    protected $fillable = [
        'nfse_id',
        'versao_schema',
        'xml_assinado',
        'xml_original',
        'hash_sha256',
        'tamanho_bytes',
        'comprimido',
        'data_armazenamento',
    ];

    protected $casts = [
        'comprimido' => 'boolean',
        'tamanho_bytes' => 'integer',
        'data_armazenamento' => 'datetime',
    ];
}
