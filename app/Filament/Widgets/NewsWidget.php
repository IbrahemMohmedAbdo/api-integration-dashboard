<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;

class NewsWidget extends Widget
{
    protected static string $view = 'filament.widgets.news-widget';

    public $newsData;
    public string $activeTab = 'breaking';

    public function mount()
    {
        $this->fetchNewsData();
    }

    public function fetchNewsData()
    {
        try {
            $apiKey = config('services.newsapi.key');
            $categories = ['business', 'technology', 'science', 'health'];
            $this->newsData = [];

            foreach ($categories as $category) {
                $response = Http::get('https://newsapi.org/v2/top-headlines', [
                    'country' => 'us',
                    'category' => $category,
                    'pageSize' => 5,
                    'apiKey' => $apiKey
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['articles']) && !empty($data['articles'])) {
                        $this->newsData[$category] = $data['articles'];
                    }
                }
            }

            if (empty($this->newsData)) {
                $this->newsData = ['error' => 'No news data available'];
            }
        } catch (\Exception $e) {
            $this->newsData = ['error' => 'Error fetching news: ' . $e->getMessage()];
        }
    }

    protected static function shouldPoll(): bool
    {
        return true;
    }

    public static function getPollingInterval(): ?string
    {
        return '15m';
    }

    #[On('setActiveTab')]
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getNewsBackground(): string
    {
        // Return your background image URL or path
        return asset('images/news-background.jpg');
    }

    public function refreshNews(): void
    {
        // Add your refresh logic here
    }
}
