# 🔍 Sistema de Validação Frontend

## 📋 Visão Geral

Este sistema garante que **ZERO ERROS** cheguem ao código antes de considerar uma feature como pronta.

---

## 🎯 Objetivo

**Evitar perda de tempo e créditos com erros que poderiam ser detectados antes.**

### Problemas que Este Sistema Resolve

❌ **Antes**:
- Componentes Flux Pro usados sem verificação
- Erros SQL por ambiguidade de colunas
- Rotas não definidas
- Páginas quebradas em produção
- Tempo perdido debugando depois
- Créditos de API desperdiçados

✅ **Depois**:
- Validação automática antes de marcar como pronto
- Erros detectados imediatamente
- Componentes verificados antes de usar
- Páginas testadas antes de deploy
- Tempo economizado
- Créditos economizados

---

## 📁 Arquivos do Sistema

### 1. Documentação

#### `.ai/guidelines/frontend-validation.md`
- Processo completo de validação
- Workflow detalhado
- Template de validação
- Comandos úteis

#### `.kiro/steering/frontend-validation-mandatory.md`
- Regras obrigatórias (auto-incluído)
- Checklist de validação
- Critérios de conclusão

#### `.ai/FLUX-UI-FREE-COMPONENTS.md`
- Lista de componentes Flux Free disponíveis
- Lista de componentes Flux Pro proibidos
- Alternativas HTML + Tailwind

### 2. Scripts de Validação

#### `scripts/validate-frontend.ps1` (Windows)
Script PowerShell que valida automaticamente:
- Cache limpo
- Servidor respondendo
- Rota registrada
- Componentes Livewire carregados
- Sintaxe PHP válida
- Testes passando
- Nenhum componente Flux Pro usado

#### `scripts/validate-frontend.sh` (Linux/Mac)
Versão Bash do script acima.

---

## 🚀 Como Usar

### Passo 1: Antes de Criar Componente

```bash
# 1. Ler backend relevante
cat app/Models/Empresa.php
cat app/Services/EmpresaService.php
cat app/Models/RegimeTributarioEnum.php

# 2. Verificar componentes Flux disponíveis
cat .ai/FLUX-UI-FREE-COMPONENTS.md
```

### Passo 2: Criar Componente

```bash
# Criar componente Livewire
php artisan make:livewire Empresas/Index

# Criar view
# Usar APENAS componentes Flux Free
# Consultar .ai/FLUX-UI-FREE-COMPONENTS.md
```

### Passo 3: Validação Automática

#### Windows (PowerShell)
```powershell
.\scripts\validate-frontend.ps1 -Route "/gestao/empresas"
```

#### Linux/Mac (Bash)
```bash
./scripts/validate-frontend.sh /gestao/empresas
```

### Passo 4: Validação Manual

```bash
# 1. Iniciar servidor (em terminal separado)
php artisan serve

# 2. Acessar no navegador
# http://127.0.0.1:8000/gestao/empresas

# 3. Verificar logs do terminal
# Procurar erros em vermelho

# 4. Verificar console do navegador (F12)
# Procurar erros em vermelho

# 5. Testar funcionalidades
# - Clicar em todos os botões
# - Preencher formulários
# - Testar filtros
# - Testar dark mode
# - Testar mobile (F12 > Device Toolbar)
```

### Passo 5: Executar Testes

```bash
# Executar testes do componente
.\vendor\bin\pest tests/Feature/Livewire/Empresas/IndexTest.php

# Executar todos os testes
.\vendor\bin\pest

# Verificar coverage
.\vendor\bin\pest --coverage
```

### Passo 6: Documentar

```markdown
# Atualizar REGISTRO-IMPLEMENTACAO-FRONTEND.md

## ✅ Feature: Gestão de Empresas

### Validação Completa
- [x] Backend lido e compreendido
- [x] Componentes Flux verificados
- [x] Script de validação executado: PASSOU
- [x] Servidor testado: ZERO erros
- [x] Console testado: ZERO erros
- [x] Funcionalidades testadas: TODAS funcionam
- [x] Testes Pest: TODOS passam
- [x] Documentação atualizada

### Resultado
✅ Feature 100% funcional e validada
```

---

## 📋 Checklist de Validação

Use este checklist para cada feature:

### Pré-Criação
- [ ] Backend lido (Models, Services, Enums)
- [ ] Métodos verificados (existem e têm assinatura correta)
- [ ] Relacionamentos verificados
- [ ] Componentes Flux consultados (`.ai/FLUX-UI-FREE-COMPONENTS.md`)

### Criação
- [ ] Componente Livewire criado
- [ ] View Blade criada (apenas componentes Flux Free)
- [ ] Rota registrada
- [ ] Testes Pest criados

### Validação Automática
- [ ] Script executado: `.\scripts\validate-frontend.ps1 -Route "/rota"`
- [ ] Resultado: PASSOU

### Validação Manual
- [ ] Servidor iniciado: `php artisan serve`
- [ ] Página acessada no navegador
- [ ] Logs do terminal: ZERO erros
- [ ] Console do navegador (F12): ZERO erros
- [ ] Funcionalidades testadas: TODAS funcionam
- [ ] Dark mode testado: funciona
- [ ] Mobile testado (F12 > Device Toolbar): funciona

### Testes Automatizados
- [ ] Testes executados: `.\vendor\bin\pest`
- [ ] Resultado: TODOS passam
- [ ] Coverage: > 80%

### Documentação
- [ ] `REGISTRO-IMPLEMENTACAO-FRONTEND.md` atualizado
- [ ] Erros documentados (se houver)
- [ ] Correções documentadas (se houver)
- [ ] Padrões estabelecidos

### Conclusão
- [ ] ✅ Feature 100% funcional
- [ ] ✅ Zero erros
- [ ] ✅ Documentada
- [ ] ✅ Testada

---

## 🎯 Critérios de Conclusão

Uma feature só está completa quando **TODOS** os itens abaixo são verdadeiros:

1. ✅ Script de validação passou sem erros
2. ✅ Servidor Laravel rodando sem erros
3. ✅ Página carrega sem erros no terminal
4. ✅ Página carrega sem erros no console
5. ✅ Todas funcionalidades testadas manualmente
6. ✅ Dark mode funciona
7. ✅ Responsivo funciona
8. ✅ Testes Pest passando (100%)
9. ✅ Coverage > 80%
10. ✅ Documentação atualizada

**SE QUALQUER ITEM FALHAR, A FEATURE NÃO ESTÁ PRONTA.**

---

## 🚨 Erros Comuns e Como Evitar

### 1. Componente Flux Pro Usado

**Erro**:
```
InvalidArgumentException: Unable to locate a class or view for component [flux::columns]
```

**Como Evitar**:
1. Consultar `.ai/FLUX-UI-FREE-COMPONENTS.md` ANTES de usar
2. Executar script de validação (detecta componentes Pro)
3. Usar alternativa HTML + Tailwind

### 2. Coluna Ambígua em SQL

**Erro**:
```
SQLSTATE[HY000]: ambiguous column name: status
```

**Como Evitar**:
1. Ler relacionamentos do model ANTES de criar query
2. Usar `wherePivot()` em relacionamentos many-to-many
3. Especificar tabela: `->where('empresas.status', 'ATIVO')`

### 3. Rota Não Definida

**Erro**:
```
RouteNotFoundException: Route [empresas] not defined
```

**Como Evitar**:
1. Verificar `routes/web.php` ANTES de usar `route()`
2. Executar `php artisan route:list` para ver rotas disponíveis
3. Evitar conflitos entre rotas de API e UI

### 4. Método Não Existe

**Erro**:
```
BadMethodCallException: Method [listarEmpresas] does not exist
```

**Como Evitar**:
1. Ler Service ANTES de chamar método
2. Verificar assinatura do método (parâmetros, retorno)
3. Verificar se método é público

---

## 📊 Métricas de Sucesso

### Antes do Sistema de Validação
- ❌ 4 bugs encontrados após implementação
- ❌ ~2 horas perdidas debugando
- ❌ Créditos desperdiçados
- ❌ Frustração do usuário

### Depois do Sistema de Validação
- ✅ 0 bugs em produção (objetivo)
- ✅ Tempo economizado
- ✅ Créditos economizados
- ✅ Confiança do usuário

---

## 🔄 Workflow Completo

```
1. Planejar Feature
   ↓
2. Ler Backend (Models, Services, Enums)
   ↓
3. Verificar Componentes Flux (.ai/FLUX-UI-FREE-COMPONENTS.md)
   ↓
4. Criar Componente Livewire
   ↓
5. Criar View Blade (apenas Flux Free)
   ↓
6. Criar Testes Pest
   ↓
7. Executar Script de Validação
   ↓
8. Iniciar Servidor (php artisan serve)
   ↓
9. Acessar no Navegador
   ↓
10. Verificar Logs do Terminal (ZERO erros)
    ↓
11. Verificar Console do Navegador (ZERO erros)
    ↓
12. Testar Funcionalidades (TODAS)
    ↓
13. Executar Testes Pest (TODOS passam)
    ↓
14. Documentar (REGISTRO-IMPLEMENTACAO-FRONTEND.md)
    ↓
15. ✅ Feature Completa
```

---

## 📚 Referências

### Documentação Obrigatória
1. `.ai/guidelines/frontend-validation.md` - Processo completo
2. `.ai/FLUX-UI-FREE-COMPONENTS.md` - Componentes disponíveis
3. `.ai/guidelines/erp-architecture.md` - Arquitetura DDD
4. `.ai/guidelines/multitenant-patterns.md` - Multitenancy

### Scripts
1. `scripts/validate-frontend.ps1` - Validação Windows
2. `scripts/validate-frontend.sh` - Validação Linux/Mac

### Documentação de Implementação
1. `REGISTRO-IMPLEMENTACAO-FRONTEND.md` - Registro de tudo implementado
2. `PLANO-FRONTEND.md` - Plano de desenvolvimento
3. `CORRECAO-BUG-FLUX-TABLE.md` - Exemplo de correção documentada

---

## 💡 Dicas

### 1. Use o Script de Validação
```powershell
# Sempre execute antes de marcar como pronto
.\scripts\validate-frontend.ps1 -Route "/gestao/empresas"
```

### 2. Consulte a Documentação
```bash
# Antes de usar qualquer componente Flux
cat .ai/FLUX-UI-FREE-COMPONENTS.md
```

### 3. Leia o Backend Primeiro
```bash
# Sempre leia antes de criar frontend
cat app/Models/Empresa.php
cat app/Services/EmpresaService.php
```

### 4. Teste no Navegador
```bash
# Sempre teste com servidor rodando
php artisan serve
# Acesse: http://127.0.0.1:8000/rota
```

### 5. Verifique os Logs
```
# Terminal do servidor
# Procure por erros em vermelho

# Console do navegador (F12)
# Procure por erros em vermelho
```

---

## 🎓 Lições Aprendidas

### Bug 1: flux::columns não existe
**Lição**: Sempre verificar se componente Flux existe na versão Free ANTES de usar.

### Bug 2: Coluna status ambígua
**Lição**: Usar `wherePivot()` em relacionamentos many-to-many.

### Bug 3: Rota não definida
**Lição**: Verificar conflitos entre rotas de API e UI.

### Bug 4: Método não existe
**Lição**: Ler Service ANTES de chamar método.

---

## 🔒 Regra de Ouro

> **"Se não foi validado com o script E testado no navegador, não está pronto."**

**NUNCA** considere uma feature completa sem:
1. ✅ Script de validação executado e PASSOU
2. ✅ Servidor Laravel rodando (`php artisan serve`)
3. ✅ Página acessada no navegador
4. ✅ Logs do terminal verificados (ZERO erros)
5. ✅ Console do navegador verificado (ZERO erros)
6. ✅ Todas funcionalidades testadas manualmente
7. ✅ Testes Pest executados e TODOS passam
8. ✅ Documentação atualizada

---

**Sistema Criado**: 27 de Abril de 2026  
**Versão**: 1.0  
**Status**: Obrigatório para todas as features frontend  
**Objetivo**: ZERO ERROS em produção
