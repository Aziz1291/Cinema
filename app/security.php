<?php
/**
 * security.php — CSRF tokens and safe poster upload.
 * Loaded by public/index.php, so every view can call csrf_field().
 */

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Hidden input to put inside every POST form. */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_valid()
{
    return !empty($_SESSION['csrf'])
        && isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf_token']);
}

/**
 * Checks posted admin form fields. Returns the cleaned values, or the first
 * problem as a message. Each rule is [label, type, min, max]:
 * text limits are lengths, int and float limits are values.
 */
function validate_fields(array $rules, &$clean)
{
    $clean = [];
    foreach ($rules as $key => [$label, $type, $min, $max]) {
        $raw = trim((string) ($_POST[$key] ?? ''));
        if ($type === 'text') {
            $length = mb_strlen($raw);
            if ($length < $min) {
                return $min <= 1 ? "$label is required." : "$label must be at least $min characters long.";
            }
            if ($length > $max) {
                return "$label must be $max characters or fewer.";
            }
            $clean[$key] = $raw;
        } else {
            $number = $type === 'int'
                ? filter_var($raw, FILTER_VALIDATE_INT)
                : filter_var($raw, FILTER_VALIDATE_FLOAT);
            if ($number === false || $number < $min || $number > $max) {
                return "$label must be a number between $min and $max.";
            }
            $clean[$key] = $number;
        }
    }
    return null;
}

/** Sends the admin back to a list page with a message shown above the list. */
function fail_to($listUrl, $message)
{
    $_SESSION['form_error'] = $message;
    header('Location: ' . $listUrl . '?error=invalid');
    exit;
}

/** Prints and clears the message saved by fail_to(). */
function invalid_alert()
{
    $message = $_SESSION['form_error'] ?? 'Some values were not valid.';
    unset($_SESSION['form_error']);
    return '<div class="alert alert--error">⚠ ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
}

/** Ends the request with the 404 page. */
function not_found()
{
    http_response_code(404);
    require __DIR__ . '/views/404.php';
    exit;
}

/**
 * Validate and store an uploaded poster in public/assets.
 * The file name is random and the extension comes from the detected MIME type,
 * never from the client file name. Returns the stored file name or null.
 */
function save_poster(array $file)
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($types[$mime])) {
        return null;
    }
    $name = 'poster_' . bin2hex(random_bytes(8)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../public/assets/' . $name)) {
        return null;
    }
    return $name;
}
