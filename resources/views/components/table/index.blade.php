<div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700" {{ $attributes->except('class') }}>
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 dark:divide-zinc-700']) }}>
        {{ $slot }}
    </table>
</div>
