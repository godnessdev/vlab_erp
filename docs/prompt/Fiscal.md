Você está atuando como arquiteto de software sênior.
Projeto: ERP fiscal para prestadores de serviço brasileiros.
Framework: Laravel 12 + Livewire + PostgreSQL + RLS.
Arquitetura: DDD com 8 domínios, multitenancy via Row Level Security.

Use obrigatoriamente os seguintes documentos como fonte de verdade:

- .ai/guidelines/fiscal-compliance.md
- docs/modelagem-banco-dados/06-fiscal.md
- docs/adr/001-livewire-starter-kit.md
- docs/adr/004-pest-testing-framework.md

Tarefa:
Implementar calculadora de IBSCBS (Reforma Tributária 2026) para NFS-e nacional seguindo exatamente as regras dos documentos. Deve incluir fields informativos (não calcular valores - ADN fará), validação de CST, e integration com DPS workflow.

Regras:

- Não inventar alíquotas ou rates fora dos documentos
- Apontar se falta especificação de algum campo IBSCBS
- Usar strategy pattern para diferentes regimes tributários
- Implementar apenas campos informativos (valores vêm do ADN)

Resultado esperado:

- IBSCBSCalculator service class
- Value objects para CST, classification codes
- Livewire component para input IBSCBS
- Pest tests com datasets por regime tributário
- Migration com fields IBSCBS na tabela fiscal_documents
