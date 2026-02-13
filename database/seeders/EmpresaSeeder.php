<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Filial;
use App\Models\ParametroOperacional;
use App\Models\StatusEmpresaEnum;
use App\Models\TipoFilialEnum;
use App\Models\RegimeTributarioEnum;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar dados existentes
        ParametroOperacional::truncate();
        Filial::truncate();
        Empresa::truncate();
        
        // Criar empresa de exemplo
        $this->criarEmpresaExemplo();
        
        // Criar empresa de demonstração
        $this->criarEmpresaDemonstracao();
    }

    private function criarEmpresaExemplo(): void
    {
        $empresa = Empresa::create([
            'razao_social' => 'Tech Solutions Ltda',
            'nome_fantasia' => 'TechSol',
            'cnpj' => '12.345.678/0001-95', // CNPJ válido matematicamente
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributarioEnum::SIMPLES_NACIONAL,
            'email' => 'contato@techsol.com.br',
            'telefone' => '(11) 98765-4321',
            'status' => StatusEmpresaEnum::ATIVO,
            'dados_endereco' => [
                'cep' => '01310-100',
                'logradouro' => 'Av. Paulista',
                'numero' => '1000',
                'complemento' => 'Sala 1001',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
            ],
        ]);

        // Criar matriz
        $matriz = Filial::create([
            'empresa_id' => $empresa->id,
            'codigo' => '001',
            'nome' => 'Matriz São Paulo',
            'tipo' => TipoFilialEnum::MATRIZ,
            'cnpj' => $empresa->cnpj,
            'inscricao_estadual' => $empresa->inscricao_estadual,
            'email' => 'matriz@techsol.com.br',
            'telefone' => '(11) 98765-4321',
            'ativo' => true,
            'endereco' => $empresa->dados_endereco,
            'configuracao_fiscal' => [
                'regime_tributario' => $empresa->regime_tributario->value,
                'aliquota_simples' => 6.0,
                'optante_simples' => true,
                'codigo_regime_especial' => null,
                'inscricao_suframa' => null,
            ],
        ]);

        // Criar filial
        Filial::create([
            'empresa_id' => $empresa->id,
            'codigo' => '002',
            'nome' => 'Filial Rio de Janeiro',
            'tipo' => TipoFilialEnum::FILIAL,
            'cnpj' => '12.345.678/0002-76', // CNPJ da filial
            'inscricao_estadual' => '123.456.789.124',
            'email' => 'rj@techsol.com.br',
            'telefone' => '(21) 98765-4321',
            'ativo' => true,
            'endereco' => [
                'cep' => '20040-020',
                'logradouro' => 'Av. Rio Branco',
                'numero' => '156',
                'complemento' => '8º andar',
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
            ],
            'configuracao_fiscal' => [
                'regime_tributario' => RegimeTributarioEnum::SIMPLES_NACIONAL->value,
                'aliquota_simples' => 6.0,
                'optante_simples' => true,
                'codigo_regime_especial' => null,
                'inscricao_suframa' => null,
            ],
        ]);

        // Criar parâmetros operacionais
        $this->criarParametrosOperacionais($empresa->id);
    }

    private function criarEmpresaDemonstracao(): void
    {
        $empresa = Empresa::create([
            'razao_social' => 'Comércio e Serviços ABC S.A.',
            'nome_fantasia' => 'ABC Serviços',
            'cnpj' => '98.765.432/0001-10',
            'inscricao_estadual' => '987.654.321.987',
            'regime_tributario' => RegimeTributarioEnum::LUCRO_PRESUMIDO,
            'email' => 'contato@abcservicos.com.br',
            'telefone' => '(11) 91234-5678',
            'status' => StatusEmpresaEnum::ATIVO,
            'dados_endereco' => [
                'cep' => '04038-001',
                'logradouro' => 'Rua Vergueiro',
                'numero' => '2000',
                'complemento' => null,
                'bairro' => 'Vila Mariana',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
            ],
        ]);

        // Criar apenas matriz para esta empresa
        Filial::create([
            'empresa_id' => $empresa->id,
            'codigo' => '001',
            'nome' => 'Matriz ABC',
            'tipo' => TipoFilialEnum::MATRIZ,
            'cnpj' => $empresa->cnpj,
            'inscricao_estadual' => $empresa->inscricao_estadual,
            'email' => 'matriz@abcservicos.com.br',
            'telefone' => '(11) 91234-5678',
            'ativo' => true,
            'endereco' => $empresa->dados_endereco,
            'configuracao_fiscal' => [
                'regime_tributario' => $empresa->regime_tributario->value,
                'aliquota_simples' => null,
                'optante_simples' => false,
                'codigo_regime_especial' => 'LP001',
                'inscricao_suframa' => null,
            ],
        ]);

        // Criar parâmetros operacionais
        $this->criarParametrosOperacionais($empresa->id);
    }

    private function criarParametrosOperacionais(string $empresaId): void
    {
        $parametros = [
            [
                'chave' => 'smtp_host',
                'valor' => '"smtp.gmail.com"',
                'tipo' => 'string',
                'descricao' => 'Servidor SMTP para envio de e-mails',
                'publico' => false,
            ],
            [
                'chave' => 'smtp_port',
                'valor' => '587',
                'tipo' => 'integer',
                'descricao' => 'Porta do servidor SMTP',
                'publico' => false,
            ],
            [
                'chave' => 'timezone',
                'valor' => '"America/Sao_Paulo"',
                'tipo' => 'string',
                'descricao' => 'Fuso horário da empresa',
                'publico' => true,
            ],
            [
                'chave' => 'moeda_padrao',
                'valor' => '"BRL"',
                'tipo' => 'string',
                'descricao' => 'Moeda padrão para transações',
                'publico' => true,
            ],
            [
                'chave' => 'logo_url',
                'valor' => '"/storage/logos/default-logo.png"',
                'tipo' => 'string',
                'descricao' => 'URL do logotipo da empresa',
                'publico' => true,
            ],
            [
                'chave' => 'backup_automatico',
                'valor' => 'true',
                'tipo' => 'boolean',
                'descricao' => 'Ativar backup automático',
                'publico' => false,
            ],
            [
                'chave' => 'dias_retencao_log',
                'valor' => '90',
                'tipo' => 'integer',
                'descricao' => 'Dias de retenção dos logs',
                'publico' => false,
            ],
        ];

        foreach ($parametros as $parametro) {
            ParametroOperacional::create([
                'empresa_id' => $empresaId,
                'chave' => $parametro['chave'],
                'valor' => $parametro['valor'],
                'tipo' => $parametro['tipo'],
                'descricao' => $parametro['descricao'],
                'publico' => $parametro['publico'],
            ]);
        }
    }
}
