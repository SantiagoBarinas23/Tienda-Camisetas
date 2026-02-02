
<?php
require_once '../config.php';

header('Content-Type: application/json');

$logged_in = isLoggedIn();

$response = [
    'logged_in' => $logged_in
];

if ($logged_in) {
    $response['usuario_id'] = $_SESSION['usuario_id'];
    $response['nombre'] = $_SESSION['nombre'];
    $response['email'] = $_SESSION['email'];
    $response['rol'] = $_SESSION['rol'];
}

echo json_encode($response);
?>