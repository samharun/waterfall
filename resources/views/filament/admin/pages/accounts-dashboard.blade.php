<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach ($this->stats() as $label => $value)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-500">{{ $label }}</div>
                <div class="mt-2 text-2xl font-semibold">BDT {{ number_format((float) $value, 2) }}</div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
