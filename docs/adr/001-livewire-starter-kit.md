# ADR-001: Uso do Livewire como Starter Kit

**Status**: Aceito  
**Data**: 2026-02-02  
**Decisores**: Equipe de Arquitetura ERP

## Contexto

O projeto ERP para prestadores de serviços no Brasil requer interfaces complexas para:

- Formulários de NFS-e nacional com campos IBSCBS (Reforma Tributária 2026)
- Cadastros de clientes com validação em tempo real (CPF/CNPJ, Receita Federal)
- Dashboards multitenants com dados fiscais sensíveis
- Calculadoras de impostos dinâmicas (ISS, PIS/COFINS, IBSCBS teste)

Alternativas avaliadas:

1. **React**: SPA completo, alta performance, mas requer equipe JavaScript dedicada
2. **Vue**: Boa integração Laravel, mas ainda split frontend/backend
3. **Livewire**: Full-stack PHP, ideal para formulários complexos
4. **None**: Blade tradicional, limitado para interatividade necessária

## Decisão

Escolhemos **Livewire** como starter kit principal.

## Justificativa

### ✅ Pontos Favoráveis

**1. Compliance Fiscal Brasileiro**

- Validação server-side nativa para regras fiscais complexas
- Formulários NFS-e com validação em tempo real sem exposer lógica
- Integração simples com bibliotecas PHP fiscais (nfephp, focus-nfe)

**2. Multitenancy-Friendly**

- Estado do componente mantém contexto seguro da empresa
- Middleware de tenant resolution funciona nativamente
- Validação de acesso por tenant no controller do componente

**3. Produtividade da Equipe**

- Equipe PHP pode manter todo o stack
- Reutilização de validation rules entre API e UI
- Testing integrado com PHPUnit/Pest

**4. Casos de Uso Específicos**

```php
// Exemplo: Formulário NFS-e com validação fiscal
class EmitirNfse extends Component
{
    public $tomador = [];
    public $ibscbs = []; // Campos Reforma Tributária

    public function updatedTomadorCnpj()
    {
        // Validação em tempo real sem exposer regras
        $this->validateCnpj();
        $this->consultarReceita();
    }

    public function emitir()
    {
        $this->validate($this->nfseValidationRules());
        // Lógica fiscal permanece no servidor
    }
}
```

### ⚠️ Pontos de Atenção

**1. Performance**

- Requests AJAX para cada interação
- Mitigação: Alpine.js para interações simples

**2. UX Mobile**

- Latência em conexões lentas
- Mitigação: Loading states e offline support

**3. SEO**

- Não aplicável (sistema interno de gestão)

## Consequências

### Positivas

- ✅ Time-to-market reduzido para formulários fiscais
- ✅ Manutenção unificada (uma linguagem)
- ✅ Validação fiscal server-side segura
- ✅ Integração nativa com multitenancy Laravel

### Negativas

- ❌ Dependência de JavaScript para UX rica
- ❌ Possível lock-in Livewire (mitigado por Blade subjacente)
- ❌ Performance inferior a SPA para dashboards pesados

### Plano de Migração

Se necessário no futuro:

1. **Híbrido**: APIs Laravel + React para dashboards específicos
2. **Inertia.js**: Migração gradual mantendo backend Laravel
3. **API-first**: Livewire coexiste com API para mobile

## Implementação

```php
// Componente padrão ERP
abstract class ERPComponent extends Component
{
    protected function authorize(string $ability, $arguments = []): void
    {
        if (!auth()->user()->can($ability, $arguments)) {
            abort(403);
        }
    }

    protected function validateTenant(): void
    {
        if (!app('current.company')) {
            throw new TenantException('Contexto de empresa requerido');
        }
    }
}
```

## Monitoramento

- **Métricas**: Response time médio dos componentes < 300ms
- **UX**: Session recordings para identificar friction points
- **Performance**: Monitor N+1 queries em componentes

---

**Revisão**: Reavaliar em 6 meses após primeiros módulos em produção.
