<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Eliminar todos los items del carrito del usuario
$sql = "DELETE FROM carrito WHERE usuario_id = $usuario_id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Carrito vaciado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al vaciar carrito']);
}
?>