# ERP Multitenant com Arquitetura de Agentes (Laravel 12)

> **ERP SaaS multitenant para prestadores de serviços, com conformidade fiscal brasileira, arquitetura DDD, segurança LGPD, automação por agentes de IA e Model Context Protocol (MCP).**

---

## Índice
- [Visão Geral](#visão-geral)
- [Stack Tecnológica](#stack-tecnológica)
- [Arquitetura & Domínios](#arquitetura--domínios)
- [Funcionalidades](#funcionalidades)
- [Fluxo de Uso](#fluxo-de-uso)
- [Requisitos e Instalação](#requisitos-e-instalação)
- [Configuração](#configuração)
- [Testes & Qualidade](#testes--qualidade)
- [Segurança & Compliance](#segurança--compliance)
- [Monitoramento & Observabilidade](#monitoramento--observabilidade)
- [Contribuição](#contribuição)
- [Licença](#licença)

---

## Visão Geral

Este ERP foi projetado para empresas de serviços que precisam de robustez, escalabilidade, multitenancy, automação fiscal e segurança de dados. Utiliza o que há de mais moderno no ecossistema Laravel, com arquitetura orientada a domínios, integração nativa com agentes de IA (Boost/MCP) e práticas enterprise.

---

## Stack Tecnológica

**Backend:**
- Laravel 12.x (PHP 8.4+)
- PostgreSQL 16+ (RLS, JSONB, UUID)
- Redis 7+ (cache, filas, rate limit)
- Livewire 4.x (componentização dinâmica)
- Vite + Tailwind CSS 4.x (frontend)
- Pest, PHPUnit, Dusk (testes)
- Boost/MCP (agentes, automação, docs)

**Infraestrutura:**
- AWS S3 (storage)
- Laravel Forge/Vapor (deploy)
- CloudFlare (CDN, proteção)
- Sentry, Pulse, New Relic (monitoramento)

**Dev Experience:**
- Guidelines customizadas em `.ai/guidelines/`
- CI/CD com GitHub Actions
- Docker (Sail)

---

## Arquitetura & Domínios

### Estrutura de Diretórios
```text
app/
├── Domain/
│   ├── Identidade/   # Usuários, papéis, permissões
│   ├── Empresa/      # Dados da empresa/tenant
│   ├── Servicos/     # Catálogo de serviços
│   ├── OrdemServico/ # Ordens de serviço
│   ├── Faturamento/  # Faturas, cobranças
│   ├── Fiscal/       # NFS-e, RPS, compliance
│   ├── Financeiro/   # Contas, fluxo de caixa
│   └── Auditoria/    # Logs, rastreio LGPD
├── Http/Controllers/ # APIs, web, Livewire
├── Jobs/             # Processamento assíncrono
├── Services/         # Integrações, lógica de negócio
├── Models/           # Eloquent, Enums, Traits
├── Livewire/         # Componentes dinâmicos
└── ...
```

### Padrões e Guidelines
- DDD: Domínios isolados, services, events, policies
- Multitenancy: Isolamento por empresa_id, RLS, cache segregado
- Compliance Fiscal: NFS-e, RPS, logs imutáveis, assinatura digital
- Segurança: LGPD, criptografia, logs de acesso, consentimento
- Performance: índices compostos, cache Redis, filas otimizadas

---

## Funcionalidades
- Gestão de empresas, filiais, usuários e permissões
- Catálogo de serviços e ordens de serviço
- Faturamento, emissão de NFS-e/RPS, retenções
- Controle financeiro: contas, fluxo de caixa, conciliação
- Multitenancy: isolamento total de dados
- Auditoria e logs LGPD
- Interface web responsiva e SPA-like
- APIs RESTful e integração com agentes MCP

---

## Fluxo de Uso
1. **Cadastro de empresa/tenant**
2. **Configuração de usuários, papéis e permissões**
3. **Cadastro de serviços e clientes**
4. **Geração de ordens de serviço**
5. **Faturamento e emissão fiscal (NFS-e/RPS)**
6. **Gestão financeira e relatórios**
7. **Auditoria, logs e compliance**

---

## Requisitos e Instalação

### Requisitos Mínimos
- PHP >= 8.2 (ideal 8.4+)
- Composer >= 2.5
- Node.js >= 18
- PostgreSQL >= 16 (ou SQLite para dev)
- Redis >= 7

### Instalação Rápida
```bash
git clone <url-do-repositorio>
cd ERP
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

### Configuração do .env
Veja `.env.example` para todas as variáveis (DB, cache, mail, queue, S3, etc).

---

## Configuração

- Configure o banco (PostgreSQL recomendado, com RLS)
- Ajuste variáveis de cache, mail, queue, S3, etc
- Para multitenancy, use o middleware e traits de tenant
- Para compliance fiscal, configure certificados digitais e endpoints NFS-e
- Para desenvolvimento, utilize guidelines em `.ai/guidelines/`

---

## Testes & Qualidade

- Testes unitários, feature e browser (Pest, PHPUnit, Dusk)
- Cobertura de testes para domínios críticos
- Lint e análise estática (Pint, Larastan, PHPMD)
- CI/CD automatizado

**Rodando os testes:**
```bash
./vendor/bin/pest
```

---

## Segurança & Compliance

- LGPD: logs de acesso, anonimização, consentimento
- RLS: isolamento de dados por tenant
- Criptografia de dados sensíveis
- Assinatura digital de XMLs fiscais
- Auditoria imutável

**Exemplo de trait para logs LGPD:**
```php
trait LogsLgpdAccess {
   protected static function bootLogsLgpdAccess(): void {
      static::retrieved(function ($model) {
         if ($model instanceof Pessoa) {
            RastreioLgpd::create([
               'pessoa_id' => $model->id,
               'usuario_id' => auth()->id(),
               'empresa_id' => tenant()->id,
               'tipo_acao' => TipoAcaoLgpd::VISUALIZACAO,
               'finalidade' => request()->route()->getName(),
               'dados_acessados' => $model->toArray(),
               'base_legal' => BaseLegalLgpd::LEGITIMO_INTERESSE,
               'ip_origem' => request()->ip(),
            ]);
         }
      });
   }
}
```

---

## Monitoramento & Observabilidade

- Pulse, Sentry, New Relic, LogRocket
- Métricas customizadas por tenant
- Slow query tracking

**Exemplo:**
```php
Pulse::record('nfse_emitida', 1, [
   'empresa_id' => $nfse->empresa_id,
   'municipio' => $nfse->municipio_prestacao,
])->count();

DB::listen(function ($query) {
   if ($query->time > 1000) {
      Pulse::record('slow_query', $query->time, [
         'sql' => $query->sql,
         'tenant_id' => tenant()->id ?? 'unknown',
      ])->avg();
   }
});
```

---

## Contribuição
1. Fork este repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nome-feature`)
3. Commit suas alterações (`git commit -m 'feat: minha feature'`)
4. Push para a branch (`git push origin feature/nome-feature`)
5. Abra um Pull Request

---

## Licença
MIT
