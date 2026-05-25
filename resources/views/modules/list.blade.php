<x-layouts::app :title="$title">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h1>
                @isset($description)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>
                @endisset
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            @foreach($columns as $column)
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                @foreach($columns as $column)
                                    @php
                                        $value = data_get($record, $column['key']);

                                        if ($value instanceof \BackedEnum) {
                                            $value = method_exists($value, 'label') ? $value->label() : $value->value;
                                        }

                                        if ($value instanceof \Carbon\CarbonInterface) {
                                            $value = $value->format($column['format'] ?? 'd/m/Y');
                                        }

                                        if (($column['type'] ?? null) === 'money' && is_numeric($value)) {
                                            $value = 'R$ '.number_format((float) $value, 2, ',', '.');
                                        }
                                    @endphp
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ filled($value) ? $value : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $records->withQueryString()->links() }}
        </div>
    </div>
</x-layouts::app>
