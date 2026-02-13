Você está atuando como arquiteto de software sênior.
Projeto: ERP fiscal para prestadores de serviço brasileiros.
Framework: Laravel 12 + PostgreSQL RLS + LGPD compliance.
Arquitetura: Multitenancy via Row Level Security, isolamento total entre empresas.

Use obrigatoriamente os seguintes documentos como fonte de verdade:

- .ai/guidelines/multitenant-patterns.md
- .ai/guidelines/security-standards.md
- docs/modelagem-banco-dados/02-empresa-multitenant.md
- docs/adr/002-laravel-built-in-auth.md

Tarefa:
Implementar middleware de tenant resolution e context management seguindo patterns documentados. Deve resolver tenant via subdomain, header, ou user fallback, com validação de pertencimento e audit trail LGPD.

Regras:

- Seguir exatamente a ordem de resolution dos documentos
- Implementar RLS policies conforme especificado
- Não pular validação de pertencimento user→company
- Audit trail obrigatório para LGPD

Resultado esperado:

- TenantResolutionMiddleware class
- RLS policies SQL para PostgreSQL
- Audit logging para todas resolutions
- Pest tests para isolation entre tenants
- Exception handling para tenant inválido
