<form method="POST" action="{{ $action }}" class="space-y-6 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</span>
            <select name="tipo" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required>
                <option value="FISICA" @selected(old('tipo', $pessoa?->tipo?->value ?? 'FISICA') === 'FISICA')>Pessoa fisica</option>
                <option value="JURIDICA" @selected(old('tipo', $pessoa?->tipo?->value) === 'JURIDICA')>Pessoa juridica</option>
            </select>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nascimento/constituicao</span>
            <input type="date" name="data_nascimento_constituicao" value="{{ old('data_nascimento_constituicao', $pessoa?->data_nascimento_constituicao?->format('Y-m-d')) }}" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        </label>
    </div>

    <label class="block">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome/Razao social</span>
        <input type="text" name="nome_razao_social" value="{{ old('nome_razao_social', $pessoa?->nome_razao_social) }}" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required maxlength="255">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome fantasia</span>
        <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $pessoa?->nome_fantasia) }}" class="mt-1 block min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" maxlength="255">
    </label>

    <div class="flex justify-end gap-2">
        <a href="{{ route('pessoas.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            Cancelar
        </a>
        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
            Salvar
        </button>
    </div>
</form>
