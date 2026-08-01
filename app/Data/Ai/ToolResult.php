<?php

namespace App\Data\Ai;

final readonly class ToolResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public bool $ok,
        public string $code,
        public string $message,
        public array $data = [],
        public ?string $resourceType = null,
        public ?string $resourceId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
