<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;

class WeatherWidget extends Widget
{
    protected static string $view = 'filament.widgets.weather-widget';

    public $weatherData;
    public $forecast;

    public function mount()
    {
        $this->fetchWeatherData();
    }

    public function fetchWeatherData()
    {
        try {
            $apiKey = config('services.openweathermap.key');

            // Get current weather for Cairo
            $currentWeather = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => 'Cairo,EG',
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            // Get 5-day forecast for Cairo
            $forecastResponse = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'q' => 'Cairo,EG',
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            if ($currentWeather->successful() && $forecastResponse->successful()) {
                $this->weatherData = $currentWeather->json();
                $this->forecast = $forecastResponse->json();
            } else {
                $this->weatherData = ['error' => 'Failed to fetch weather data'];
            }
        } catch (\Exception $e) {
            $this->weatherData = ['error' => 'Error connecting to weather service'];
        }
    }

    protected static function shouldPoll(): bool
    {
        return true;
    }

    public static function getPollingInterval(): ?string
    {
        return '5m';
    }

    protected function getWeatherBackground(): string
    {
        $weatherCode = $this->weatherData['weather'][0]['id'] ?? 800;
        $timeOfDay = now()->hour > 18 || now()->hour < 6 ? 'night' : 'day';

        $backgrounds = [
            '2' => "https://source.unsplash.com/1600x900/?thunderstorm,{$timeOfDay}",
            '3' => "https://source.unsplash.com/1600x900/?drizzle,rain,{$timeOfDay}",
            '5' => "https://source.unsplash.com/1600x900/?rain,storm,{$timeOfDay}",
            '6' => "https://source.unsplash.com/1600x900/?snow,winter,{$timeOfDay}",
            '7' => "https://source.unsplash.com/1600x900/?fog,mist,{$timeOfDay}",
            '800' => "https://source.unsplash.com/1600x900/?clear,sky,{$timeOfDay}",
            '80' => "https://source.unsplash.com/1600x900/?clouds,sky,{$timeOfDay}",
        ];

        $weatherGroup = (string) floor($weatherCode / 100);
        $url = $backgrounds[$weatherGroup] ?? $backgrounds['800'];

        // Debugging
        \Log::info('Weather background URL:', ['url' => $url]);

        return $url;
    }

    protected function getWeatherIcon()
    {
        $iconName = $this->getWeatherIconName();
        return "https://basmilius.github.io/weather-icons/production/fill/all/{$iconName}.svg";
    }

    protected function getWeatherIconName(?string $condition = null): string
    {
        $condition = strtolower($condition ?? $this->weatherData['weather'][0]['main'] ?? 'clear');
        $description = strtolower($this->weatherData['weather'][0]['description'] ?? '');
        $isDay = (int)date('H') >= 6 && (int)date('H') < 18;

        $icons = [
            'clear' => $isDay ? 'clear-day' : 'clear-night',
            'clouds' => [
                'few clouds' => $isDay ? 'partly-cloudy-day' : 'partly-cloudy-night',
                'scattered clouds' => $isDay ? 'partly-cloudy-day' : 'partly-cloudy-night',
                'broken clouds' => 'cloudy',
                'overcast clouds' => 'overcast',
                'default' => 'cloudy' // Removed night variant as it's not available
            ],
            'rain' => [
                'light rain' => 'drizzle',
                'moderate rain' => 'rain',
                'heavy intensity rain' => 'extreme-rain',
                'very heavy rain' => 'extreme-rain',
                'extreme rain' => 'extreme-day-rain',
                'freezing rain' => 'sleet',
                'default' => 'rain'
            ],
            'drizzle' => 'drizzle',
            'thunderstorm' => [
                'thunderstorm with light rain' => 'thunderstorms-rain',
                'thunderstorm with rain' => 'thunderstorms-rain',
                'thunderstorm with heavy rain' => 'thunderstorms-extreme-rain',
                'default' => 'thunderstorms'
            ],
            'snow' => [
                'light snow' => 'partly-cloudy-day-snow',
                'heavy snow' => 'extreme-day-snow',
                'default' => 'snow'
            ],
            'mist' => 'mist',
            'fog' => 'fog',
            'haze' => 'haze',
            'dust' => 'dust',
            'smoke' => 'smoke'
        ];

        if (isset($icons[$condition])) {
            if (is_array($icons[$condition])) {
                return $icons[$condition][$description] ?? $icons[$condition]['default'];
            }
            return $icons[$condition];
        }

        return $isDay ? 'clear-day' : 'clear-night';
    }

    protected function getWeatherDescription(): string
    {
        if (!isset($this->weatherData['weather'][0])) {
            return 'Weather data unavailable';
        }

        $temp = round($this->weatherData['main']['temp']);
        $feels = round($this->weatherData['main']['feels_like']);
        $description = ucfirst($this->weatherData['weather'][0]['description']);

        return "{$description}, {$temp}°C (Feels like {$feels}°C)";
    }

    protected function getWindDirection($degrees)
    {
        $directions = [
            'North', 'North-Northeast', 'Northeast', 'East-Northeast',
            'East', 'East-Southeast', 'Southeast', 'South-Southeast',
            'South', 'South-Southwest', 'Southwest', 'West-Southwest',
            'West', 'West-Northwest', 'Northwest', 'North-Northwest'
        ];

        $index = round($degrees / 22.5) % 16;
        return $directions[$index];
    }

    protected function getUVIndex(): array
    {
        $hour = (int)date('H');
        $uvLevel = match(true) {
            $hour >= 10 && $hour <= 16 => rand(6, 10),
            $hour >= 8 && $hour <= 18 => rand(3, 6),
            default => rand(0, 2)
        };

        return [
            'value' => $uvLevel,
            'level' => match(true) {
                $uvLevel >= 8 => 'Very High',
                $uvLevel >= 6 => 'High',
                $uvLevel >= 3 => 'Moderate',
                default => 'Low'
            },
            'color' => match(true) {
                $uvLevel >= 8 => 'red',
                $uvLevel >= 6 => 'orange',
                $uvLevel >= 3 => 'yellow',
                default => 'green'
            }
        ];
    }

    protected function getWindSpeedCategory(): array
    {
        $speed = $this->weatherData['wind']['speed'] ?? 0;

        return [
            'speed' => round($speed),
            'category' => match(true) {
                $speed >= 32.7 => 'Hurricane Force',
                $speed >= 28.5 => 'Violent Storm',
                $speed >= 24.5 => 'Storm',
                $speed >= 20.8 => 'Strong Gale',
                $speed >= 17.2 => 'Gale',
                $speed >= 13.9 => 'Near Gale',
                $speed >= 10.8 => 'Strong Breeze',
                $speed >= 8.0 => 'Fresh Breeze',
                $speed >= 5.5 => 'Moderate Breeze',
                $speed >= 3.4 => 'Gentle Breeze',
                $speed >= 1.6 => 'Light Breeze',
                $speed >= 0.3 => 'Light Air',
                default => 'Calm'
            },
            'color' => match(true) {
                $speed >= 20.8 => 'red',
                $speed >= 13.9 => 'orange',
                $speed >= 8.0 => 'yellow',
                default => 'green'
            }
        ];
    }

    protected function getDayPeriod(): string
    {
        $hour = (int)date('H');
        return match(true) {
            $hour >= 5 && $hour < 12 => 'Morning',
            $hour >= 12 && $hour < 17 => 'Afternoon',
            $hour >= 17 && $hour < 20 => 'Evening',
            default => 'Night'
        };
    }

    protected function getTemperatureColor(): string
    {
        $temp = round($this->weatherData['main']['temp'] ?? 20);

        return match(true) {
            $temp >= 35 => 'text-red-600',
            $temp >= 30 => 'text-orange-500',
            $temp >= 25 => 'text-yellow-500',
            $temp >= 20 => 'text-green-500',
            $temp >= 15 => 'text-cyan-500',
            $temp >= 10 => 'text-blue-500',
            default => 'text-indigo-500'
        };
    }

    protected function getCardStyle(): string
    {
        $period = $this->getDayPeriod();
        $condition = strtolower($this->weatherData['weather'][0]['main'] ?? 'clear');

        $baseStyles = match($period) {
            'Morning' => 'from-amber-400/90 to-sky-600/90',
            'Afternoon' => 'from-sky-400/90 to-blue-600/90',
            'Evening' => 'from-orange-400/90 to-purple-600/90',
            'Night' => 'from-blue-900/90 to-indigo-900/90'
        };

        $weatherEffect = match($condition) {
            'clouds' => 'animate-pulse backdrop-blur-sm',
            'rain' => 'animate-rain backdrop-blur-md',
            'snow' => 'animate-snow backdrop-blur-sm',
            'thunderstorm' => 'animate-flash backdrop-blur-lg',
            'mist', 'fog', 'haze' => 'backdrop-blur-xl',
            default => 'backdrop-blur-none'
        };

        return "{$baseStyles} {$weatherEffect} transition-all duration-500";
    }

    protected function getWeatherEffect(): string
        {
            $condition = strtolower($this->weatherData['weather'][0]['main'] ?? 'clear');

            return match($condition) {
                'clouds' => 'clouds',
                'rain', 'drizzle' => 'rain',
                'snow' => 'snow',
                'thunderstorm' => 'thunder',
                default => ''
            };
        }

    protected function getWeatherColors(): array
    {
        $condition = strtolower($this->weatherData['weather'][0]['main'] ?? 'clear');
        $temp = round($this->weatherData['main']['temp'] ?? 20);

        return [
            'primary' => match(true) {
                $temp >= 30 => 'text-red-500',
                $temp >= 20 => 'text-orange-400',
                $temp >= 10 => 'text-yellow-400',
                default => 'text-blue-400'
            },
            'secondary' => match($condition) {
                'clouds' => 'text-gray-400',
                'rain' => 'text-blue-400',
                'snow' => 'text-slate-200',
                'thunderstorm' => 'text-purple-400',
                'mist', 'fog', 'haze' => 'text-gray-300',
                default => 'text-amber-400'
            },
            'accent' => match($condition) {
                'clouds' => 'bg-gray-600/20',
                'rain' => 'bg-blue-600/20',
                'snow' => 'bg-slate-400/20',
                'thunderstorm' => 'bg-purple-600/20',
                'mist', 'fog', 'haze' => 'bg-gray-500/20',
                default => 'bg-amber-600/20'
            }
        ];
    }

    protected function getWeatherAlert(): ?array
    {
        $temp = round($this->weatherData['main']['temp'] ?? 20);
        $humidity = $this->weatherData['main']['humidity'] ?? 50;
        $windSpeed = $this->weatherData['wind']['speed'] ?? 0;

        if ($temp >= 35) {
            return [
                'type' => 'warning',
                'message' => 'Extreme heat warning',
                'icon' => 'temperature-high',
                'color' => 'red'
            ];
        }

        if ($humidity >= 80) {
            return [
                'type' => 'info',
                'message' => 'High humidity levels',
                'icon' => 'droplet',
                'color' => 'blue'
            ];
        }

        if ($windSpeed >= 20) {
            return [
                'type' => 'warning',
                'message' => 'Strong wind alert',
                'icon' => 'wind',
                'color' => 'yellow'
            ];
        }

        return null;
    }

    protected function getTemperatureTrend(): array
    {
        if (!isset($this->weatherData['main']['temp'])) {
            return [
                'trend' => 'stable',
                'icon' => 'minus',
                'color' => 'text-gray-500'
            ];
        }

        $currentTemp = $this->weatherData['main']['temp'];
        $feelsLike = $this->weatherData['main']['feels_like'];

        // Get the first forecast temperature
        $nextTemp = $this->forecast['list'][0]['main']['temp'] ?? $currentTemp;

        // Calculate temperature difference
        $difference = $nextTemp - $currentTemp;

        return [
            'trend' => match(true) {
                $difference >= 3 => 'rising_fast',
                $difference >= 1 => 'rising',
                $difference <= -3 => 'falling_fast',
                $difference <= -1 => 'falling',
                default => 'stable'
            },
            'icon' => match(true) {
                $difference >= 3 => 'arrow-trend-up',
                $difference >= 1 => 'arrow-up',
                $difference <= -3 => 'arrow-trend-down',
                $difference <= -1 => 'arrow-down',
                default => 'minus'
            },
            'color' => match(true) {
                $difference >= 3 => 'text-red-600',
                $difference >= 1 => 'text-orange-500',
                $difference <= -3 => 'text-blue-600',
                $difference <= -1 => 'text-cyan-500',
                default => 'text-gray-500'
            }
        ];
    }

    protected function getHumidityLevel(): string
        {
            $humidity = $this->weatherData['main']['humidity'] ?? 50;
            $temp = $this->weatherData['main']['temp'] ?? 20;

            return match(true) {
                $humidity >= 80 && $temp >= 25 => 'Very Humid',
                $humidity >= 70 && $temp >= 25 => 'Humid',
                $humidity >= 60 => 'Moderate',
                $humidity >= 40 => 'Comfortable',
                $humidity >= 30 => 'Slightly Dry',
                default => 'Very Dry'
            };
        }

    protected function getComfortLevel(): string
        {
            $temp = $this->weatherData['main']['temp'] ?? 20;
            $humidity = $this->weatherData['main']['humidity'] ?? 50;
            $feelsLike = $this->weatherData['main']['feels_like'] ?? $temp;

            return match(true) {
                $feelsLike >= 35 => 'Very Hot',
                $feelsLike >= 30 && $humidity >= 70 => 'Uncomfortable',
                $feelsLike >= 30 => 'Hot',
                $feelsLike >= 25 && $humidity >= 70 => 'Warm & Humid',
                $feelsLike >= 25 => 'Warm',
                $feelsLike >= 20 => 'Pleasant',
                $feelsLike >= 15 => 'Cool',
                $feelsLike >= 10 => 'Chilly',
                $feelsLike >= 5 => 'Cold',
                default => 'Very Cold'
            };
        }

    protected function getWeatherPattern(): array
        {
            if (!isset($this->forecast['list'])) {
                return [
                    'pattern' => 'Unknown',
                    'dominant_condition' => 'Unknown',
                    'precipitation_likelihood' => 0,
                    'stability' => 'unknown'
                ];
            }

            $conditions = [];
            $precipitationChances = [];

            // Analyze next 24 hours (8 3-hour intervals)
            foreach (array_slice($this->forecast['list'], 0, 8) as $forecast) {
                $conditions[] = $forecast['weather'][0]['main'];
                $precipitationChances[] = ($forecast['pop'] ?? 0) * 100;
            }

            $conditionCounts = array_count_values($conditions);
            $dominantCondition = array_search(max($conditionCounts), $conditionCounts);
            $avgPrecipChance = array_sum($precipitationChances) / count($precipitationChances);

            return [
                'pattern' => match(true) {
                    count($conditionCounts) === 1 => 'Steady',
                    in_array('Thunderstorm', $conditions) => 'Stormy',
                    in_array('Rain', $conditions) && in_array('Clear', $conditions) => 'Scattered Showers',
                    in_array('Clear', $conditions) && in_array('Clouds', $conditions) => 'Variable',
                    default => 'Mixed'
                },
                'dominant_condition' => $dominantCondition,
                'precipitation_likelihood' => round($avgPrecipChance),
                'stability' => count($conditionCounts) === 1 ? 'stable' : 'changing'
            ];
        }

    protected function getDailyForecast(): array
        {
            if (!isset($this->forecast['list'])) {
                return [];
            }

            $dailyForecasts = [];
            $processedDates = [];

            foreach ($this->forecast['list'] as $forecast) {
                $date = date('Y-m-d', strtotime($forecast['dt_txt']));

                // Skip if we already have this date
                if (in_array($date, $processedDates)) {
                    continue;
                }

                $processedDates[] = $date;

                $dailyForecasts[] = [
                    'date' => date('D, M j', strtotime($forecast['dt_txt'])),
                    'temp_max' => round($forecast['main']['temp_max']),
                    'temp_min' => round($forecast['main']['temp_min']),
                    'description' => $forecast['weather'][0]['description'],
                    'icon' => $this->getWeatherIconName($forecast['weather'][0]['main']),
                    'precipitation' => round(($forecast['pop'] ?? 0) * 100),
                    'wind_speed' => round($forecast['wind']['speed']),
                    'humidity' => $forecast['main']['humidity']
                ];

                // Only get 5 days
                if (count($dailyForecasts) >= 5) {
                    break;
                }
            }

            return $dailyForecasts;
        }
}