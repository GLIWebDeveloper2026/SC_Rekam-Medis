<?php

namespace App\Contracts\Ai;

interface OpenAiClient
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createResponse(array $payload): array;
}
