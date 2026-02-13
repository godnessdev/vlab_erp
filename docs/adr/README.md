# Architecture Decision Records (ADR)

Este diretório contém todas as decisões arquiteturais importantes tomadas durante o desenvolvimento do ERP para prestadores de serviços.

## Formato dos ADRs

Cada ADR segue o template:

- **Status**: Proposto | Aceito | Rejeitado | Substituído
- **Contexto**: Situação que motivou a decisão
- **Decisão**: O que foi decidido
- **Consequências**: Impactos positivos e negativos

## Lista de ADRs

| ADR                                          | Título                                      | Status | Data       |
| -------------------------------------------- | ------------------------------------------- | ------ | ---------- |
| [ADR-001](001-livewire-starter-kit.md)       | Uso do Livewire como Starter Kit            | Aceito | 2026-02-02 |
| [ADR-002](002-laravel-built-in-auth.md)      | Laravel Built-in Authentication             | Aceito | 2026-02-02 |
| [ADR-003](003-livewire-separate-files.md)    | Componentes Livewire com Arquivos Separados | Aceito | 2026-02-02 |
| [ADR-004](004-pest-testing-framework.md)     | Pest como Framework de Testes               | Aceito | 2026-02-02 |
| [ADR-005](005-laravel-boost-ai-assistant.md) | Laravel Boost para AI-Assisted Development  | Aceito | 2026-02-02 |

## Princípios de Decisão

1. **Compliance Fiscal Brasileiro**: Todas as decisões consideram as complexidades do cenário tributário brasileiro de 2026
2. **Multitenancy**: Arquitetura deve suportar isolamento seguro entre empresas
3. **Manutenibilidade**: Código deve ser sustentável por equipes de diferentes níveis
4. **Performance**: Sistema deve escalar para milhares de usuários por tenant
5. **Segurança**: Zero tolerance para vazamentos de dados entre tenants
