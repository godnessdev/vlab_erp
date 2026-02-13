<?php

use App\Domain\Servicos\Enums\RegimeTributario;
use App\Domain\Servicos\Enums\StatusServico;
use App\Domain\Servicos\Enums\UnidadeMedida;
use App\Domain\Servicos\Models\CodigoServicoMunicipal;
use App\Domain\Servicos\Models\RegraTributacao;
use App\Domain\Servicos\Models\Servico;
use App\Domain\Servicos\Services\ServicoService;
use App\Models\Empresa;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->servicoService = new ServicoService();
    $this->empresa = Empresa::factory()->create();
});

test('pode criar servico basico', function () {
    $dados = [
        'descricao' => 'Desenvolvimento de Software',
        'unidade_medida' => UnidadeMedida::HORA->value,
        'preco_base' => 150.00,
        'aliquota_iss_default' => 3.00,
        'classificacao_fiscal' => '6201-5/00',
        'observacoes' => 'Desenvolvimento web e mobile'
    ];

    $servico = $this->servicoService->criar($this->empresa->id, $dados);

    expect($servico)
        ->toBeInstanceOf(Servico::class)
        ->descricao->toBe($dados['descricao'])
        ->unidade_medida->value->toBe($dados['unidade_medida'])
        ->preco_base->toEqual(150.00)
        ->aliquota_iss_default->toEqual(3.00)
        ->classificacao_fiscal->toBe($dados['classificacao_fiscal'])
        ->status->toBe(StatusServico::ATIVO)
        ->empresa_id->toBe($this->empresa->id);
});

test('pode criar servico com codigos municipais', function () {
    $dados = [
        'descricao' => 'Consultoria Técnica',
        'unidade_medida' => UnidadeMedida::DIA->value,
        'preco_base' => 800.00,
        'aliquota_iss_default' => 5.00,
        'classificacao_fiscal' => '7020-4/00',
        'codigos_municipais' => [
            [
                'codigo_municipio_ibge' => '3550308',
                'codigo_servico' => '01.01',
                'descricao_municipal' => 'Análise e desenvolvimento de sistemas',
                'aliquota_iss' => 2.00,
                'data_vigencia_inicio' => now()->toDateString()
            ]
        ]
    ];

    $servico = $this->servicoService->criar($this->empresa->id, $dados);

    expect($servico->codigosMunicipais)->toHaveCount(1);
    
    $codigoMunicipal = $servico->codigosMunicipais->first();
    expect($codigoMunicipal)
        ->codigo_municipio_ibge->toBe('3550308')
        ->codigo_servico->toBe('01.01')
        ->aliquota_iss->toEqual(2.00);
});

test('pode criar servico com regras tributacao', function () {
    $dados = [
        'descricao' => 'Auditoria Contábil',
        'unidade_medida' => UnidadeMedida::PROJETO->value,
        'preco_base' => 5000.00,
        'aliquota_iss_default' => 5.00,
        'classificacao_fiscal' => '6920-6/01',
        'regras_tributacao' => [
            [
                'regime_tributario' => RegimeTributario::LUCRO_PRESUMIDO->value,
                'aliquota_ir' => 1.5,
                'aliquota_csll' => 1.0,
                'aliquota_pis' => 0.65,
                'aliquota_cofins' => 3.0,
                'retencao_inss' => false
            ]
        ]
    ];

    $servico = $this->servicoService->criar($this->empresa->id, $dados);

    expect($servico->regrasTributacao)->toHaveCount(1);
    
    $regra = $servico->regrasTributacao->first();
    expect($regra)
        ->regime_tributario->toBe(RegimeTributario::LUCRO_PRESUMIDO)
        ->aliquota_ir->toEqual(1.5)
        ->aliquota_csll->toEqual(1.0);
});

test('pode buscar servico por id', function () {
    $servico = Servico::factory()->create(['empresa_id' => $this->empresa->id]);

    $servicoEncontrado = $this->servicoService->buscar($this->empresa->id, $servico->id);

    expect($servicoEncontrado->id)->toBe($servico->id);
});

test('falha ao buscar servico inexistente', function () {
    expect(fn() => $this->servicoService->buscar($this->empresa->id, fake()->uuid()))
        ->toThrow(ValidationException::class);
});

test('pode atualizar servico', function () {
    $servico = Servico::factory()->create(['empresa_id' => $this->empresa->id]);

    $novosDados = [
        'descricao' => 'Nova Descrição',
        'preco_base' => 200.00
    ];

    $servicoAtualizado = $this->servicoService->atualizar(
        $this->empresa->id, 
        $servico->id, 
        $novosDados
    );

    expect($servicoAtualizado)
        ->descricao->toBe('Nova Descrição')
        ->preco_base->toEqual(200.00);
});

test('pode inativar servico', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::ATIVO
    ]);

    $servicoInativado = $this->servicoService->inativar($this->empresa->id, $servico->id);

    expect($servicoInativado->status)->toBe(StatusServico::INATIVO);
});

test('pode ativar servico', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::INATIVO
    ]);

    $servicoAtivado = $this->servicoService->ativar($this->empresa->id, $servico->id);

    expect($servicoAtivado->status)->toBe(StatusServico::ATIVO);
});

test('pode calcular tributacao basica', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'aliquota_iss_default' => 5.0
    ]);

    $calculo = $this->servicoService->calcularTributacao(
        $this->empresa->id,
        $servico->id,
        1000.00,
        RegimeTributario::SIMPLES_NACIONAL->value
    );

    expect($calculo)
        ->valor_base->toEqual(1000.00)
        ->impostos->toHaveKey('iss')
        ->valor_total_impostos->toEqual(50.0) // 5% de 1000
        ->valor_liquido->toEqual(950.0);
});

test('pode calcular tributacao com regra especifica', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'aliquota_iss_default' => 5.0
    ]);

    RegraTributacao::create([
        'servico_id' => $servico->id,
        'regime_tributario' => RegimeTributario::LUCRO_PRESUMIDO,
        'aliquota_ir' => 1.5,
        'aliquota_csll' => 1.0,
        'aliquota_pis' => 0.65,
        'aliquota_cofins' => 3.0,
    ]);

    $calculo = $this->servicoService->calcularTributacao(
        $this->empresa->id,
        $servico->id,
        1000.00,
        RegimeTributario::LUCRO_PRESUMIDO->value
    );

    expect($calculo['impostos'])
        ->toHaveKey('iss')
        ->toHaveKey('ir')
        ->toHaveKey('csll')
        ->toHaveKey('pis')
        ->toHaveKey('cofins');

    // ISS: 5% + IR: 1.5% + CSLL: 1% + PIS: 0.65% + COFINS: 3% = 11.15%
    expect($calculo['valor_total_impostos'])->toEqual(111.5);
});

test('pode obter aliquota iss municipal', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'aliquota_iss_default' => 5.0
    ]);

    CodigoServicoMunicipal::create([
        'servico_id' => $servico->id,
        'codigo_municipio_ibge' => '3550308',
        'codigo_servico' => '01.01',
        'aliquota_iss' => 2.0,
        'data_vigencia_inicio' => now()->toDateString()
    ]);

    $aliquota = $servico->obterAliquotaISS('3550308');

    expect($aliquota)->toEqual(2.0);
});

test('usa aliquota default quando nao tem codigo municipal', function () {
    $servico = Servico::factory()->create([
        'empresa_id' => $this->empresa->id,
        'aliquota_iss_default' => 5.0
    ]);

    $aliquota = $servico->obterAliquotaISS('1234567');

    expect($aliquota)->toEqual(5.0);
});

test('pode adicionar codigo municipal', function () {
    $servico = Servico::factory()->create(['empresa_id' => $this->empresa->id]);

    $dadosCodigo = [
        'codigo_municipio_ibge' => '3550308',
        'codigo_servico' => '01.01',
        'descricao_municipal' => 'Análise e desenvolvimento de sistemas',
        'aliquota_iss' => 2.0,
        'data_vigencia_inicio' => now()->toDateString()
    ];

    $codigo = $this->servicoService->adicionarCodigoMunicipal(
        $this->empresa->id,
        $servico->id,
        $dadosCodigo
    );

    expect($codigo)
        ->toBeInstanceOf(CodigoServicoMunicipal::class)
        ->servico_id->toBe($servico->id)
        ->codigo_municipio_ibge->toBe('3550308');
});

test('pode adicionar regra tributacao', function () {
    $servico = Servico::factory()->create(['empresa_id' => $this->empresa->id]);

    $dadosRegra = [
        'regime_tributario' => RegimeTributario::LUCRO_REAL->value,
        'aliquota_ir' => 15.0,
        'aliquota_csll' => 9.0,
        'aliquota_pis' => 1.65,
        'aliquota_cofins' => 7.6,
        'retencao_inss' => false
    ];

    $regra = $this->servicoService->adicionarRegraTributacao(
        $this->empresa->id,
        $servico->id,
        $dadosRegra
    );

    expect($regra)
        ->toBeInstanceOf(RegraTributacao::class)
        ->servico_id->toBe($servico->id)
        ->regime_tributario->toBe(RegimeTributario::LUCRO_REAL);
});

test('falha ao adicionar regra duplicada', function () {
    $servico = Servico::factory()->create(['empresa_id' => $this->empresa->id]);

    RegraTributacao::create([
        'servico_id' => $servico->id,
        'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
        'aliquota_ir' => 0,
        'aliquota_csll' => 0,
        'aliquota_pis' => 0,
        'aliquota_cofins' => 0,
    ]);

    $dadosRegra = [
        'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL->value,
        'aliquota_ir' => 1.0,
    ];

    expect(fn() => $this->servicoService->adicionarRegraTributacao(
        $this->empresa->id,
        $servico->id,
        $dadosRegra
    ))->toThrow(ValidationException::class);
});

test('pode listar servicos com filtros', function () {
    Servico::factory()->count(5)->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::ATIVO
    ]);

    Servico::factory()->count(2)->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::INATIVO
    ]);

    $servicos = $this->servicoService->listar($this->empresa->id, ['status' => 'ATIVO']);

    expect($servicos->total())->toBe(5);
});

test('pode obter estatisticas', function () {
    Servico::factory()->count(3)->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::ATIVO
    ]);

    Servico::factory()->count(2)->create([
        'empresa_id' => $this->empresa->id,
        'status' => StatusServico::INATIVO
    ]);

    $stats = $this->servicoService->obterEstatisticas($this->empresa->id);

    expect($stats)
        ->total->toBe(5)
        ->ativos->toBe(3)
        ->inativos->toBe(2);
});
