<div class="sticky top-17 left-0 right-0">
    @if ($ads->isEmpty() && $error)
        <div class="py-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p>{{ $error }}</p>
            </div>
        </div>
    @elseif ($ads->isEmpty())
        <div class="py-4 text-center">
            <p class="text-lg text-gray-500">{{ __('ad_section.noAds') ?: 'No ads available' }}</p>
        </div>
    @else
        <section class="py-4 ">

            <!-- نسخه دسکتاپ -->
            <div class="hidden md:block">
                <div class="space-y-6">
                    @foreach ($ads as $item)
                        <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                           class="block group"
                           wire:click="handleAdClick({{ $item->id }})">
                            <div class="card glass rounded-xl border-none shadow-lg transition-all duration-300 group-hover:shadow-xl group-hover:scale-[1.01] relative overflow-hidden">
                                <figure class="relative aspect-[16/9] w-full overflow-hidden">

                                      <img class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                             src="{{Storage::url($item->cover)}}"
                                             alt="{{$item->title}}"
                                             loading="lazy"/>
                                    {{-- @if(app()->getLocale()=='fa')

                                    @elseif(app()->getLocale()=='en')
                                        <img class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                             src="{{Storage::url($item->cover_en??$item->cover_fa)}}"
                                             alt="{{$item->title}}"
                                             loading="lazy"/>
                                    @elseif(app()->getLocale()=='ar')
                                        <img class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                             src="{{asset(Storage::url($item->cover_ar??$item->cover_fa))}}"
                                             alt="{{$item->title}}"
                                             loading="lazy"/>
                                    @endif --}}
                                </figure>
                                <div class="card-body absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent backdrop-blur-sm p-6 text-white transition-all duration-300 group-hover:from-black/80">
                                    <h2 class="card-title text-xl font-bold mb-2">{{$item->title}}</h2>
                                    @if($item->info)
                                        <p class="line-clamp-2 text-sm/relaxed opacity-90">{{$item->info}}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- نسخه موبایل: Carousel -->
            <div class="block md:hidden">
                <div class="carousel carousel-center bg-neutral rounded-box max-w-full my-3 space-x-4 p-4" dir="ltr">
                    @foreach ($ads as $item)
                        <div class="carousel-item snap-center">
                            <div class="card glass rounded-xl border-none w-80 shadow-xl relative">
                                <figure>
                                     <img class="border-none rounded-xl"
                                             src="{{Storage::url($item->cover)}}"
                                             alt="{{$item->title}}"/>
                                    {{-- @if(app()->getLocale()=='fa')

                                    @elseif(app()->getLocale()=='en')
                                        <img class="border-none rounded-xl"
                                             src="{{asset(Storage::url($item->cover_en??$item->cover_fa))}}"
                                             alt="{{$item->title}}"/>
                                    @elseif(app()->getLocale()=='ar')
                                        <img class="border-none rounded-xl"
                                             src="{{asset(Storage::url($item->cover_ar??$item->cover_fa))}}"
                                             alt="{{$item->title}}"/>
                                    @endif --}}
                                </figure>
                                <a href="{{$item->link}}" wire:click="handleAdClick({{ $item->id }})">
                                    <div class="card-body glass rounded-xl absolute bottom-0 left-0 right-0 text-white bg-gradient-to-t from-black/70 to-transparent backdrop-blur-sm p-4" dir="auto">
                                        <h2 class="card-title text-base font-bold">{{$item->title}}</h2>
                                        @if($item->info)
                                            <p class="text-sm line-clamp-2">{{$item->info}}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </section>
    @endif
</div>
