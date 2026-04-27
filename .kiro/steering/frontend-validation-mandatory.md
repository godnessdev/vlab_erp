---
inclusion: auto
priority: 1000
---

# 🚨 VALIDAÇÃO FRONTEND OBRIGATÓRIA

## ⚠️ REGRA CRÍTICA - LEIA ANTES DE QUALQUER IMPLEMENTAÇÃO FRONTEND

**VOCÊ DEVE SEGUIR ESTE PROCESSO PARA TODA FEATURE FRONTEND. SEM EXCEÇÕES.**

---

## 📋 PROCESSO OBRIGATÓRIO

### ANTES de Criar Componente

1. **Ler Backend Primeiro**
   ```bash
   # Ler models, services, enums relevantes
   # Verificar métodos, relacionamentos, colunas
   ```

2. **Verificar Componentes Flux UI**
   ```bash
   # Consultar .ai/FLUX-UI-FREE-COMPONENTS.md
   # Se componente não está na lista Free, NÃO USAR
   ```

### DURANTE a Criação

3. **Usar Apenas Componentes Validados**
   - ✅ Flux Free: card, modal, heading, input, button, badge, icon
   - ❌ Flux Pro: table, columns, rows, dropdown, pagination, toast
   - Alternativa: HTML + Tailwind CSS

### DEPOIS de Criar (OBRIGATÓRIO)

4. **Iniciar Servidor**
   ```bash
   php artisan serve
   ```

5. **Acessar Página no Navegador**
   ```
   http://127.0.0.1:8000/[rota]
   ```

6. **LER LOGS DO TERMINAL**
   - Procurar erros em vermelho
   - Procurar warnings
   - Procurar exceptions

7. **VERIFICAR CONSOLE DO NAVEGADOR (F12)**
   - Procurar erros JavaScript
   - Procurar erros Livewire
   - Procurar 404s

8. **TESTAR TODAS FUNCIONALIDADES**
   - Clicar em todos os botões
   - Preencher todos os formulários
   - Testar filtros
   - Testar dark mode
   - Testar mobile (F12 > Device Toolbar)

9. **EXECUTAR TESTES PEST**
   ```bash
   ./vendor/bin/pest tests/Feature/Livewire/[ComponenteTest.php]
   ```

10. **DOCUMENTAR TUDO**
    - Atualizar REGISTRO-IMPLEMENTACAO-FRONTEND.md
    - Documentar erros encontrados
    - Documentar correções aplicadas

---

## 🚫 NUNCA FAÇA ISSO

❌ Criar componente sem ler backend  
❌ Usar componente Flux sem verificar se existe na versão Free  
❌ Marcar feature como "pronta" sem testar no navegador  
❌ Ignorar erros no terminal  
❌ Ignorar erros no console  
❌ Pular testes automatizados  
❌ Não documentar erros encontrados  

---

## ✅ SEMPRE FAÇA ISSO

✅ Ler backend ANTES de criar frontend  
✅ Consultar .ai/FLUX-UI-FREE-COMPONENTS.md  
✅ Iniciar `php artisan serve`  
✅ Acessar página no navegador  
✅ Ler logs do terminal  
✅ Verificar console do navegador (F12)  
✅ Testar TODAS funcionalidades  
✅ Executar testes Pest  
✅ Documentar tudo  

---

## 🎯 CRITÉRIO DE CONCLUSÃO

Uma feature frontend só está completa quando:

1. ✅ Servidor Laravel rodando sem erros
2. ✅ Página carrega sem erros no terminal
3. ✅ Página carrega sem erros no console
4. ✅ Todas funcionalidades testadas manualmente
5. ✅ Testes Pest passando
6. ✅ Documentação atualizada

**SE QUALQUER ITEM ACIMA FALHAR, A FEATURE NÃO ESTÁ PRONTA.**

---

## 📝 TEMPLATE DE VALIDAÇÃO

Copie e preencha este checklist para cada feature:

```markdown
## ✅ Validação: [Nome da Feature]

### Pré-Criação
- [ ] Backend lido e compreendido
- [ ] Componentes Flux verificados
- [ ] Apenas componentes Free serão usados

### Criação
- [ ] Componente Livewire criado
- [ ] View Blade criada
- [ ] Rota registrada

### Validação (OBRIGATÓRIO)
- [ ] `php artisan serve` executado
- [ ] Página acessada: http://127.0.0.1:8000/[rota]
- [ ] Logs do terminal verificados: ZERO erros
- [ ] Console do navegador verificado: ZERO erros
- [ ] Funcionalidades testadas: TODAS funcionam
- [ ] Dark mode testado: funciona
- [ ] Mobile testado: funciona
- [ ] Testes Pest executados: TODOS passam

### Documentação
- [ ] REGISTRO-IMPLEMENTACAO-FRONTEND.md atualizado
- [ ] Erros documentados (se houver)
- [ ] Correções documentadas (se houver)

### Resultado
- [ ] ✅ Feature 100% funcional e validada
```

---

## 🔥 LEMBRE-SE

> **"Tempo gasto em validação é tempo economizado em correções."**

**Cada erro não detectado custa:**
- ⏱️ Tempo para debugar depois
- 💰 Créditos de API desperdiçados
- 😤 Frustração do usuário
- 🐛 Bugs em produção

**Cada validação feita economiza:**
- ✅ Tempo de desenvolvimento
- ✅ Créditos de API
- ✅ Confiança do usuário
- ✅ Qualidade do código

---

## 📚 DOCUMENTOS DE REFERÊNCIA

**SEMPRE consultar antes de criar frontend:**

1. `.ai/FLUX-UI-FREE-COMPONENTS.md` - Componentes disponíveis
2. `.ai/guidelines/frontend-validation.md` - Processo completo
3. `.ai/guidelines/erp-architecture.md` - Arquitetura DDD
4. `.ai/guidelines/multitenant-patterns.md` - Multitenancy
5. Backend relevante (Models, Services, Enums)

---

**ESTA REGRA É OBRIGATÓRIA E NÃO NEGOCIÁVEL.**

**SE VOCÊ ESTÁ LENDO ISSO, SIGNIFICA QUE VOCÊ DEVE SEGUIR O PROCESSO ACIMA.**

**NÃO HÁ EXCEÇÕES.**
