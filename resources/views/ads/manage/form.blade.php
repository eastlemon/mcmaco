<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $mode === 'create' ? __('ads.create') : __('ads.edit') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 text-green-800 border border-green-200 rounded p-3 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ $mode === 'create' ? route('ads.manage.store') : route('ads.manage.update', $ad) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
                @csrf
                @if ($mode === 'edit')
                    @method('PATCH')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('common.title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $ad->title) }}" class="border rounded px-3 py-2 w-full">
                    @error('title')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('ads.description') }}</label>
                    <textarea name="description" rows="6" class="border rounded px-3 py-2 w-full">{{ old('description', $ad->description) }}</textarea>
                    @error('description')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('ads.price') }}</label>
                        <input type="number" name="price" value="{{ old('price', $ad->price ?? 0) }}" class="border rounded px-3 py-2 w-full">
                        @error('price')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('ads.city') }}</label>
                        <input type="text" name="city" value="{{ old('city', $ad->city) }}" class="border rounded px-3 py-2 w-full">
                        @error('city')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('ads.category') }}</label>
                        <select name="category_id" class="border rounded px-3 py-2 w-full">
                            <option value="">{{ __('common.select') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $ad->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('ads.condition') }}</label>
                        <select name="condition" class="border rounded px-3 py-2 w-full">
                            <option value="new" @selected(old('condition', $ad->condition) === 'new')>{{ __('ads.condition_new') }}</option>
                            <option value="used" @selected(old('condition', $ad->condition) === 'used')>{{ __('ads.condition_used') }}</option>
                        </select>
                        @error('condition')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="bg-amber-600 text-white px-4 py-2 rounded">{{ __('common.save') }}</button>
                </div>
            </form>

            @if ($mode === 'edit')
                <div class="bg-white shadow rounded-lg p-6 mt-6" x-data="imageUploader({{ $ad->id }}, {{ $ad->images->count() }})">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">{{ __('ads.photos') }} (до 10)</h3>
                        <span class="text-sm text-gray-500" x-text="`${images.length}/10`"></span>
                    </div>

                    <input type="file" multiple accept="image/*" @change="upload($event)" class="mb-4">
                    <div class="text-sm text-gray-500 mb-4">{{ __('ads.photos_hint') }}</div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach($ad->images as $image)
                            <div class="border rounded p-2 text-center" x-data>
                                <img src="{{ Storage::url(str_replace('.jpg', '_thumb.jpg', $image->path)) }}" class="w-full h-24 object-cover rounded" alt="">
                                <button class="mt-2 text-red-600 text-sm" @click="remove({{ $image->id }})">{{ __('common.delete') }}</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-sm text-gray-500 mt-4">{{ __('ads.photos_after_save') }}</div>
            @endif
        </div>
    </div>

    @if ($mode === 'edit')
        <script>
            function imageUploader(adId, initialCount) {
                return {
                    images: Array.from({ length: initialCount }),
                    async upload(event) {
                        const files = Array.from(event.target.files || []);
                        for (const file of files) {
                            if (this.images.length >= 10) {
                                alert('{{ __(\'ads.photos_max\') }}');
                                break;
                            }
                            const formData = new FormData();
                            formData.append('image', file);

                            const response = await fetch(`/ads/${adId}/images`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: formData,
                            });

                            if (!response.ok) {
                                const data = await response.json().catch(() => ({}));
                                alert(data.message || '{{ __(\'common.error_upload\') }}');
                                continue;
                            }

                            this.images.push(file.name);
                            window.location.reload();
                        }
                    },
                    async remove(imageId) {
                        if (!confirm('{{ __(\'ads.confirm_delete_image\') }}')) {
                            return;
                        }

                        const response = await fetch(`/ads/${adId}/images/${imageId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });

                        if (response.ok) {
                            window.location.reload();
                        }
                    },
                };
            }
        </script>
    @endif
</x-app-layout>
