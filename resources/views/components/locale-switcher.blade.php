<div class="flex items-center gap-0.5 bg-gray-100 dark:bg-gray-800/50 rounded-lg p-0.5" role="group" aria-label="Language switcher">
    @foreach($locales as $code => $label)
        @if($code === $current)
            <span class="px-3 py-1.5 text-sm font-medium rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm select-none transition-all">
                {{ $label }}
            </span>
        @else
            <form method="POST" action="{{ route('locale.switch', $code) }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="px-3 py-1.5 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/50 transition-all duration-200"
                >
                    {{ $label }}
                </button>
            </form>
        @endif
    @endforeach
</div>
