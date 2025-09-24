<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_verification_code() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_password($password) {
    return strlen($password) >= 8;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
