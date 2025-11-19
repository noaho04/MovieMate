<?php
// Authentication request handler
// Handles login and signup requests via AJAX

session_start();
require_once "db.php";

header('Content-Type: application/json');

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'login') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        $result = loginUser($username, $password);

        echo json_encode($result);
        exit;

    } else if ($action === 'signup') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        $result = registerUser($username, $email, $password);
        echo json_encode($result);
        exit;

    } else if ($action === 'logout') {
        logoutUser();
        exit;

    } else {
        $response["message"] = "Invalid action";
        echo json_encode($response);
        exit;
    }
} else {
    $response["message"] = "Invalid request method";
    echo json_encode($response);
    exit;
}
?>
