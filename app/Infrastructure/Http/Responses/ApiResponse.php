<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ApiResponse
{
    public static function success( array $data = [], string $message = 'Success', int $status = Response::HTTP_OK): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== []) {
            $response['data'] = $data;
        }
    
        return response()->json($response, $status);
    }

    public static function error(string $message, int $status = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}