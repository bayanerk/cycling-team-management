<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Select a ride
            </x-slot>

            <x-slot name="description">
                Choose a ride from the list to show only coaches registered on that ride.
            </x-slot>

            <div class="max-w-md">
                <select
                    wire:model.live="selectedRideId"
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 dark:disabled:bg-transparent dark:disabled:text-gray-400 dark:disabled:placeholder:text-gray-600"
                >
                    <option value="">Select a ride to view registered coaches</option>
                    @foreach($this->getRidesOptions() as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
        </x-filament::section>

        @if($this->selectedRideId)
            <x-filament::section>
                <x-slot name="heading">
                    Coaches on this ride
                </x-slot>

                <x-slot name="description">
                    All coaches registered for the selected ride.
                </x-slot>

                {{ $this->table }}
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">
                    Select a ride first
                </x-slot>

                <x-slot name="description">
                    Please choose a ride from the list above to view registered coaches.
                </x-slot>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
