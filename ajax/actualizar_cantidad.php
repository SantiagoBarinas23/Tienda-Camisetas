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
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
$usuario_id = $_SESSION['usuario_id'];

if ($cart_id <= 0 || $cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Verificar que el item pertenece al usuario
$sql_check = "SELECT c.*, p.stock FROM carrito c 
              INNER JOIN productos p ON c.producto_id = p.id
              WHERE c.id = $cart_id AND c.usuario_id = $usuario_id";
$result = $conn->query($sql_check);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    exit();
}

$item = $result->fetch_assoc();

// Verificar stock
if ($cantidad > $item['stock']) {
    echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
    exit();
}

// Actualizar cantidad
$sql_update = "UPDATE carrito SET cantidad = $cantidad WHERE id = $cart_id";

if ($conn->query($sql_update)) {
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
?>