# Modelagem de Banco de Dados - Sistema ERP

Este documento apresenta a modelagem completa do banco de dados do sistema ERP, organizado em 8 domínios principais seguindo princípios de Domain Driven Design (DDD) com foco em multitenancy e conformidade fiscal brasileira.

## 🏗️ Arquitetura Geral

O sistema é estruturado em domínios independentes com responsabilidades bem definidas:

- **Identidade e Pessoas**: Gestão centralizada de identidades
- **Empresa (Multitenant)**: Core do multitenancy e RBAC
- **Catálogo de Serviços**: Serviços e regras tributárias
- **Ordem de Serviço**: Gestão de ordens e execução
- **Faturamento**: Geração de faturas
- **Fiscal**: Conformidade fiscal (NFS-e, RPS)
- **Financeiro**: Gestão de contas a receber/pagar
- **Auditoria**: Logs e rastreabilidade

## 📋 Índice de Domínios

1. [Domínio de Identidade e Pessoas](./01-identidade-pessoas.md)
2. [Domínio de Empresa (Multitenant)](./02-empresa-multitenant.md)
3. [Domínio de Catálogo de Serviços](./03-catalogo-servicos.md)
4. [Domínio de Ordem de Serviço](./04-ordem-servico.md)
5. [Domínio de Faturamento](./05-faturamento.md)
6. [Domínio Fiscal](./06-fiscal.md)
7. [Domínio Financeiro](./07-financeiro.md)
8. [Domínio de Auditoria e Logs](./08-auditoria-logs.md)

## 🗂️ Diagramas ER

Cada domínio possui seu diagrama ER específico:

- [Diagrama ER - Identidade e Pessoas](./diagramas/er-identidade-pessoas.md)
- [Diagrama ER - Empresa](./diagramas/er-empresa.md)
- [Diagrama ER - Catálogo de Serviços](./diagramas/er-catalogo-servicos.md)
- [Diagrama ER - Ordem de Serviço](./diagramas/er-ordem-servico.md)
- [Diagrama ER - Faturamento](./diagramas/er-faturamento.md)
- [Diagrama ER - Fiscal](./diagramas/er-fiscal.md)
- [Diagrama ER - Financeiro](./diagramas/er-financeiro.md)
- [Diagrama ER - Auditoria](./diagramas/er-auditoria.md)
- [Diagrama ER - Completo](./diagramas/er-completo.md)

## 🎯 Decisões Arquiteturais Principais

### Multitenancy
- **Isolamento lógico** via `empresa_id` em todas as tabelas
- **Row Level Security (RLS)** para enforcement no banco
- **UUIDs** como PK para escalabilidade horizontal

### Modelagem Rica (DDD)
- **Agregados** bem definidos com raízes claras
- **Entidades** encapsulam comportamentos específicos
- **Value Objects** para dados estruturados (endereços, documentos)

### Conformidade Fiscal
- **State machines** para workflow de RPS/NFS-e
- **Integração assíncrona** com webservices municipais
- **Armazenamento de XMLs** para auditoria

### Performance e Escalabilidade
- **Índices compostos** para queries multitenancy
- **Particionamento** por data em tabelas de logs
- **Cache de permissões** para RBAC

### Auditoria e Conformidade
- **Logs imutáveis** para rastreabilidade
- **Histórico temporal** em entidades críticas
- **Conformidade LGPD** com rastreio de dados pessoais

## 📊 Métricas de Complexidade

| Domínio | Entidades | Relacionamentos | Complexidade |
|---------|-----------|-----------------|--------------|
| Identidade e Pessoas | 6 | 8 | Alta |
| Empresa | 7 | 10 | Alta |
| Catálogo de Serviços | 3 | 4 | Média |
| Ordem de Serviço | 4 | 6 | Média |
| Faturamento | 3 | 5 | Média |
| Fiscal | 9 | 12 | Muito Alta |
| Financeiro | 5 | 7 | Média |
| Auditoria | 4 | 4 | Baixa |

## 🔍 Padrões Utilizados

- **Aggregate Pattern**: Para garantir consistência de dados
- **Repository Pattern**: Para abstração de persistência
- **Event Sourcing**: Para logs e auditoria
- **CQRS**: Separação de comandos e consultas complexas
- **State Machine**: Para workflows fiscais
- **EAV Pattern**: Para dados extensíveis (configurações)

## 🛠️ Tecnologias Recomendadas

- **Banco**: PostgreSQL com extensões UUID e JSONB
- **ORM**: Eloquent (Laravel) ou Doctrine (Symfony)
- **Cache**: Redis para permissões e sessões
- **Queue**: Laravel Queues ou RabbitMQ para integração fiscal
- **Storage**: AWS S3 ou local para XMLs/PDFs

---

*Este documento é versionado e deve ser atualizado conforme evoluções do sistema.*
