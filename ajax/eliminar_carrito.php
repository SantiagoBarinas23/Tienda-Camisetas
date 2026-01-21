<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
$usuario_id = $_SESSION['usuario_id'];

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

// Eliminar solo si pertenece al usuario
$sql = "DELETE FROM carrito WHERE id = $cart_id AND usuario_id = $usuario_id";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
?>