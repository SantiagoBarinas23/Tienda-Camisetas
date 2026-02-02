<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;

if ($producto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Desactivar el producto en lugar de eliminarlo
$sql = "UPDATE productos SET activo = 0 WHERE id = $producto_id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
?>