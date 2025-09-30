<?php

namespace App\Livewire\Components;

use Carbon\Carbon;
use Livewire\Component;

class NewsCard extends Component
{
    public $news;

    public function mount($news)
    {
        $this->news = $news;
    }

    public function cleanContent($content)
    {
        $content = preg_replace('/<\/?[^>]+(>|$)/', '', $content);
        return mb_substr($content, 0, 330, 'UTF-8') . '...';
    }
    public function render()
    {
        return view('livewire.components.news-card', [
            'cleanContent' => $this->cleanContent($this->news['content']),
        ]);
    }
}
