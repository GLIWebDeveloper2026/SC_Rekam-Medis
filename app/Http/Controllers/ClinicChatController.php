<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ai\ClinicChatRequest;
use App\Services\Ai\ClinicChatOrchestrator;
use Illuminate\Http\JsonResponse;
use Throwable;

class ClinicChatController extends Controller
{
    public function __invoke(ClinicChatRequest $request, ClinicChatOrchestrator $chat): JsonResponse
    {
        try {
            return response()->json($chat->respond(
                user: $request->user(),
                messages: $request->validated('messages'),
                idempotencyKey: $request->validated('idempotency_key'),
                currentPage: $request->validated('current_page'),
            ));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Asisten klinik sedang tidak tersedia. Gunakan menu manual untuk melanjutkan.',
                'tool_results' => [],
                'execution_ids' => [],
            ], 503);
        }
    }
}
