<?php

namespace Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Traits;

use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums\ModelName;

trait ModelNameToString
{
    private function modelNameToString(ModelName|string $modelName): string
    {
        return is_string($modelName) ? "models/$modelName" : $modelName->value;
    }
}
