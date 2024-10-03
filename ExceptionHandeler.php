<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected $statusCode;

    public function __construct($message = 'An error occurred', $statusCode = 400)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'status_code' => $this->getStatusCode(),
            ]
        ], $this->getStatusCode());
    }
}

// In app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'error' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $exception->errors(),
                    ]
                ], 422);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => [
                        'message' => 'Resource not found.',
                    ]
                ], 404);
            }

            if ($exception instanceof ApiException) {
                return $exception->render();
            }

            // Handle any other exceptions
            return response()->json([
                'error' => [
                    'message' => 'An unexpected error occurred.',
                ]
            ], 500);
        }

        return parent::render($request, $exception);
    }
}