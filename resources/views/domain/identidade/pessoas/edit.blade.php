<x-layouts::app :title="'Editar pessoa'">
    <div class="max-w-3xl space-y-6">
        <div>
            <a href="{{ route('pessoas.show', $pessoa) }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300">Voltar para detalhes</a>
            <h1 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar pessoa</h1>
        </div>

        @include('domain.identidade.pessoas.partials.form', [
            'pessoa' => $pessoa,
            'action' => route('pessoas.update', $pessoa),
            'method' => 'PUT',
        ])
    </div>
</x-layouts::app>
