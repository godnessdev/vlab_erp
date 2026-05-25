<x-layouts::app :title="$pessoa->nome_razao_social">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('pessoas.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300">Voltar para pessoas</a>
                <h1 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $pessoa->nome_razao_social }}</h1>
                @if($pessoa->nome_fantasia)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $pessoa->nome_fantasia }}</p>
                @endif
            </div>

            <div class="flex gap-2">
                <a href="{{ route('pessoas.edit', $pessoa) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Editar</a>
                <form method="POST" action="{{ $pessoa->status->isAtivo() ? route('pessoas.inativar', $pessoa) : route('pessoas.ativar', $pessoa) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        {{ $pessoa->status->isAtivo() ? 'Inativar' : 'Ativar' }}
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Dados principais</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Tipo</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $pessoa->tipo->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $pessoa->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nascimento/constituicao</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $pessoa->data_nascimento_constituicao?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Documentos</h2>
                <div class="mt-4 space-y-3">
                    @forelse($pessoa->documentos as $documento)
                        <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $documento->tipo->label() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">{{ $documento->valor_formatado }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum documento cadastrado.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Contatos</h2>
                <div class="mt-4 space-y-3">
                    @forelse($pessoa->contatos as $contato)
                        <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $contato->tipo->label() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">{{ $contato->valor }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum contato cadastrado.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
