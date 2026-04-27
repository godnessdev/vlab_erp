<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoas';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'tipo',
        'nome_razao_social',
        'nome_fantasia',
        'data_nascimento_constituicao',
        'status',
        'data_criacao',
        'data_atualizacao',
    ];
}
