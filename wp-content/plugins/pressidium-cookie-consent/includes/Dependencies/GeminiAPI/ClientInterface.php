<?php

declare(strict_types=1);

namespace Pressidium\WP\CookieConsent\Dependencies\GeminiAPI;

use CurlHandle;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums\ModelName;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Requests\CountTokensRequest;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Requests\EmbedContentRequest;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Requests\GenerateContentRequest;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Requests\GenerateContentStreamRequest;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Responses\CountTokensResponse;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Responses\EmbedContentResponse;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Responses\GenerateContentResponse;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Responses\ListModelsResponse;

/**
 * @since v1.1.0
 */
interface ClientInterface
{
    public const API_KEY_HEADER_NAME = 'x-goog-api-key';
    public const API_VERSION_V1 = 'v1';
    public const API_VERSION_V1_BETA = 'v1beta';

    public function countTokens(CountTokensRequest $request): CountTokensResponse;
    public function generateContent(GenerateContentRequest $request): GenerateContentResponse;
    public function embedContent(EmbedContentRequest $request): EmbedContentResponse;
    public function generativeModel(ModelName|string $modelName): GenerativeModel;
    public function embeddingModel(ModelName|string $modelName): EmbeddingModel;
    public function listModels(): ListModelsResponse;
    public function withBaseUrl(string $baseUrl): self;

    /**
     * @param GenerateContentStreamRequest $request
     * @param callable(GenerateContentResponse): void $callback
     * @param CurlHandle|null $curl
     * @return void
     */
    public function generateContentStream(
        GenerateContentStreamRequest $request,
        callable $callback,
        ?CurlHandle $curl = null,
    ): void;
}
