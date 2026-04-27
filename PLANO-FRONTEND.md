# 🎨 Plano de Desenvolvimento Frontend - ERP Multitenant

## 📋 Status Atual

### ✅ Já Implementado

1. **Autenticação** (Laravel Fortify + Livewire)
   - Login/Logout
   - Registro
   - Password Reset
   - 2FA (Two-Factor Authentication)
   - Profile Settings

2. **Gestão de Empresas** (Parcial)
   - ✅ Listagem de empresas
   - ✅ Filtros (busca, status, regime tributário)
   - ✅ Estatísticas (cards)
   - ✅ CRUD básico (criar, editar, ativar, inativar, excluir)
   - ⚠️ Falta: Gestão de filiais, upload de certificados

3. **Infraestrutura**
   - ✅ Livewire 4.x configurado
   - ✅ Flux UI components
   - ✅ Tailwind CSS v4
   - ✅ Alpine.js
   - ✅ Layout base (sidebar, header)

### ❌ Não Implementado

1. **Dashboard** - Apenas placeholder
2. **Gestão de Filiais** - Não existe
3. **Gestão de Pessoas** - Rotas existem, mas sem UI Livewire
4. **Módulo Fiscal** - Rotas existem, mas sem UI
5. **Módulo de Serviços** - Rotas existem, mas sem UI
6. **Módulo de Ordens de Serviço** - Rotas existem, mas sem UI
7. **Módulo de Faturamento** - Rotas existem, mas sem UI
8. **Módulo Financeiro** - Rotas existem, mas sem UI

---

## 🎯 Roadmap de Desenvolvimento Frontend

Seguindo o **ONBOARDING-MCP.md**, vamos implementar na seguinte ordem:

### **Fase 1: Dashboard & Company Management** (Prioridade ALTA)
**Objetivo**: Completar o módulo de gestão de empresas e criar dashboard funcional

#### 1.1 Dashboard Principal
- [ ] Criar componente Livewire `Dashboard/Index.php`
- [ ] Cards de estatísticas gerais
- [ ] Gráficos de faturamento (últimos 30 dias)
- [ ] Lista de últimas ordens de serviço
- [ ] Alertas fiscais (certificados vencendo, NFS-e pendentes)
- [ ] Quick actions (nova OS, emitir NFS-e, etc.)

#### 1.2 Gestão de Filiais
- [ ] Criar componente Livewire `Filiais/Index.php`
- [ ] Criar componente Livewire `Filiais/Form.php`
- [ ] Listagem de filiais por empresa
- [ ] CRUD completo de filiais
- [ ] Associação de endereços
- [ ] Configurações fiscais por filial

#### 1.3 Upload de Certificados Digitais
- [ ] Criar componente Livewire `Empresas/Certificados.php`
- [ ] Upload de certificado A1/A3
- [ ] Validação de certificado
- [ ] Exibição de dados do certificado
- [ ] Alertas de vencimento

---

### **Fase 2: Gestão de Pessoas** (Prioridade ALTA)
**Objetivo**: Criar UI para gestão de clientes/fornecedores

#### 2.1 Listagem de Pessoas
- [ ] Criar componente Livewire `Pessoas/Index.php`
- [ ] Filtros (tipo pessoa, status, papéis)
- [ ] Busca por nome/documento
- [ ] Estatísticas (total clientes, fornecedores, etc.)
- [ ] Ações em massa

#### 2.2 Formulário de Pessoa
- [ ] Criar componente Livewire `Pessoas/Form.php`
- [ ] Dados básicos (nome, tipo pessoa, status)
- [ ] Gestão de documentos (CPF, CNPJ, RG, etc.)
- [ ] Gestão de endereços (múltiplos)
- [ ] Gestão de contatos (telefone, email, etc.)
- [ ] Gestão de papéis (cliente, fornecedor, etc.)
- [ ] Dados específicos por papel

#### 2.3 Visualização de Pessoa
- [ ] Criar componente Livewire `Pessoas/Show.php`
- [ ] Tabs (dados, documentos, endereços, contatos, histórico)
- [ ] Timeline de eventos
- [ ] Histórico de transações

---

### **Fase 3: Módulo Fiscal** (Prioridade CRÍTICA)
**Objetivo**: Criar UI para emissão de NFS-e e gestão fiscal

#### 3.1 Dashboard Fiscal
- [ ] Criar componente Livewire `Fiscal/Dashboard.php`
- [ ] Estatísticas fiscais (NFS-e emitidas, canceladas, etc.)
- [ ] Gráficos de faturamento por regime
- [ ] Alertas de contingência
- [ ] Status SEFAZ

#### 3.2 Gestão de RPS
- [ ] Criar componente Livewire `Fiscal/Rps/Index.php`
- [ ] Criar componente Livewire `Fiscal/Rps/Form.php`
- [ ] Listagem de RPS
- [ ] Criação de RPS
- [ ] Conversão RPS → NFS-e
- [ ] Cancelamento de RPS

#### 3.3 Gestão de NFS-e
- [ ] Criar componente Livewire `Fiscal/Nfse/Index.php`
- [ ] Criar componente Livewire `Fiscal/Nfse/Show.php`
- [ ] Listagem de NFS-e
- [ ] Visualização de NFS-e (XML, PDF)
- [ ] Download de XML/PDF
- [ ] Cancelamento de NFS-e
- [ ] Reenvio de email
- [ ] Consulta status SEFAZ

#### 3.4 Lotes de RPS
- [ ] Criar componente Livewire `Fiscal/Lotes/Index.php`
- [ ] Criar componente Livewire `Fiscal/Lotes/Show.php`
- [ ] Listagem de lotes
- [ ] Criação de lote
- [ ] Envio de lote para SEFAZ
- [ ] Consulta de protocolo
- [ ] Visualização de retorno

#### 3.5 Cálculo de Impostos
- [ ] Criar componente Livewire `Fiscal/Calculadora.php`
- [ ] Calculadora de ISS
- [ ] Calculadora de PIS/COFINS
- [ ] Calculadora de retenções
- [ ] Simulação de cálculo por regime

---

### **Fase 4: Módulo de Serviços** (Prioridade ALTA)
**Objetivo**: Criar catálogo de serviços

#### 4.1 Gestão de Serviços
- [ ] Criar componente Livewire `Servicos/Index.php`
- [ ] Criar componente Livewire `Servicos/Form.php`
- [ ] Listagem de serviços
- [ ] CRUD de serviços
- [ ] Categorização de serviços
- [ ] Códigos municipais (LC 116/2003)
- [ ] Alíquotas de ISS por município
- [ ] Preços e descontos

---

### **Fase 5: Módulo de Ordens de Serviço** (Prioridade ALTA)
**Objetivo**: Criar workflow de ordens de serviço

#### 5.1 Listagem de OS
- [ ] Criar componente Livewire `OrdensServico/Index.php`
- [ ] Filtros (status, cliente, período)
- [ ] Kanban board (opcional)
- [ ] Estatísticas de OS

#### 5.2 Formulário de OS
- [ ] Criar componente Livewire `OrdensServico/Form.php`
- [ ] Seleção de cliente
- [ ] Adição de serviços
- [ ] Cálculo automático de valores
- [ ] Cálculo de impostos
- [ ] Observações e anexos

#### 5.3 Visualização de OS
- [ ] Criar componente Livewire `OrdensServico/Show.php`
- [ ] Dados da OS
- [ ] Timeline de status
- [ ] Ações (aprovar, executar, faturar, cancelar)
- [ ] Geração de NFS-e

---

### **Fase 6: Módulo de Faturamento** (Prioridade ALTA)
**Objetivo**: Criar gestão de faturas e parcelas

#### 6.1 Gestão de Faturas
- [ ] Criar componente Livewire `Faturamento/Faturas/Index.php`
- [ ] Criar componente Livewire `Faturamento/Faturas/Show.php`
- [ ] Listagem de faturas
- [ ] Visualização de fatura
- [ ] Geração de boleto
- [ ] Envio de fatura por email
- [ ] Cancelamento de fatura

#### 6.2 Gestão de Parcelas
- [ ] Criar componente Livewire `Faturamento/Parcelas/Index.php`
- [ ] Listagem de parcelas
- [ ] Baixa de parcelas
- [ ] Renegociação de parcelas
- [ ] Histórico de pagamentos

---

### **Fase 7: Módulo Financeiro** (Prioridade MÉDIA)
**Objetivo**: Criar gestão financeira

#### 7.1 Contas a Receber
- [ ] Criar componente Livewire `Financeiro/ContasReceber/Index.php`
- [ ] Listagem de contas a receber
- [ ] Baixa de recebimentos
- [ ] Conciliação bancária

#### 7.2 Contas a Pagar
- [ ] Criar componente Livewire `Financeiro/ContasPagar/Index.php`
- [ ] Listagem de contas a pagar
- [ ] Baixa de pagamentos
- [ ] Agendamento de pagamentos

#### 7.3 Fluxo de Caixa
- [ ] Criar componente Livewire `Financeiro/FluxoCaixa/Index.php`
- [ ] Visualização de fluxo de caixa
- [ ] Gráficos de entrada/saída
- [ ] Projeções

---

## 📊 Priorização por Sprint

### **Sprint 1 (Semana 1-2)**: Dashboard & Filiais
- Dashboard funcional com estatísticas reais
- Gestão de filiais completa
- Upload de certificados digitais

### **Sprint 2 (Semana 3-4)**: Gestão de Pessoas
- Listagem e CRUD de pessoas
- Gestão de documentos, endereços, contatos
- Gestão de papéis

### **Sprint 3 (Semana 5-6)**: Módulo Fiscal - Parte 1
- Dashboard fiscal
- Gestão de RPS
- Gestão de NFS-e (listagem e visualização)

### **Sprint 4 (Semana 7-8)**: Módulo Fiscal - Parte 2
- Lotes de RPS
- Calculadora de impostos
- Integração SEFAZ

### **Sprint 5 (Semana 9-10)**: Serviços e OS - Parte 1
- Catálogo de serviços
- Listagem de OS
- Formulário de OS

### **Sprint 6 (Semana 11-12)**: Serviços e OS - Parte 2
- Visualização de OS
- Workflow de aprovação
- Geração de NFS-e a partir de OS

### **Sprint 7 (Semana 13-14)**: Faturamento
- Gestão de faturas
- Gestão de parcelas
- Geração de boletos

### **Sprint 8 (Semana 15-16)**: Financeiro
- Contas a receber
- Contas a pagar
- Fluxo de caixa

---

## 🎨 Padrões de UI/UX

### Componentes Flux UI a Usar

1. **Layouts**
   - `flux:card` - Cards de conteúdo
   - `flux:modal` - Modais
   - `flux:dropdown` - Dropdowns

2. **Formulários**
   - `flux:field` - Wrapper de campo
   - `flux:input` - Inputs de texto
   - `flux:select` - Selects
   - `flux:checkbox` - Checkboxes
   - `flux:radio` - Radio buttons
   - `flux:textarea` - Textareas
   - `flux:error` - Mensagens de erro

3. **Navegação**
   - `flux:button` - Botões
   - `flux:breadcrumbs` - Breadcrumbs
   - `flux:navbar` - Navbar

4. **Dados**
   - `flux:table` - Tabelas
   - `flux:badge` - Badges
   - `flux:avatar` - Avatares
   - `flux:icon` - Ícones

5. **Feedback**
   - `flux:callout` - Alertas/Avisos
   - `flux:tooltip` - Tooltips
   - `flux:skeleton` - Loading states

### Padrões de Cores

- **Primary**: Azul (ações principais)
- **Success**: Verde (sucesso, ativo)
- **Danger**: Vermelho (erro, excluir, inativo)
- **Warning**: Amarelo (avisos, pendente)
- **Info**: Azul claro (informações)

### Padrões de Layout

1. **Listagem**
   - Header com título e botão de ação
   - Cards de estatísticas (4 colunas)
   - Filtros em card separado
   - Tabela com ações
   - Empty state quando sem dados

2. **Formulário**
   - Modal para criar/editar
   - Campos agrupados logicamente
   - Validação em tempo real
   - Botões de ação no footer

3. **Visualização**
   - Header com título e ações
   - Tabs para diferentes seções
   - Cards para informações agrupadas
   - Timeline para histórico

---

## ✅ Checklist de Qualidade

Para cada componente criado, verificar:

- [ ] Segue arquitetura DDD (Domain/Application/Presentation)
- [ ] Aplica tenant isolation (global scopes)
- [ ] Implementa audit trail (LGPD)
- [ ] Adiciona authorization checks
- [ ] Inclui testes Pest com multitenancy
- [ ] Usa Livewire 4.x (separate files)
- [ ] Usa Flux UI components
- [ ] Responsivo (mobile-first)
- [ ] Dark mode support
- [ ] Loading states
- [ ] Empty states
- [ ] Error handling
- [ ] Success feedback
- [ ] Performance < 200ms

---

## 🚀 Próximo Passo

**Começar com Sprint 1**: Dashboard & Filiais

Quer que eu comece implementando o Dashboard funcional?
