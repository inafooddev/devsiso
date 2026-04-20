<div>
    <x-slot name="title">Edit Category</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="update">
                <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Formulir Edit Category</h3>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Perbarui
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                            <p class="font-bold">Oops! Ada beberapa kesalahan:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category ID -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category ID <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="category_id" id="category_id" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Category Name -->
                        <div>
                            <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Category <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="category_name" id="category_name" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('category_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
