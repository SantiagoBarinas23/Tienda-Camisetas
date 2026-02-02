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

$pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
$estado = isset($_POST['estado']) ? cleanInput($_POST['estado']) : '';

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

$estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];

if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

$sql = "UPDATE pedidos SET estado = '$estado', fecha_actualizacion = NOW() WHERE id = $pedido_id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
?>