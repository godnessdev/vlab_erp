<x-layouts::app :title="'Nova pessoa'">
    <div class="max-w-3xl space-y-6">
        <div>
            <a href="{{ route('pessoas.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300">Voltar para pessoas</a>
            <h1 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">Nova pessoa</h1>
        </div>

        @include('domain.identidade.pessoas.partials.form', [
            'pessoa' => null,
            'action' => route('pessoas.store'),
            'method' => 'POST',
        ])
    </div>
</x-layouts::app>
