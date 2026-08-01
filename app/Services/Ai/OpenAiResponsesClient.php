<?php

namespace App\Services\Ai;

use App\Contracts\Ai\OpenAiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenAiResponsesClient implements OpenAiClient
{
    public function createResponse(array $payload): array
    {
        $key = config('services.openai.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $requestPayload = [
            ...$payload,
            'model' => config('services.openai.model'),
            'store' => false,
            'reasoning' => ['effort' => config('services.openai.reasoning_effort')],
        ];
        $response = Http::baseUrl(rtrim((string) config('services.openai.base_url'), '/'))
            ->withToken($key)
            ->acceptJson()
            ->asJson()
            ->connectTimeout((int) config('services.openai.connect_timeout'))
            ->timeout((int) config('services.openai.timeout'))
            ->retry([200, 500, 1000], function (Throwable $exception, PendingRequest $request): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            }, throw: false)
            ->post('/responses', $requestPayload)
            ->throw();
        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('OpenAI returned an invalid response.');
        }

        return $data;
    }
}
