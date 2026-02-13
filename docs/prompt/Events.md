Você está atuando como arquiteto de software sênior.
Projeto: ERP fiscal para prestadores de serviço brasileiros.
Framework: Laravel 12 + Event-driven architecture + Queue jobs.
Arquitetura: Domain events para comunicação entre 8 domínios DDD.

Use obrigatoriamente os seguintes documentos como fonte de verdade:

- .ai/guidelines/erp-architecture.md
- docs/modelagem-banco-dados/04-ordem-servico.md
- docs/modelagem-banco-dados/05-faturamento.md
- .ai/guidelines/performance-optimization.md

Tarefa:
Implementar workflow de eventos quando ordem de serviço é finalizada: deve disparar faturamento, cálculo fiscal, e audit trail seguindo patterns documentados.

Regras:

- Usar domain events conforme especificado
- Jobs de queue para operações assíncronas
- Manter tenant context em jobs
- Não violar boundaries entre domínios

Resultado esperado:

- OrdemServicoFinalizada event class
- Event listeners para cada domínio afetado
- Queue jobs com tenant awareness
- Pest tests para workflow completo
- Error handling e retry logic
