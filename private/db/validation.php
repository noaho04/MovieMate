<?php
/* Hash password, specify PASSWORD_BCRYPT
Returns the hashed password */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/* Verify password against hash
Returns bool value */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/* Validate a given username
Returns bool value */
function validateUsername($username) {
    if (mb_strlen($username, 'utf8') > 50) {
        return False;
    }
    if (!preg_match('/^[A-Za-z0-9]+$/', $username)) {
        return False;
    }
    return True;
}

/* Validate a given email
Returns bool value */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== False;
}

/* Validate a given password
Returns bool value */
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