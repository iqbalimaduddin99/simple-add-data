<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'status'  => $statusCode,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $statusCode);
    }

    public static function error($message = 'Error', $errors = [], $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'status'  => $statusCode,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $statusCode);
    }
}
