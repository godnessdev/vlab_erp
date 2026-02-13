# ADR-003: Componentes Livewire com Arquivos Separados

**Status**: Aceito  
**Data**: 2026-02-02  
**Decisores**: Equipe de Arquitetura ERP

## Contexto

Sistema ERP requer componentes Livewire complexos para:

- **Formulários NFS-e**: 50+ campos incluindo IBSCBS (Reforma Tributária 2026)
- **Calculadoras fiscais**: Múltiplas seções (ICMS, ISS, PIS/COFINS, IBS/CBS teste)
- **Dashboards multitenants**: Charts, filtros, exportações
- **Cadastros completos**: Clientes, serviços, contratos

Laravel oferece duas abordagens:

1. **Single-file**: Classe + template em um arquivo PHP
2. **Separate files**: Classe PHP + template Blade separados

## Decisão

Escolhemos **arquivos separados** (classe PHP + template Blade) para todos os componentes Livewire.

## Justificativa

### ✅ Pontos Favoráveis

**1. Complexidade dos Formulários Fiscais**

Template NFS-e com arquivos separados:

```blade
{{-- resources/views/livewire/nfse/emitir-nfse.blade.php --}}
<div>
    {{-- Seção Prestador (20 linhas) --}}
    <x-card-section title="Dados do Prestador">
        <!-- Campos prestador -->
    </x-card-section>

    {{-- Seção Tomador (30 linhas) --}}
    <x-card-section title="Dados do Tomador">
        <div class="grid grid-cols-2 gap-4">
            <x-input
                label="CNPJ/CPF"
                wire:model.debounce.500ms="tomador.cnpj"
                mask="##.###.###/####-##"
                :error="$errors->first('tomador.cnpj')" />
        </div>
    </x-card-section>

    {{-- Seção Serviços (40 linhas) --}}
    <x-card-section title="Serviços Prestados">
        @foreach($servicos as $index => $servico)
            <!-- Campos por serviço -->
        @endforeach
    </x-card-section>

    {{-- Seção IBSCBS - Reforma Tributária 2026 (25 linhas) --}}
    <x-card-section title="IBSCBS - Informativo 2026" collapsible>
        <div class="bg-yellow-50 p-4 rounded mb-4">
            <p class="text-sm text-yellow-800">
                ℹ️ Campos informativos para teste da Reforma Tributária.
                Valores finais serão calculados pelo ADN.
            </p>
        </div>
        <!-- Campos IBSCBS -->
    </x-card-section>
</div>
```

Classe PHP focada em lógica:

```php
// app/Http/Livewire/Nfse/EmitirNfse.php
class EmitirNfse extends Component
{
    public $fatura;
    public $tomador = [];
    public $servicos = [];
    public $ibscbs = []; // Campos IBSCBS 2026

    protected $rules = [
        'tomador.cnpj' => 'required|cnpj',
        'servicos.*.codigo_municipal' => 'required|string',
        'ibscbs.finNFSe' => 'required|in:0,1',
        'ibscbs.cst' => 'required|string|size:2',
    ];

    public function mount(Fatura $fatura)
    {
        $this->authorize('nfse.emitir');
        $this->fatura = $fatura;
        $this->preencherDadosIniciais();
    }

    // 50+ linhas de lógica fiscal...
}
```

**2. Manutenibilidade em Equipe**

| Aspecto          | Single File              | Separate Files     |
| ---------------- | ------------------------ | ------------------ |
| **Frontend Dev** | Precisa entender PHP ❌  | Foca no Blade ✅   |
| **Backend Dev**  | Template misturado ❌    | Lógica isolada ✅  |
| **Code Review**  | Diff confuso ❌          | Mudanças claras ✅ |
| **IDE Support**  | Highlighting limitado ❌ | Full support ✅    |

**3. Reutilização de Templates**

```blade
{{-- Template base para formulários fiscais --}}
{{-- resources/views/livewire/fiscal/base-form.blade.php --}}
<x-fiscal-layout>
    <x-card-section title="{{ $title }}">
        @yield('prestador-section')
    </x-card-section>

    <x-card-section title="Dados do Tomador">
        @yield('tomador-section')
    </x-card-section>

    {{-- Seções reutilizáveis --}}
</x-fiscal-layout>

{{-- Componentes específicos extendem --}}
{{-- resources/views/livewire/nfse/emitir-nfse.blade.php --}}
@extends('livewire.fiscal.base-form')

@section('tomador-section')
    {{-- Campos específicos NFS-e --}}
@endsection
```

**4. Testing Granular**

```php
// Teste do template isolado
test('nfse form renders all required fields')
    ->livewire(EmitirNfse::class, ['fatura' => $fatura])
    ->assertSeeText('CNPJ/CPF')
    ->assertSeeText('IBSCBS - Informativo 2026')
    ->assertSee('wire:model="tomador.cnpj"');

// Teste da lógica isolada
test('validates cnpj format correctly')
    ->livewire(EmitirNfse::class, ['fatura' => $fatura])
    ->set('tomador.cnpj', '12.345.678/0001-90')
    ->assertHasNoErrors('tomador.cnpj');
```

### ⚠️ Pontos Negativos

**1. Mais Arquivos**

- Dobra o número de arquivos por componente
- Mitigação: Organização clara em pastas

**2. Context Switching**

- Dev precisa alternar entre PHP e Blade
- Mitigação: IDE com split view

## Consequências

### Positivas

- ✅ **Maintainability**: Código mais limpo e organizado
- ✅ **Team collaboration**: Frontend/backend podem trabalhar em paralelo
- ✅ **Template reuse**: Formulários fiscais compartilham layouts
- ✅ **IDE support**: Syntax highlighting completo
- ✅ **Testing**: Granularidade para testar lógica vs. apresentação

### Negativas

- ❌ **File proliferation**: Mais arquivos para gerenciar
- ❌ **Context switching**: Desenvolver requer alternar arquivos
- ❌ **Overhead inicial**: Setup mais complexo por componente

## Implementação

### 1. Estrutura de Pastas

```
app/Http/Livewire/
├── Fiscal/
│   ├── Nfse/
│   │   ├── EmitirNfse.php
│   │   ├── ConsultarNfse.php
│   │   └── CancelarNfse.php
│   └── Sped/
│       ├── GerarEfdContribuicoes.php
│       └── ValidarReinf.php
├── Clientes/
│   ├── CadastrarCliente.php
│   └── EditarCliente.php
└── Dashboard/
    ├── ResumoFiscal.php
    └── GraficosReceita.php

resources/views/livewire/
├── fiscal/
│   ├── nfse/
│   │   ├── emitir-nfse.blade.php
│   │   ├── consultar-nfse.blade.php
│   │   └── cancelar-nfse.blade.php
│   └── sped/
│       ├── gerar-efd-contribuicoes.blade.php
│       └── validar-reinf.blade.php
├── clientes/
│   ├── cadastrar-cliente.blade.php
│   └── editar-cliente.blade.php
└── dashboard/
    ├── resumo-fiscal.blade.php
    └── graficos-receita.blade.php
```

### 2. Base Component para ERP

```php
// app/Http/Livewire/ERPComponent.php
abstract class ERPComponent extends Component
{
    protected function authorize(string $ability, $arguments = []): void
    {
        if (!auth()->user()->can($ability, $arguments)) {
            abort(403, 'Acesso negado para: ' . $ability);
        }
    }

    protected function validateTenant(): void
    {
        if (!app('current.company')) {
            throw new TenantException('Contexto de empresa requerido');
        }
    }

    protected function auditAction(string $action, array $metadata = []): void
    {
        AuditLog::create([
            'action' => $action,
            'component' => static::class,
            'user_id' => auth()->id(),
            'company_id' => app('current.company')?->id,
            'metadata' => $metadata,
        ]);
    }
}
```

### 3. Naming Convention

| Tipo         | Convention          | Exemplo                 |
| ------------ | ------------------- | ----------------------- |
| **Class**    | PascalCase + Action | `EmitirNfse`            |
| **Template** | kebab-case + action | `emitir-nfse.blade.php` |
| **Folder**   | PascalCase          | `Nfse/`                 |
| **Route**    | kebab-case          | `nfse.emitir`           |

## Monitoramento

- **Code Quality**: Complexidade de templates < 50 linhas por seção
- **Performance**: Component render time < 100ms
- **Developer Experience**: Survey trimestral sobre produtividade
- **Maintainability**: Time para fix bugs vs. single-file benchmark

---

**Revisão**: Reavaliar após 6 meses de desenvolvimento se overhead compensa benefícios.
