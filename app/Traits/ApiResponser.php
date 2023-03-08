<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($message, $data = null, $code = 200)
    {
        return response()->json(
            [
                'status' => 'success',
                'message' => $message,
                'data' => $data,
                'code' => $code
            ]);
    }

    protected function errorResponse($message = 'Something went wrong', $errors = [], $code = 500)
    {
        return response()->json(
            [
                'status' => 'error',
                'message' => $message,
                'errors' => $errors,
                'code' => $code
            ]);
    }

    protected function response($data, $message = null)
    {
        if (is_array($data) && count($data) == 0 || empty($data)) {
            $message = 'No records found!';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }
}
