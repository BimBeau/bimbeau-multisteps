<?php

declare(strict_types=1);

namespace Pressidium\WP\CookieConsent\Dependencies\GeminiAPI;

use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums\ModelName;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums\Role;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Enums\TaskType;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Requests\EmbedContentRequest;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Resources\Content;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Resources\Parts\PartInterface;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Responses\EmbedContentResponse;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Http\Client\ClientExceptionInterface;

class EmbeddingModel
{
    private ?TaskType $taskType = null;

    public function __construct(
        private readonly Client $client,
        public readonly ModelName|string $modelName,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function embedContent(PartInterface ...$parts): EmbedContentResponse
    {
        $request = new EmbedContentRequest(
            $this->modelName,
            new Content($parts, Role::User),
            $this->taskType,
        );

        return $this->client->embedContent($request);
    }

    /**
     * embedContentWithTitle will override the task type with TaskType::RETRIEVAL_DOCUMENT.
     * This is not a persistent change.
     *
     * @throws ClientExceptionInterface
     */
    public function embedContentWithTitle(string $title, PartInterface ...$parts): EmbedContentResponse
    {
        $request = new EmbedContentRequest(
            $this->modelName,
            new Content($parts, Role::User),
            TaskType::RETRIEVAL_DOCUMENT,
            $title,
        );

        return $this->client->embedContent($request);
    }

    public function withTaskType(TaskType $taskType): self
    {
        $clone = clone $this;
        $clone->taskType = $taskType;

        return $clone;
    }
}
