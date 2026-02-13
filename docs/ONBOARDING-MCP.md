# Onboarding MCP - Model Context Protocol

## Guia Estratégico de Desenvolvimento ERP Multitenant

**Data de Criação**: 03 de Fevereiro de 2026  
**Versão**: 1.0  
**Status**: Em Desenvolvimento

---

## 📋 Índice de Conteúdo

1. [Visão Geral do Projeto](#1-visão-geral-do-projeto)
2. [Análise dos Domínios](#2-análise-dos-domínios)
3. [Roadmap de Implementação](#3-roadmap-de-implementação)
4. [Ordem de Desenvolvimento](#4-ordem-de-desenvolvimento)
5. [Definição de Partes (Componentes)](#5-definição-de-partes-componentes)
6. [Guidelines MCP](#6-guidelines-mcp)
7. [Checklist de Verificação](#7-checklist-de-verificação)

---

## 1. Visão Geral do Projeto

### 🎯 **Objetivo Principal**

Desenvolver ERP multitenant para prestadores de serviços no Brasil com compliance fiscal total para NFS-e nacional, IBSCBS (Reforma Tributária 2026), e integração com SPED/Reinf.

### 📊 **Métricas de Sucesso**

- **Performance**: < 200ms response time para 95% das requisições
- **Segurança**: Zero vazamentos de dados entre tenants
- **Compliance**: 100% conformidade fiscal (NFS-e, SPED, LGPD)
- **Escalabilidade**: 1000+ usuários simultâneos por tenant
- **Qualidade**: > 90% test coverage

### 🏗️ **Arquitetura Base**

- **Backend**: Laravel 12 + PHP 8.4 + PostgreSQL 16
- **Frontend**: Livewire + Alpine.js + Tailwind CSS
- **AI**: Laravel Boost + MCP + GitHub Copilot
- **Testing**: Pest + Architecture Testing
- **Multitenancy**: Row-Level Security (RLS) + Global Scopes

---

## 2. Análise dos Domínios

### 📈 **Priorização por Criticidade**

| Domínio                 | Criticidade | Complexidade | Dependências     | Ordem |
| ----------------------- | ----------- | ------------ | ---------------- | ----- |
| **Identity & Auth**     | 🔴 Crítica  | ⭐⭐⭐       | 0                | #1    |
| **Company Management**  | 🔴 Crítica  | ⭐⭐⭐⭐     | Identity         | #2    |
| **Fiscal Core**         | 🔴 Crítica  | ⭐⭐⭐⭐⭐   | Company          | #3    |
| **Services Management** | 🟡 Alta     | ⭐⭐⭐       | Company, Fiscal  | #4    |
| **Orders & Billing**    | 🟡 Alta     | ⭐⭐⭐⭐     | Services, Fiscal | #5    |
| **Financial**           | 🟢 Média    | ⭐⭐⭐⭐     | Billing          | #6    |
| **Auditing & Reports**  | 🟢 Média    | ⭐⭐⭐       | All              | #7    |

### 🔍 **Análise Detalhada por Domínio**

#### **Domínio 1: Identity & Authentication**

```
📂 Componentes Principais:
├── User Management (CRUD, roles, permissions)
├── Company Association (N:1 relationship)
├── Multi-Factor Authentication (2FA obrigatório)
├── Audit Trail (LGPD compliance)
└── Session Management (tenant-aware)

🎯 Entregáveis:
✅ Login/Register com 2FA
✅ Role-based permissions
✅ Company context resolution
✅ LGPD audit trail
```

#### **Domínio 2: Company Management**

```
📂 Componentes Principais:
├── Company Registration (CNPJ, IE, IM)
├── Tenant Context Management
├── Settings & Configuration
├── Branch Management (multi-location)
└── Integration Credentials (SEFAZ certificates)

🎯 Entregáveis:
✅ Company onboarding flow
✅ Tenant isolation (RLS)
✅ Settings management
✅ Certificate upload/validation
```

#### **Domínio 3: Fiscal Core**

```
📂 Componentes Principais:
├── NFS-e Nacional (DPS → ADN → NFS-e)
├── IBSCBS Calculator (Reforma Tributária 2026)
├── Tax Calculation Engine (ISS, PIS/COFINS)
├── SEFAZ Integration (contingency, validation)
└── SPED Generation (EFD-Contribuições, Reinf)

🎯 Entregáveis:
✅ NFS-e emission workflow
✅ IBSCBS informative fields
✅ Tax calculation by regime
✅ Contingency protocols
✅ SPED export functionality
```

---

## 3. Roadmap de Implementação

### 📅 **Cronograma por Sprints (2 semanas cada)**

#### **Sprint 1-2: Foundation (Semanas 1-4)**

```
🏗️ Infraestrutura Base
├── ✅ Laravel 12 setup + Boost integration
├── ✅ Database design + migrations
├── ✅ Authentication system + 2FA
├── ✅ Multitenancy foundation (RLS)
└── ✅ Base testing framework (Pest)

📊 Entregáveis:
- Sistema de login funcional
- Isolamento básico entre empresas
- Estrutura de testes definida
- CI/CD pipeline básico
```

#### **Sprint 3-4: Core Company (Semanas 5-8)**

```
🏢 Company Management
├── Company registration flow
├── Settings management system
├── Tenant context middleware
├── Certificate management
└── Basic dashboard

📊 Entregáveis:
- Onboarding de empresas completo
- Dashboard básico funcional
- Upload de certificados A1/A3
- Configurações por tenant
```

#### **Sprint 5-8: Fiscal Engine (Semanas 9-16)**

```
📋 Core Fiscal
├── Tax calculation engine
├── NFS-e nacional integration
├── IBSCBS implementation (informativo)
├── Contingency protocols
└── Validation framework

📊 Entregáveis:
- Cálculo automático de impostos
- Emissão NFS-e funcional
- Campos IBSCBS informativos
- Sistema de contingência robusto
```

#### **Sprint 9-12: Services & Orders (Semanas 17-24)**

```
⚙️ Business Operations
├── Service catalog management
├── Customer management (B2B focus)
├── Order processing workflow
├── Billing automation
└── Contract management

📊 Entregáveis:
- Catálogo de serviços completo
- Gestão de clientes B2B
- Workflow de pedidos automatizado
- Faturamento recorrente
```

#### **Sprint 13-16: Financial & Reports (Semanas 25-32)**

```
💰 Financial Management
├── Chart of accounts (Brazilian standard)
├── Double-entry bookkeeping
├── Financial reports
├── SPED integration (EFD-Contribuições)
└── Reinf automation

📊 Entregáveis:
- Contabilidade integrada
- Relatórios fiscais automáticos
- Exportação SPED completa
- Integração Reinf funcional
```

---

## 4. Ordem de Desenvolvimento

### 🎯 **Fase 1: Foundation & Identity (Concluído)**

```bash
# ✅ Já implementado
✅ Laravel 12 com Livewire
✅ Pest para testes
✅ Laravel Boost + MCP setup
✅ Documentação ADR completa
✅ Guidelines AI definidos
```

### 🔄 **Fase 2: Authentication & Multitenancy (Em Desenvolvimento)**

#### **2.1 Implementar Authentication Base**

```php
// Ordem de implementação:
1. User model + migrations
2. Company model + relationship
3. Role & Permission system
4. 2FA implementation (Google Authenticator)
5. Middleware tenant resolution
6. Audit logging system
```

#### **2.2 Configurar Multitenancy**

```sql
-- Database setup
1. Enable Row Level Security (RLS)
2. Create tenant policies
3. Configure session variables
4. Test isolation between companies
```

### 🏢 **Fase 3: Company Management**

#### **3.1 Company Registration**

```php
// Componentes Livewire:
1. CompanyRegistration (onboarding)
2. CompanySettings (configurações)
3. CertificateUpload (A1/A3 certificates)
4. BranchManagement (filiais)
```

#### **3.2 Tenant Configuration**

```php
// Services:
1. TenantContextService
2. SettingsService (per-tenant config)
3. CertificateValidationService
4. CompanyValidationService (CNPJ, IE)
```

### 📋 **Fase 4: Fiscal Core**

#### **4.1 Tax Calculation Engine**

```php
// Calculators (seguindo fiscal-compliance.md):
1. ICMSCalculator
2. ISSCalculator
3. PISCOFINSCalculator
4. IBSCBSCalculator (informativo 2026)
5. TaxRegimeService (Simples, Real, Presumido)
```

#### **4.2 NFS-e Nacional Integration**

```php
// Services:
1. NfseNacionalService (DPS → ADN)
2. IBSCBSService (campos informativos)
3. ContingencyService (offline protocols)
4. ValidationService (business rules)
5. XMLGeneratorService (structured data)
```

---

## 5. Definição de Partes (Componentes)

### 🧩 **Arquitetura de Componentes**

#### **A. Domain Layer (Core Business)**

```
Domain/
├── Identity/
│   ├── User.php (Aggregate Root)
│   ├── Role.php
│   ├── Permission.php
│   └── Events/
│       ├── UserLoggedIn.php
│       └── CompanyContextChanged.php
├── Company/
│   ├── Company.php (Aggregate Root)
│   ├── Branch.php
│   ├── Settings.php
│   └── ValueObjects/
│       ├── CNPJ.php
│       └── TaxRegime.php
└── Fiscal/
    ├── NfseDocument.php (Aggregate Root)
    ├── TaxCalculation.php
    ├── IBSCBSData.php
    └── Events/
        ├── NfseEmitted.php
        └── TaxCalculated.php
```

#### **B. Application Layer (Use Cases)**

```
Application/
├── Services/
│   ├── AuthenticationService.php
│   ├── CompanyService.php
│   ├── NfseService.php
│   └── TaxCalculationService.php
├── DTOs/
│   ├── CompanyRegistrationDTO.php
│   ├── NfseEmissionDTO.php
│   └── TaxCalculationDTO.php
└── Queries/
    ├── GetCompanyDashboard.php
    └── GetFiscalReports.php
```

#### **C. Infrastructure Layer (Technical)**

```
Infrastructure/
├── Repositories/
│   ├── UserRepository.php
│   ├── CompanyRepository.php
│   └── NfseRepository.php
├── External/
│   ├── SefazClient.php
│   ├── ReceitaFederalClient.php
│   └── FocusNfeClient.php
├── Cache/
│   ├── TenantCache.php
│   └── FiscalDataCache.php
└── Security/
    ├── TenantSecurityService.php
    └── AuditService.php
```

#### **D. Presentation Layer (UI/API)**

```
Presentation/
├── Livewire/
│   ├── Auth/
│   │   ├── Login.php
│   │   └── TwoFactorAuth.php
│   ├── Company/
│   │   ├── Register.php
│   │   └── Settings.php
│   └── Fiscal/
│       ├── EmitNfse.php
│       └── TaxCalculator.php
├── API/
│   ├── Controllers/
│   │   ├── CompanyController.php
│   │   └── NfseController.php
│   └── Resources/
│       ├── CompanyResource.php
│       └── NfseResource.php
└── Views/
    └── livewire/
        ├── auth/
        ├── company/
        └── fiscal/
```

---

## 6. Guidelines MCP

### 🤖 **Configuração AI Guidelines**

#### **6.1 Estrutura de Prompts**

```markdown
## Template de Prompt para MCP

### Context

"Desenvolva [COMPONENT] para ERP multitenant brasileiro seguindo:"

### Requirements

- Domain: [IDENTITY|COMPANY|FISCAL|SERVICES|ORDERS|FINANCIAL|AUDITING]
- Pattern: [DDD|Repository|Service|Livewire Component]
- Guidelines: [security-standards.md, fiscal-compliance.md, etc.]
- Tests: Pest com scenarios múltiplos

### Constraints

- Multitenancy: Isolamento obrigatório via RLS
- LGPD: Audit trail para todas operações
- Performance: < 200ms response time
- Fiscal: Compliance NFS-e nacional + IBSCBS 2026

### Output Format

- PHP 8.4 syntax com tipos strict
- Livewire class + Blade template separados
- Pest tests com datasets fiscais
- Documentation inline
```

#### **6.2 AI Validation Checklist**

```yaml
# .ai/validation-checklist.yml
security:
  - tenant_isolation: true
  - audit_trail: true
  - input_validation: true
  - authorization_check: true

fiscal:
  - nfse_compliance: true
  - ibscbs_fields: true (informativo)
  - tax_calculation: true
  - contingency_protocol: true

performance:
  - response_time: < 200ms
  - query_optimization: true
  - cache_strategy: true
  - n_plus_one_prevention: true

testing:
  - unit_coverage: > 80%
  - integration_tests: true
  - multitenancy_tests: true
  - fiscal_scenarios: true
```

---

## 7. Checklist de Verificação

### ✅ **Checklist por Feature**

#### **Authentication & Identity**

- [ ] User registration com company assignment
- [ ] Login com 2FA obrigatório
- [ ] Role-based access control (RBAC)
- [ ] Company context resolution middleware
- [ ] LGPD audit trail completo
- [ ] Session management tenant-aware
- [ ] Password policies por empresa
- [ ] Account lockout após tentativas falhas

#### **Company Management**

- [ ] Company registration com validação CNPJ
- [ ] Certificate upload (A1/A3) e validação
- [ ] Settings per-tenant (configurações fiscais)
- [ ] Branch management (múltiplas filiais)
- [ ] Tenant isolation total (RLS + global scopes)
- [ ] Dashboard básico com KPIs
- [ ] Integration credentials management
- [ ] Company onboarding workflow

#### **Fiscal Core**

- [ ] Tax calculation engine (ISS, PIS/COFINS)
- [ ] NFS-e nacional integration (DPS → ADN)
- [ ] IBSCBS campos informativos (Reforma 2026)
- [ ] Contingency protocols (offline mode)
- [ ] CFOP determination automática
- [ ] SEFAZ integration com retry logic
- [ ] XML validation contra schemas oficiais
- [ ] SPED export (EFD-Contribuições)

### 🎯 **Critérios de Aceitação**

#### **Funcional**

- ✅ Zero vazamentos entre tenants
- ✅ Emissão NFS-e em < 5 segundos
- ✅ Cálculo fiscal automático e preciso
- ✅ Contingência funcional quando SEFAZ offline

#### **Não-Funcional**

- ✅ Response time < 200ms (95% requests)
- ✅ Uptime > 99.9% (SLA)
- ✅ Test coverage > 90%
- ✅ Zero security vulnerabilities críticas

#### **Compliance**

- ✅ LGPD conformance total
- ✅ NFS-e nacional specification
- ✅ IBSCBS informativos 2026
- ✅ SPED/Reinf integration ready

---

## 📋 **Próximos Passos Imediatos**

### **Semana 1 (03-09 Fev 2026)**

1. ✅ Finalizar setup Laravel Boost + MCP
2. ✅ Implementar User model + migrations
3. ✅ Configurar authentication base (Livewire)
4. ✅ Setup Row Level Security (PostgreSQL)
5. ✅ Criar primeiros testes Pest

### **Semana 2 (10-16 Fev 2026)**

1. [ ] Company model + relationship
2. [ ] 2FA implementation com Google Authenticator
3. [ ] Middleware tenant resolution
4. [ ] Audit logging system (LGPD)
5. [ ] Dashboard básico Livewire

### **Meta para Final de Fevereiro**

- ✅ Authentication completo e funcional
- ✅ Multitenancy com isolamento total
- ✅ Base para desenvolvimento fiscal (Março)
- ✅ CI/CD pipeline funcionando
- ✅ Documentação atualizada

---

**🎯 Este documento serve como north star para todo desenvolvimento do ERP. Cada feature deve ser validada contra estes critérios antes de ser considerada completa.**
