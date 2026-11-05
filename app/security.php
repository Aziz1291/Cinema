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
