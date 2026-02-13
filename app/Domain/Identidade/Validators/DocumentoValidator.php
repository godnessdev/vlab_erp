<?php

namespace App\Domain\Identidade\Validators;

use App\Domain\Identidade\Enums\TipoDocumento;

class DocumentoValidator
{
    public function validar(TipoDocumento $tipo, string $valor): bool
    {
        $valor = $this->limpar($valor);

        return match ($tipo) {
            TipoDocumento::CPF => $this->validarCpf($valor),
            TipoDocumento::CNPJ => $this->validarCnpj($valor),
            TipoDocumento::RG => $this->validarRg($valor),
            TipoDocumento::IE => $this->validarIe($valor),
            TipoDocumento::IM => $this->validarIm($valor),
            default => false,
        };
    }

    private function validarCpf(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = $this->limpar($cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se não é uma sequência de números iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        // Verifica o primeiro dígito
        if ($cpf[9] != $digit1) {
            return false;
        }

        // Calcula o segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        // Verifica o segundo dígito
        return $cpf[10] == $digit2;
    }

    private function validarCnpj(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = $this->limpar($cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Verifica se não é uma sequência de números iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        // Verifica o primeiro dígito
        if ($cnpj[12] != $digit1) {
            return false;
        }

        // Calcula o segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        // Verifica o segundo dígito
        return $cnpj[13] == $digit2;
    }

    private function validarRg(string $rg): bool
    {
        // Remove caracteres não numéricos
        $rg = $this->limpar($rg);

        // RG deve ter entre 7 e 12 dígitos (varia por estado)
        $length = strlen($rg);
        if ($length < 7 || $length > 12) {
            return false;
        }

        // Verifica se não é uma sequência de números iguais
        if (preg_match('/(\d)\1{' . ($length - 1) . '}/', $rg)) {
            return false;
        }

        // RG não tem algoritmo de validação universal no Brasil
        // Aqui implementamos validações básicas
        return true;
    }

    private function validarIe(string $ie): bool
    {
        // Remove caracteres não numéricos e converte para maiúsculo
        $ie = strtoupper(preg_replace('/[^0-9A-Z]/', '', $ie));

        // IE varia muito por estado, implementação básica
        if (empty($ie)) {
            return false;
        }

        // Verifica casos especiais
        if (in_array($ie, ['ISENTO', 'ISENTA'])) {
            return true;
        }

        // Deve ter pelo menos 8 caracteres
        if (strlen($ie) < 8) {
            return false;
        }

        // Implementar validações específicas por estado se necessário
        return true;
    }

    private function validarIm(string $im): bool
    {
        // Remove caracteres não numéricos e converte para maiúsculo
        $im = strtoupper(preg_replace('/[^0-9A-Z]/', '', $im));

        // IM varia por município
        if (empty($im)) {
            return false;
        }

        // Verifica casos especiais
        if (in_array($im, ['ISENTO', 'ISENTA'])) {
            return true;
        }

        // Deve ter pelo menos 6 caracteres
        if (strlen($im) < 6) {
            return false;
        }

        // Implementar validações específicas por município se necessário
        return true;
    }

    public function validarDocumentosPessoa(array $documentos): array
    {
        $erros = [];

        foreach ($documentos as $documento) {
            $tipo = TipoDocumento::from($documento['tipo']);
            $valor = $documento['valor'];

            if (!$this->validar($tipo, $valor)) {
                $erros[] = "Documento {$tipo->label()} inválido: {$valor}";
            }
        }

        return $erros;
    }

    public function obterMascara(TipoDocumento $tipo): string
    {
        return $tipo->getMask();
    }

    public function formatarDocumento(TipoDocumento $tipo, string $valor): string
    {
        $valor = $this->limpar($valor);

        return match ($tipo) {
            TipoDocumento::CPF => $this->formatarCpf($valor),
            TipoDocumento::CNPJ => $this->formatarCnpj($valor),
            TipoDocumento::RG => $this->formatarRg($valor),
            default => $valor,
        };
    }

    private function formatarCpf(string $cpf): string
    {
        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return sprintf('%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    private function formatarCnpj(string $cnpj): string
    {
        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }

        return sprintf('%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    private function formatarRg(string $rg): string
    {
        $length = strlen($rg);
        if ($length < 7) {
            return $rg;
        }

        $digitoVerificador = substr($rg, -1);
        $numero = substr($rg, 0, -1);

        // Formatar com pontos a cada 3 dígitos da direita para esquerda
        $numeroFormatado = strrev(implode('.', str_split(strrev($numero), 3)));
        
        return $numeroFormatado . '-' . $digitoVerificador;
    }

    private function limpar(string $documento): string
    {
        return preg_replace('/[^0-9A-Z]/', '', strtoupper($documento));
    }

    public function gerarCpfValido(): string
    {
        // Gera 9 primeiros dígitos aleatórios
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= rand(0, 9);
        }

        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit1;

        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit2;

        return $cpf;
    }

    public function gerarCnpjValido(): string
    {
        // Gera 12 primeiros dígitos aleatórios
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= rand(0, 9);
        }

        // Calcula primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        $cnpj .= $digit1;

        // Calcula segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        $cnpj .= $digit2;

        return $cnpj;
    }
}
