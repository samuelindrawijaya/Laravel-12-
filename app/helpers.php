<?php

if (!function_exists('error')) {
    function error(string $key, int $status = 400)
    {
        return response()->json(['error' => __('errors.' . $key)], $status);
    }
}

if (!function_exists('error_with_input')) {
    function error_with_input(string $fieldValue, string $key, int $status = 400)
    {
        return response()->json([
            'errors' => __('errors.' . $key, ['input' => $fieldValue])
        ], $status);
    }
}
