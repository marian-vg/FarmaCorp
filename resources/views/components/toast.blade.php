<div 
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        timeout: null,
        showToast(event) {
            this.message = event.detail?.message || event.detail?.[0] || 'Notification';
            this.type = event.detail?.type || 'success';
            this.show = true;
            
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => { this.show = false }, 3500);
        }
    }"
    x-init="
        @if(session()->has('notify'))
            setTimeout(() => {
                showToast({ detail: { message: '{{ session('notify')['message'] ?? 'Notification' }}', type: '{{ session('notify')['type'] ?? 'success' }}' } })
            }, 100);
        @endif
    "
    @notify.window="showToast($event)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-4 inset-x-0 mx-auto z-50 flex items-center w-full max-w-xs p-4 space-x-3 text-gray-700 rounded-lg shadow-lg dark:text-zinc-200 ring-1"
    :class="{
        'bg-emerald-50 ring-emerald-500/20 dark:bg-emerald-900/40 dark:ring-emerald-500/30': type === 'success',
        'bg-red-50 ring-red-500/20 dark:bg-red-900/40 dark:ring-red-500/30': type === 'error'
    }"
    style="display: none;"
>
    <div x-show="type === 'success'" class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-emerald-500 bg-emerald-100/50 rounded-lg dark:bg-emerald-800/50 dark:text-emerald-400">
        <flux:icon.check-circle class="w-5 h-5" />
    </div>

    <div x-show="type === 'error'" class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-red-500 bg-red-100/50 rounded-lg dark:bg-red-800/50 dark:text-red-400">
        <flux:icon.x-circle class="w-5 h-5" />
    </div>

    <div class="ml-3 text-sm font-medium" x-text="message"></div>

</div>