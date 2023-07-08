<?php

class ErrorHandler
{
    public static function handleException(Throwable $error): void
    {
        http_response_code(500);

        echo json_encode([
            'code' => $error->getCode(),
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
        ]);
    }

    public static function handleError(int $code, string $message, string $file, int $line)
    {
        throw new ErrorException($message, 0, $code, $file, $line);
    }
}
