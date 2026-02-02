<?php
// Carousel Component
use Livewire\Component;

state(['ads' => fn() => \App\Models\Advertisement::get()]);
?>

<div class="w-full">
    <div class="carousel carousel-center rounded-2xl w-full my-3 space-x-4 p-4 bg-gradient-to-r from-slate-50 to-slate-100">
        @forelse ($ads as $item)
            <div class="carousel-item snap-center w-80 flex-shrink-0">
                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer" class="group w-full">
                    <div class="card backdrop-blur-2xl rounded-2xl border-none shadow-xl overflow-hidden h-96 transition-transform duration-300 hover:shadow-2xl hover:scale-105">
                        <img
                            src="{{ Storage::url($item->cover) }}"
                            alt="{{ $item->title }}"
                            loading="lazy"
                            class="w-full h-full object-cover rounded-2xl"
                        />

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $item->title }}</h3>
                            @if($item->info)
                                <p class="text-sm text-gray-200 line-clamp-2">{{ $item->info }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="w-full flex items-center justify-center h-40 text-gray-500">
                <span>{{ __('messages.no_ads') }}</span>
            </div>
        @endforelse
    </div>
</div>
