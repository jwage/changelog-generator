<?php

declare(strict_types=1);

namespace ChangelogGenerator;

class IssueClientResponse
{
    /** @var mixed[] */
    private array $body;

    private ?string $nextUrl = null;

    /**
     * @param mixed[] $body
     */
    public function __construct(array $body, ?string $nextUrl)
    {
        $this->body    = $body;
        $this->nextUrl = $nextUrl;
    }

    /**
     * @return mixed[]
     */
    public function getBody(): array
    {
        return $this->body;
    }

    public function getNextUrl(): ?string
    {
        return $this->nextUrl;
    }
}
