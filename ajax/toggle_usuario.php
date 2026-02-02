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

$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// No permitir desactivar el propio usuario
if ($usuario_id == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
    exit;
}

// Cambiar el estado del usuario
$sql = "UPDATE usuarios SET activo = NOT activo WHERE id = $usuario_id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
?>