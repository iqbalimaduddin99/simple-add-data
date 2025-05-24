<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $exception)
    {   
        if (
            $exception instanceof RouteNotFoundException &&
            str_contains($exception->getMessage(), 'Route [login] not defined')
        ) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        return parent::render($request, $exception);
    }
}
