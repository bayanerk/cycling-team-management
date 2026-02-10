<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                اختر الرايد
            </x-slot>
            
            <x-slot name="description">
                اختر رايد من القائمة لعرض الكوتشات المسجلين عليه فقط
            </x-slot>

            <div class="max-w-md">
                <select 
                    wire:model.live="selectedRideId" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 dark:disabled:bg-transparent dark:disabled:text-gray-400 dark:disabled:placeholder:text-gray-600"
                >
                    <option value="">اختر رايد لعرض الكوتشات المسجلين عليه</option>
                    @foreach($this->getRidesOptions() as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
        </x-filament::section>

        @if($this->selectedRideId)
            <x-filament::section>
                <x-slot name="heading">
                    الكوتشات المسجلين على الرايد
                </x-slot>
                
                <x-slot name="description">
                    قائمة بجميع الكوتشات المسجلين على الرايد المحدد
                </x-slot>

                {{ $this->table }}
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">
                    اختر رايد أولاً
                </x-slot>
                
                <x-slot name="description">
                    يرجى اختيار رايد من القائمة أعلاه لعرض الكوتشات المسجلين عليه
                </x-slot>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
