<div class="flex items-center gap-1 text-sm font-medium">
    @foreach($locales as $code => $label)
        @if($code === $current)
            <span class="px-2 py-0.5 rounded bg-primary text-white select-none">
                {{ $label }}
            </span>
        @else
            <form method="POST" action="{{ route('locale.switch', $code) }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="px-2 py-0.5 rounded text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-150"
                >
                    {{ $label }}
                </button>
            </form>
        @endif
    @endforeach
</div>
