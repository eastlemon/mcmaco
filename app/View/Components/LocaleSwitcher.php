<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LocaleSwitcher extends Component
{
    public string $current;

    public array $locales = [
        'ru' => 'RU',
        'en' => 'EN',
    ];

    public function __construct()
    {
        $this->current = app()->getLocale();
    }

    public function render()
    {
        return view('components.locale-switcher');
    }
}
