Você está atuando como arquiteto de software sênior.
Projeto: ERP fiscal para prestadores de serviço brasileiros.
Framework: Laravel 12 + Livewire (arquivos separados) + Pest testing.
Arquitetura: Components com class PHP + template Blade separados.

Use obrigatoriamente os seguintes documentos como fonte de verdade:

- docs/adr/003-livewire-separate-files.md
- .ai/guidelines/erp-architecture.md
- docs/modelagem-banco-dados/03-catalogo-servicos.md
- .ai/guidelines/testing-standards.md

Tarefa:
Criar componente Livewire para cadastro de serviço seguindo patterns de arquivos separados. Deve incluir código municipal, alíquota ISS, classificação tributária, e validação por tenant.

Regras:

- Usar ERPComponent como base conforme guidelines
- Arquivos separados: PHP class + Blade template
- Não misturar lógica no template
- Implementar authorization e tenant validation

Resultado esperado:

- CadastrarServico.php (Livewire class)
- cadastrar-servico.blade.php (template)
- Pest tests com tenant isolation
- Form request validation class
- Audit logging integration
