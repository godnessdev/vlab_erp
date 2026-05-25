<x-layouts::app :title="'Pessoas'">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Pessoas</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Cadastro central de pessoas fisicas e juridicas.
                </p>
            </div>

            <a href="{{ route('pessoas.create') }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                Nova pessoa
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('pessoas.index') }}" class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-[1fr_180px_180px_auto]">
            <label class="block">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Busca</span>
                <input type="search"
                       name="busca"
                       value="{{ request('busca') }}"
                       class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="Nome, fantasia ou documento">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</span>
                <select name="tipo" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="FISICA" @selected(request('tipo') === 'FISICA')>Fisica</option>
                    <option value="JURIDICA" @selected(request('tipo') === 'JURIDICA')>Juridica</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</span>
                <select name="status" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="ATIVO" @selected(request('status') === 'ATIVO')>Ativo</option>
                    <option value="INATIVO" @selected(request('status') === 'INATIVO')>Inativo</option>
                </select>
            </label>

            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                    Filtrar
                </button>
                <a href="{{ route('pessoas.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Limpar
                </a>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Documento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Contato</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($pessoas as $pessoa)
                            @php
                                $documento = $pessoa->documentos->first(fn ($documento) => $documento->isPrincipal()) ?? $pessoa->documentos->first();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $pessoa->nome_razao_social }}</div>
                                    @if($pessoa->nome_fantasia)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $pessoa->nome_fantasia }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $pessoa->tipo->label() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $documento?->valor_formatado ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $pessoa->getEmailPrincipal() ?? $pessoa->getTelefonePrincipal() ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $pessoa->status->isAtivo() ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $pessoa->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('pessoas.show', $pessoa) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-900/30">Ver</a>
                                        <a href="{{ route('pessoas.edit', $pessoa) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhuma pessoa encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $pessoas->withQueryString()->links() }}
        </div>
    </div>
</x-layouts::app>
