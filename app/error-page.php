<?php
// Shows a short error page instead of PHP's error text (which exposed file paths),
// and writes the details to the server log.
if (!defined('APP_ERROR_PAGE')) {
    define('APP_ERROR_PAGE', true);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    function app_error_page(): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Something went wrong</title><link rel="icon" href="/Cinema/public/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="/Cinema/public/favicon.svg"><link rel="icon" type="image/png" sizes="48x48" href="/Cinema/public/favicon-48.png"><link rel="apple-touch-icon" href="/Cinema/public/apple-touch-icon.png"></head>'
            . '<body style="font-family:system-ui,sans-serif;max-width:32rem;margin:15vh auto;padding:0 1rem;color:#1f2937">'
            . '<h1 style="font-size:1.5rem">Something went wrong</h1>'
            . '<p>We could not complete that request, and nothing was saved. Please go back and try again.</p>'
            . '<p><a href="javascript:history.back()">Go back</a> &middot; <a href="/Cinema/">Home</a></p>'
            . '</body></html>';
    }

    set_exception_handler(function (Throwable $e): void {
        error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        app_error_page();
    });

    register_shutdown_function(function (): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            app_error_page();
        }
    });
}
