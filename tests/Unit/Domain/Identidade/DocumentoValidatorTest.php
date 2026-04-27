<?php

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Validators\DocumentoValidator;

beforeEach(function () {
    $this->validator = new DocumentoValidator;
});

describe('Validação CPF', function () {
    test('CPF válido é aceito', function () {
        $cpf = $this->validator->gerarCpfValido();

        expect($this->validator->validar(TipoDocumento::CPF, $cpf))->toBeTrue();
    });

    test('CPF inválido é rejeitado', function () {
        expect($this->validator->validar(TipoDocumento::CPF, '12345678901'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::CPF, '00000000000'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::CPF, '11111111111'))->toBeFalse();
    });

    test('CPF com formatação é validado', function () {
        $cpf = $this->validator->gerarCpfValido();
        $cpfFormatado = $this->validator->formatarDocumento(TipoDocumento::CPF, $cpf);

        expect($this->validator->validar(TipoDocumento::CPF, $cpfFormatado))->toBeTrue();
    });

    test('CPF é formatado corretamente', function () {
        $cpf = '12345678900';
        $formatado = $this->validator->formatarDocumento(TipoDocumento::CPF, $cpf);

        expect($formatado)->toBe('123.456.789-00');
    });

    test('gerador de CPF produz CPFs válidos', function () {
        for ($i = 0; $i < 10; $i++) {
            $cpf = $this->validator->gerarCpfValido();

            expect($this->validator->validar(TipoDocumento::CPF, $cpf))
                ->toBeTrue("CPF gerado {$cpf} deveria ser válido");
            expect($cpf)->toHaveLength(11);
        }
    });
});

describe('Validação CNPJ', function () {
    test('CNPJ válido é aceito', function () {
        $cnpj = $this->validator->gerarCnpjValido();

        expect($this->validator->validar(TipoDocumento::CNPJ, $cnpj))->toBeTrue();
    });

    test('CNPJ inválido é rejeitado', function () {
        expect($this->validator->validar(TipoDocumento::CNPJ, '12345678000100'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::CNPJ, '00000000000000'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::CNPJ, '11111111111111'))->toBeFalse();
    });

    test('CNPJ com formatação é validado', function () {
        $cnpj = $this->validator->gerarCnpjValido();
        $cnpjFormatado = $this->validator->formatarDocumento(TipoDocumento::CNPJ, $cnpj);

        expect($this->validator->validar(TipoDocumento::CNPJ, $cnpjFormatado))->toBeTrue();
    });

    test('CNPJ é formatado corretamente', function () {
        $cnpj = '12345678000195';
        $formatado = $this->validator->formatarDocumento(TipoDocumento::CNPJ, $cnpj);

        expect($formatado)->toBe('12.345.678/0001-95');
    });

    test('gerador de CNPJ produz CNPJs válidos', function () {
        for ($i = 0; $i < 10; $i++) {
            $cnpj = $this->validator->gerarCnpjValido();

            expect($this->validator->validar(TipoDocumento::CNPJ, $cnpj))
                ->toBeTrue("CNPJ gerado {$cnpj} deveria ser válido");
            expect($cnpj)->toHaveLength(14);
        }
    });
});

describe('Validação RG', function () {
    test('RG com tamanho válido é aceito', function () {
        $rgs = [
            '1234567',      // 7 dígitos
            '12345678',     // 8 dígitos
            '123456789',    // 9 dígitos
            '1234567890',   // 10 dígitos
        ];

        foreach ($rgs as $rg) {
            expect($this->validator->validar(TipoDocumento::RG, $rg))
                ->toBeTrue("RG {$rg} deveria ser válido");
        }
    });

    test('RG com tamanho inválido é rejeitado', function () {
        $rgsInvalidos = [
            '123456',           // Muito curto
            '1234567890123',    // Muito longo
            '000000',           // Muito curto
        ];

        foreach ($rgsInvalidos as $rg) {
            expect($this->validator->validar(TipoDocumento::RG, $rg))
                ->toBeFalse("RG {$rg} deveria ser inválido");
        }
    });

    test('RG não aceita sequências iguais', function () {
        expect($this->validator->validar(TipoDocumento::RG, '1111111'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::RG, '0000000'))->toBeFalse();
    });
});

describe('Validação IE', function () {
    test('IE válida é aceita', function () {
        expect($this->validator->validar(TipoDocumento::IE, '12345678901'))->toBeTrue();
        expect($this->validator->validar(TipoDocumento::IE, 'ISENTO'))->toBeTrue();
        expect($this->validator->validar(TipoDocumento::IE, 'ISENTA'))->toBeTrue();
    });

    test('IE muito curta é rejeitada', function () {
        expect($this->validator->validar(TipoDocumento::IE, '1234567'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::IE, ''))->toBeFalse();
    });
});

describe('Validação IM', function () {
    test('IM válida é aceita', function () {
        expect($this->validator->validar(TipoDocumento::IM, '123456789'))->toBeTrue();
        expect($this->validator->validar(TipoDocumento::IM, 'ISENTO'))->toBeTrue();
        expect($this->validator->validar(TipoDocumento::IM, 'ISENTA'))->toBeTrue();
    });

    test('IM muito curta é rejeitada', function () {
        expect($this->validator->validar(TipoDocumento::IM, '12345'))->toBeFalse();
        expect($this->validator->validar(TipoDocumento::IM, ''))->toBeFalse();
    });
});

describe('Validações múltiplas', function () {
    test('pode validar lista de documentos', function () {
        $documentos = [
            ['tipo' => 'CPF', 'valor' => $this->validator->gerarCpfValido()],
            ['tipo' => 'RG', 'valor' => '123456789'],
        ];

        $erros = $this->validator->validarDocumentosPessoa($documentos);

        expect($erros)->toBeEmpty();
    });

    test('retorna erros para documentos inválidos', function () {
        $documentos = [
            ['tipo' => 'CPF', 'valor' => '12345678901'], // CPF inválido
            ['tipo' => 'CNPJ', 'valor' => '12345678000100'], // CNPJ inválido
        ];

        $erros = $this->validator->validarDocumentosPessoa($documentos);

        expect($erros)->toHaveCount(2);
    });
});

describe('Formatação de documentos', function () {
    test('formatação RG funciona corretamente', function () {
        expect($this->validator->formatarDocumento(TipoDocumento::RG, '123456789'))
            ->toBe('12.345.678-9');
    });

    test('documento inválido retorna sem formatação', function () {
        expect($this->validator->formatarDocumento(TipoDocumento::CPF, '123'))
            ->toBe('123');

        expect($this->validator->formatarDocumento(TipoDocumento::CNPJ, '123'))
            ->toBe('123');
    });
});

describe('Máscaras', function () {
    test('retorna máscara correta para cada tipo', function () {
        expect($this->validator->obterMascara(TipoDocumento::CPF))->toBe('000.000.000-00');
        expect($this->validator->obterMascara(TipoDocumento::CNPJ))->toBe('00.000.000/0000-00');
        expect($this->validator->obterMascara(TipoDocumento::RG))->toBe('00.000.000-0');
    });
});
