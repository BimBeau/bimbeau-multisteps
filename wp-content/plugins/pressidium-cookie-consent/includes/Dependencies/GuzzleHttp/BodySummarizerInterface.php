<?php

namespace Pressidium\WP\CookieConsent\Dependencies\GuzzleHttp;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
