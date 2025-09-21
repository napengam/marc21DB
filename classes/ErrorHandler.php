<?php

class ErrorHandler {

    private static ?self $instance = null;

    private function __construct() {
        self::$instance = $this;
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public static function register(): self {
        return self::$instance ?? new self();
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Respect error_reporting settings
        if (!(error_reporting() & $errno)) {
            return true;
        }

        $this->reportError($errno, $errstr, $errfile, $errline);
        return true; // Prevent PHP's internal error handler from running
    }

    public function handleShutdown(): void {
        $error = error_get_last();

        if ($error === null || !$error || !$this->isFatalError($error['type'])) {
            return; // nothing to do
        }

        $this->reportError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
        );
    }

    private function isFatalError(int $type): bool {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true);
    }

    private function reportError(int $errno, string $errstr, string $errfile, int $errline): void {
        // Log error details
        error_log("Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}");

        // Return structured JSON response
        $response = [
            'error' => 'Fehler aufgetreten. Bitte dem Administrator mitteilen',
            'result' => ''
        ];

        //header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        exit;
    }
}
