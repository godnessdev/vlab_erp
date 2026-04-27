# 🎨 Flux UI - Componentes Disponíveis (Versão Free)

## ⚠️ IMPORTANTE
Este projeto usa **Flux UI Free Edition**. Componentes marcados como "Pro" não estão disponíveis e causarão erros.

---

## ✅ Componentes Disponíveis (Free)

### Layout & Structure
- `<flux:card>` - Container com borda e padding
- `<flux:modal>` - Modal/Dialog overlay
- `<flux:separator>` - Linha divisória

### Typography
- `<flux:heading>` - Títulos (size: xs, sm, base, lg, xl, 2xl)
- `<flux:subheading>` - Subtítulos
- `<flux:text>` - Texto padrão

### Forms
- `<flux:input>` - Campo de texto
- `<flux:textarea>` - Campo de texto multilinha
- `<flux:select>` - Select/dropdown
- `<flux:checkbox>` - Checkbox
- `<flux:radio>` - Radio button
- `<flux:switch>` - Toggle switch
- `<flux:label>` - Label para campos
- `<flux:error>` - Mensagem de erro
- `<flux:field>` - Wrapper para campo + label + erro

### Buttons & Actions
- `<flux:button>` - Botão (variants: primary, secondary, ghost, outline, danger)
- `<flux:button-group>` - Grupo de botões

### Feedback
- `<flux:badge>` - Badge/tag (variants: success, warning, danger, info, outline)
- `<flux:callout>` - Alerta/aviso (variants: success, warning, danger, info)
- `<flux:spinner>` - Loading spinner

### Icons
- `<flux:icon.{name}>` - Ícones Heroicons
  - Exemplos: `building-office`, `user-group`, `clipboard`, `currency-dollar`, `document-text`, `check-circle`, `x-circle`, `exclamation-triangle`, `information-circle`, `pencil`, `trash`, `plus`, `magnifying-glass`, `map-pin`, `pause`, `play`

### Navigation
- `<flux:tabs>` - Abas/tabs
- `<flux:tab>` - Item de aba

---

## ❌ Componentes NÃO Disponíveis (Pro)

### Tables (Pro Only)
- ❌ `<flux:table>` - Tabela
- ❌ `<flux:columns>` - Colunas da tabela
- ❌ `<flux:column>` - Coluna individual
- ❌ `<flux:rows>` - Linhas da tabela
- ❌ `<flux:row>` - Linha individual
- ❌ `<flux:cell>` - Célula da tabela
- ❌ `<flux:data-table>` - Tabela com paginação/ordenação

### Advanced Components (Pro Only)
- ❌ `<flux:dropdown>` - Dropdown menu
- ❌ `<flux:menu>` - Menu de navegação
- ❌ `<flux:sidebar>` - Sidebar
- ❌ `<flux:navbar>` - Barra de navegação
- ❌ `<flux:breadcrumbs>` - Breadcrumbs
- ❌ `<flux:pagination>` - Paginação
- ❌ `<flux:toast>` - Notificações toast
- ❌ `<flux:tooltip>` - Tooltips
- ❌ `<flux:popover>` - Popovers
- ❌ `<flux:accordion>` - Accordion
- ❌ `<flux:command-palette>` - Command palette

---

## 🔄 Alternativas para Componentes Pro

### Tabelas
**Problema**: `flux::table` não existe na versão Free

**Solução**: Usar HTML + Tailwind CSS

```blade
<!-- ❌ NÃO FUNCIONA (Pro) -->
<flux:table>
    <flux:columns>
        <flux:column>Nome</flux:column>
    </flux:columns>
    <flux:rows>
        <flux:row>
            <flux:cell>Valor</flux:cell>
        </flux:row>
    </flux:rows>
</flux:table>

<!-- ✅ FUNCIONA (HTML + Tailwind) -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Nome
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="whitespace-nowrap px-6 py-4 text-gray-900 dark:text-gray-100">
                    Valor
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Dropdown Menu
**Problema**: `flux::dropdown` não existe na versão Free

**Solução**: Usar Livewire + Alpine.js

```blade
<!-- ✅ FUNCIONA (Alpine.js) -->
<div x-data="{ open: false }" class="relative">
    <flux:button @click="open = !open" icon="chevron-down">
        Menu
    </flux:button>
    
    <div x-show="open" 
         @click.away="open = false"
         class="absolute right-0 z-10 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800">
        <div class="py-1">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                Opção 1
            </a>
        </div>
    </div>
</div>
```

### Paginação
**Problema**: `flux::pagination` não existe na versão Free

**Solução**: Usar paginação nativa do Laravel

```blade
<!-- ✅ FUNCIONA (Laravel Pagination) -->
<div class="mt-4">
    {{ $items->links() }}
</div>
```

### Toast Notifications
**Problema**: `flux::toast` não existe na versão Free

**Solução**: Usar Livewire Events + Alpine.js

```blade
<!-- ✅ FUNCIONA (Alpine.js + Livewire) -->
<div x-data="{ show: false, message: '' }"
     @notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)">
    <div x-show="show" 
         x-transition
         class="fixed bottom-4 right-4 z-50 rounded-lg bg-green-500 px-4 py-3 text-white shadow-lg">
        <span x-text="message"></span>
    </div>
</div>

<!-- No componente Livewire -->
$this->dispatch('notify', message: 'Salvo com sucesso!');
```

---

## 📋 Checklist de Desenvolvimento

Antes de usar um componente Flux, verifique:

- [ ] O componente está na lista de "Disponíveis (Free)"?
- [ ] Se não estiver, há uma alternativa HTML + Tailwind?
- [ ] O componente tem todos os atributos necessários?
- [ ] O componente está dentro de um `<flux:card>` se necessário?
- [ ] As classes Tailwind incluem dark mode (`dark:`)?
- [ ] O componente é responsivo (grid, flex, etc.)?

---

## 🎨 Padrões de Estilo

### Cores (Tailwind)
```
Cinza: gray-50, gray-100, gray-200, ..., gray-900
Azul: blue-50, blue-100, ..., blue-900
Verde: green-50, green-100, ..., green-900
Vermelho: red-50, red-100, ..., red-900
Amarelo: yellow-50, yellow-100, ..., yellow-900
```

### Dark Mode
Sempre adicionar classes dark mode:
```blade
<div class="bg-white dark:bg-gray-900">
    <p class="text-gray-900 dark:text-gray-100">Texto</p>
</div>
```

### Espaçamento
```
Padding: p-2, p-4, p-6, px-4, py-2
Margin: m-2, m-4, m-6, mx-4, my-2
Gap: gap-2, gap-4, gap-6
```

### Grid Responsivo
```blade
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Mobile: 1 coluna -->
    <!-- Tablet: 2 colunas -->
    <!-- Desktop: 4 colunas -->
</div>
```

---

## 📚 Referências

- **Flux UI Docs**: https://fluxui.dev/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Heroicons**: https://heroicons.com
- **Alpine.js**: https://alpinejs.dev
- **Livewire**: https://livewire.laravel.com

---

**Última Atualização**: 27 de Abril de 2026  
**Versão Flux UI**: Free Edition  
**Versão Tailwind**: 3.x
