<x-filament::widget class="col-span-full">
    <div
        style="background-image: url('{{ $this->getWeatherIconName() }}'), url('/images/clouds.png');"
        class="bg-cover bg-center bg-no-repeat rounded-xl shadow-lg overflow-hidden relative h-full"
    >
        <!-- Gradient overlay for better text contrast -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/40"></div>
        <div class="backdrop-blur-sm bg-white/80 h-full relative p-2">
            <!-- Header Section -->
            <div class="border-b border-gray-200/50 p-2 backdrop-blur-md bg-white/50 relative z-10">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-cloud-sun text-xl text-blue-500"></i>
                            <i class="fas fa-newspaper text-xl text-indigo-500"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $weatherData['name'] ?? 'Weather' }}</h2>
                            <p class="text-sm text-gray-600">{{ date('l, F j') }}</p>
                        </div>
                    </div>
                    <x-filament::button wire:click="fetchWeatherData" size="sm" class="bg-primary-600/90 hover:bg-primary-700 text-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Update
                    </x-filament::button>
                </div>
            </div>

            @if($weatherData && isset($weatherData['main']))
                <div class="p-2">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-2">
                        <!-- Current Weather -->
                        <div class="bg-gradient-to-br from-blue-500/10 to-purple-500/10 backdrop-blur-sm rounded-lg p-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-5xl font-light {{ $this->getTemperatureColor() }}">
                                        {{ round($weatherData['main']['temp']) }}°
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ ucfirst($weatherData['weather'][0]['description']) }}
                                        @php $trend = $this->getTemperatureTrend(); @endphp
                                        <span class="{{ $trend['color'] }}">
                                            <i class="fas fa-{{ $trend['icon'] }} ml-1"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full p-1">
                                    <img src="https://basmilius.github.io/weather-icons/production/fill/all/{{ $this->getWeatherIconName() }}.svg"
                                         class="w-12 h-12 animate-pulse"
                                         onload="this.classList.remove('animate-pulse')"
                                         onerror="this.src='https://openweathermap.org/img/wn/{{ $weatherData['weather'][0]['icon'] }}@2x.png'">
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-1 mt-2">
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-1 text-center">
                                    <i class="fas fa-temperature-high text-blue-500 text-base"></i>
                                    <p class="text-xs text-gray-600">Feels Like</p>
                                    <p class="text-sm font-medium text-gray-800">{{ round($weatherData['main']['feels_like']) }}°</p>
                                    <p class="text-xs text-gray-500">{{ $this->getComfortLevel() }}</p>
                                </div>
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-1 text-center">
                                    <i class="fas fa-tint text-blue-500 text-base"></i>
                                    <p class="text-xs text-gray-600">Humidity</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $weatherData['main']['humidity'] }}%</p>
                                    <p class="text-xs text-gray-500">{{ $this->getHumidityLevel() }}</p>
                                </div>
                                @php $windInfo = $this->getWindSpeedCategory(); @endphp
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-1 text-center">
                                    <i class="fas fa-wind text-{{ $windInfo['color'] }}-500 text-base"></i>
                                    <p class="text-xs text-gray-600">Wind</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $windInfo['speed'] }}m/s</p>
                                    <p class="text-xs text-gray-500">{{ $this->getWindDirection($weatherData['wind']['deg']) }}</p>
                                </div>
                                @php $uvIndex = $this->getUVIndex(); @endphp
                                <div class="bg-white/60 backdrop-blur-sm rounded-lg p-1 text-center">
                                    <i class="fas fa-sun text-{{ $uvIndex['color'] }}-500 text-base"></i>
                                    <p class="text-xs text-gray-600">UV Index</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $uvIndex['value'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $uvIndex['level'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Forecast Section -->
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">5-Day Forecast</h3>
                            @if(isset($forecast['list']))
                                <div class="space-y-0.5">
                                    @php
                                        $dailyForecast = $this->getDailyForecast();
                                    @endphp
                                    @foreach($dailyForecast as $day)
                                        <div class="flex items-center justify-between bg-white/60 backdrop-blur-sm rounded-lg p-1 {{ $loop->first ? 'border-l-2 border-blue-500' : '' }}">
                                            <div class="flex items-center gap-1">
                                                <img src="https://basmilius.github.io/weather-icons/production/fill/all/{{ $day['icon'] }}.svg"
                                                     class="w-6 h-6">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-800">{{ $day['date'] }}</p>
                                                    <p class="text-xs text-gray-600">{{ ucfirst($day['description']) }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-800">
                                                    {{ $day['temp_max'] }}° / {{ $day['temp_min'] }}°
                                                </p>
                                                <p class="text-xs text-gray-600">
                                                    <i class="fas fa-tint text-blue-400"></i> {{ $day['precipitation'] }}%
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Moved Section to Bottom -->
                    <div class="mt-2 grid grid-cols-1 xl:grid-cols-2 gap-2">
                        @if($alert = $this->getWeatherAlert())
                            <div class="bg-white/60 backdrop-blur-sm rounded-lg p-2">
                                <p class="text-sm text-{{ $alert['color'] }}-600">
                                    <i class="fas fa-exclamation-circle"></i> {{ $alert['message'] }}
                                </p>
                            </div>
                        @endif

                        @php $pattern = $this->getWeatherPattern(); @endphp
                        <div class="bg-white/60 backdrop-blur-sm rounded-lg p-2">
                            <p class="text-xs text-gray-600">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                {{ $pattern['pattern'] }}: {{ ucfirst($pattern['dominant_condition']) }} conditions expected
                                @if($pattern['precipitation_likelihood'] > 30)
                                    with {{ $pattern['precipitation_likelihood'] }}% chance of precipitation
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex justify-center items-center h-full">
                    <div class="animate-spin h-8 w-8 border-2 border-gray-200 border-t-blue-500 rounded-full"></div>
                </div>
            @endif
        </div>
    </div>
</x-filament::widget>