<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        $body = [
            'success' => true,
            'message' => $message,
        ];

        if (! empty($data)) {
            $body['data'] = $data;
        }

        return response()->json($body, $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Validation failed.', 422, $errors);
    }

    protected function forbiddenResponse(): JsonResponse
    {
        return $this->errorResponse('You do not have permission to perform this action.', 403);
    }
}
