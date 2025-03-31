<x-filament::widget class="col-span-full">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header Section -->
        <div class="border-b border-gray-200/50 p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <x-filament::icon name="heroicon-o-newspaper" class="h-8 w-8 text-indigo-500" />
                        <x-filament::icon name="heroicon-o-rss" class="h-8 w-8 text-orange-500" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Latest News</h2>
                        <p class="text-sm text-gray-600">{{ date('l, F j') }}</p>
                    </div>
                </div>
                <x-filament::button wire:click="refreshNews" size="sm" class="bg-primary-600/90 hover:bg-primary-700">
                    <x-filament::icon name="heroicon-o-refresh" class="h-4 w-4 mr-1" />
                    Refresh
                </x-filament::button>
            </div>
        </div>
        <div class="bg-[url('{{ $this->getNewsBackground() }}')] bg-cover bg-center rounded-2xl shadow-xl overflow-hidden">
            <div class="backdrop-blur-sm bg-gradient-to-br from-white/95 to-white/90 h-full">
                {{-- <div class="border-b border-gray-200/50 p-6 backdrop-blur-md bg-white/30">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::icon name="heroicon-o-newspaper" class="h-8 w-8 text-primary-600" />
                            <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-800">News Dashboard</h2>
                        </div>
                        <x-filament::button
                            wire:click="fetchNewsData"
                            size="sm"
                            class="bg-primary-600/90 hover:bg-primary-700 shadow-lg hover:shadow-primary-500/20 transition-all duration-300">
                            <x-filament::icon name="heroicon-o-refresh" class="h-4 w-4 mr-1 animate-spin" wire:loading />
                            <x-filament::icon name="heroicon-o-refresh" class="h-4 w-4 mr-1" wire:loading.remove />
                            Refresh
                        </x-filament::button>
                    </div>
                </div> --}}

                <div class="p-6">
                    <x-filament::tabs>
                        <x-filament::tabs.item wire:click="setActiveTab('breaking')" :active="$activeTab === 'breaking'">
                            Breaking News
                        </x-filament::tabs.item>
                        <x-filament::tabs.item wire:click="setActiveTab('tech')" :active="$activeTab === 'tech'">
                            Technology
                        </x-filament::tabs.item>
                        <x-filament::tabs.item wire:click="setActiveTab('science')" :active="$activeTab === 'science'">
                            Science & Health
                        </x-filament::tabs.item>
                    </x-filament::tabs>

                    <div class="mt-6">
                        @if($activeTab === 'breaking')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach(array_slice($newsData['business'], 0, 4) as $news)
                                    <div class="group bg-gradient-to-r from-red-50 to-orange-50 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300">
                                        @if($news['urlToImage'])
                                            <div class="relative h-36 overflow-hidden bg-gray-100">
                                                <img src="{{ $news['urlToImage'] }}"
                                                     class="w-full h-full object-cover animate-pulse transition-all duration-500 group-hover:scale-105"
                                                     onload="this.classList.remove('animate-pulse')"
                                                     onerror="this.parentElement.style.display='none'"
                                                     loading="lazy"
                                                     alt="{{ $news['title'] }}">
                                                <div class="absolute inset-0 bg-gradient-to-t from-red-900/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                                <div class="absolute bottom-0 left-0 right-0 p-3 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                                    <p class="text-xs line-clamp-2">{{ $news['description'] }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="p-4">
                                            <a href="{{ $news['url'] }}" target="_blank" class="block">
                                                <h4 class="text-sm font-medium text-gray-900 group-hover:text-red-600 transition-colors">{{ $news['title'] }}</h4>
                                                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                                    <div class="relative w-4 h-4 overflow-hidden rounded-full bg-gray-100">
                                                        <img src="https://www.google.com/s2/favicons?domain={{ parse_url($news['url'], PHP_URL_HOST) }}"
                                                             class="w-full h-full object-cover"
                                                             loading="lazy"
                                                             alt="{{ $news['source']['name'] }}">
                                                    </div>
                                                    <span class="group-hover:text-red-500 transition-colors">{{ $news['source']['name'] }}</span>
                                                    <span>•</span>
                                                    <span>{{ \Carbon\Carbon::parse($news['publishedAt'])->diffForHumans() }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($activeTab === 'tech')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach(array_slice($newsData['technology'], 0, 4) as $news)
                                    <div class="group bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300">
                                        @if($news['urlToImage'])
                                            <div class="relative h-32 overflow-hidden bg-gray-100">
                                                <img src="{{ $news['urlToImage'] }}"
                                                     class="w-full h-full object-cover animate-pulse transition-opacity duration-300"
                                                     onload="this.classList.remove('animate-pulse')"
                                                     onerror="this.parentElement.style.display='none'"
                                                     loading="lazy"
                                                     alt="{{ $news['title'] }}">
                                                <div class="absolute inset-0 bg-gradient-to-t from-purple-500/30 to-transparent"></div>
                                            </div>
                                        @endif
                                        <div class="p-3">
                                            <a href="{{ $news['url'] }}" target="_blank" class="block">
                                                <h4 class="text-sm font-medium text-gray-900 group-hover:text-purple-700 transition-colors">{{ $news['title'] }}</h4>
                                                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                                    <img src="https://www.google.com/s2/favicons?domain={{ parse_url($news['url'], PHP_URL_HOST) }}"
                                                         class="w-4 h-4"
                                                         alt="{{ $news['source']['name'] }}">
                                                    <span>{{ $news['source']['name'] }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($activeTab === 'science')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach(array_slice($newsData['science'], 0, 4) as $news)
                                    <div class="group bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg overflow-hidden hover:shadow-md transition-all duration-300">
                                        @if($news['urlToImage'])
                                            <div class="relative h-32 overflow-hidden bg-gray-100">
                                                <img src="{{ $news['urlToImage'] }}"
                                                     class="w-full h-full object-cover animate-pulse transition-opacity duration-300"
                                                     onload="this.classList.remove('animate-pulse')"
                                                     onerror="this.parentElement.style.display='none'"
                                                     loading="lazy"
                                                     alt="{{ $news['title'] }}">
                                                <div class="absolute inset-0 bg-gradient-to-t from-blue-500/30 to-transparent"></div>
                                            </div>
                                        @endif
                                        <div class="p-3">
                                            <a href="{{ $news['url'] }}" target="_blank" class="block">
                                                <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-700 transition-colors">{{ $news['title'] }}</h4>
                                                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                                    <img src="https://www.google.com/s2/favicons?domain={{ parse_url($news['url'], PHP_URL_HOST) }}"
                                                         class="w-4 h-4"
                                                         alt="{{ $news['source']['name'] }}">
                                                    <span>{{ $news['source']['name'] }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::widget>
