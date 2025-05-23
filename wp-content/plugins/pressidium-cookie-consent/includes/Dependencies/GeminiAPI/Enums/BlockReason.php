<?php

declare(strict_types=1);

namespace Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums;

enum BlockReason: string
{
    case BLOCK_REASON_UNSPECIFIED = 'BLOCK_REASON_UNSPECIFIED';
    case SAFETY = 'SAFETY';
    case OTHER = 'OTHER';
}
