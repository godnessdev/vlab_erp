<?php

use App\Domain\Faturamento\Enums\TipoParcelamento;

test('pode obter todos os tipos de parcelamento disponíveis', function () {
    $tipos = TipoParcelamento::cases();
    
    expect($tipos)->toHaveCount(4);
    
    $expectedTipos = [
        TipoParcelamento::A_VISTA,
        TipoParcelamento::FIXO,
        TipoParcelamento::VARIAVEL,
        TipoParcelamento::PERSONALIZADO,
    ];
    
    foreach ($expectedTipos as $expectedTipo) {
        expect($tipos)->toContain($expectedTipo);
    }
});

test('pode obter labels dos tipos de parcelamento', function () {
    expect(TipoParcelamento::A_VISTA->getLabel())->toBe('À Vista');
    expect(TipoParcelamento::FIXO->getLabel())->toBe('Parcelamento Fixo');
    expect(TipoParcelamento::VARIAVEL->getLabel())->toBe('Parcelamento Variável');
    expect(TipoParcelamento::PERSONALIZADO->getLabel())->toBe('Personalizado');
});

test('valida permissão de juros por tipo', function () {
    expect(TipoParcelamento::A_VISTA->permiteJuros())->toBeFalse();
    
    expect(TipoParcelamento::FIXO->permiteJuros())->toBeTrue();
    expect(TipoParcelamento::VARIAVEL->permiteJuros())->toBeTrue();
    expect(TipoParcelamento::PERSONALIZADO->permiteJuros())->toBeTrue();
});

test('valida permissão de entrada por tipo', function () {
    expect(TipoParcelamento::A_VISTA->permiteEntrada())->toBeFalse();
    
    expect(TipoParcelamento::FIXO->permiteEntrada())->toBeTrue();
    expect(TipoParcelamento::VARIAVEL->permiteEntrada())->toBeTrue();
    expect(TipoParcelamento::PERSONALIZADO->permiteEntrada())->toBeTrue();
});

test('obtem intervalo padrão correto', function () {
    expect(TipoParcelamento::A_VISTA->getIntervaloPatrao())->toBe(0);
    expect(TipoParcelamento::FIXO->getIntervaloPatrao())->toBe(30);
    expect(TipoParcelamento::VARIAVEL->getIntervaloPatrao())->toBe(30);
    expect(TipoParcelamento::PERSONALIZADO->getIntervaloPatrao())->toBe(0);
});

test('gera esquema de parcelamento à vista', function () {
    $esquema = TipoParcelamento::getEsquemaParcelamento(TipoParcelamento::A_VISTA);
    
    expect($esquema['quantidade_parcelas'])->toBe(1);
    expect($esquema['intervalo_dias'])->toBe(0);
    expect($esquema['juros_parcelamento'])->toBe(0);
    expect($esquema['entrada_percentual'])->toBe(0);
});

test('gera esquema de parcelamento fixo com parâmetros padrão', function () {
    $esquema = TipoParcelamento::getEsquemaParcelamento(TipoParcelamento::FIXO);
    
    expect($esquema['quantidade_parcelas'])->toBe(2);
    expect($esquema['intervalo_dias'])->toBe(30);
    expect($esquema['juros_parcelamento'])->toBe(0);
    expect($esquema['entrada_percentual'])->toBe(0);
});

test('gera esquema de parcelamento fixo com parâmetros customizados', function () {
    $parametros = [
        'quantidade_parcelas' => 5,
        'intervalo_dias' => 45,
        'juros_parcelamento' => 2.5,
        'entrada_percentual' => 20,
    ];
    
    $esquema = TipoParcelamento::getEsquemaParcelamento(TipoParcelamento::FIXO, $parametros);
    
    expect($esquema['quantidade_parcelas'])->toBe(5);
    expect($esquema['intervalo_dias'])->toBe(45);
    expect($esquema['juros_parcelamento'])->toBe(2.5);
    expect($esquema['entrada_percentual'])->toBe(20);
});

test('gera esquema de parcelamento variável', function () {
    $parametros = [
        'parcelas' => [
            ['valor' => 1000, 'vencimento' => '2024-03-01'],
            ['valor' => 1500, 'vencimento' => '2024-04-01'],
            ['valor' => 2000, 'vencimento' => '2024-05-01'],
        ],
        'juros_parcelamento' => 1.5,
    ];
    
    $esquema = TipoParcelamento::getEsquemaParcelamento(TipoParcelamento::VARIAVEL, $parametros);
    
    expect($esquema['parcelas'])->toHaveCount(3);
    expect($esquema['juros_parcelamento'])->toBe(1.5);
});

test('gera esquema personalizado com parâmetros específicos', function () {
    $parametrosPersonalizados = [
        'tipo_especial' => 'desconto_progressivo',
        'desconto_primeira_parcela' => 10,
        'multa_atraso' => 2,
        'juros_diario' => 0.03,
    ];
    
    $esquema = TipoParcelamento::getEsquemaParcelamento(TipoParcelamento::PERSONALIZADO, $parametrosPersonalizados);
    
    expect($esquema)->toBe($parametrosPersonalizados);
});
