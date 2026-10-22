<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Akaunting\Language\Middleware\SetLocale as Middleware;
use Jenssegers\Agent\Agent;

class SetLocale extends Middleware
{
    /**
     * Requests without an Accept-Language header would set the locale to false,
     * which Carbon 3 rejects, so fall back to the app locale in that case.
     */
    public function setDefaultLocale()
    {
        if (config('language.auto') && empty((new Agent())->languages())) {
            config(['language.auto' => false]);

            parent::setDefaultLocale();

            config(['language.auto' => true]);

            return;
        }

        parent::setDefaultLocale();
    }
}
