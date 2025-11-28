<?php
// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password against hashed password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function validateUsername($username) {
    if (mb_strlen($username, 'utf8') > 50) {
        return False;
    }
    if (!preg_match('/^[A-Za-z0-9]+$/', $username)) {
        return False;
    }
    return True;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    if (strlen($password) < 10) {
        return False;
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return False;
    }
    if (!preg_match("/[0-9]{2,}/", $password)) {
        return False;
    }
    if (!preg_match("/[.!@#%&()_\\-=+]/", $password)) {
        return False;
    }
    return True;
}

?>