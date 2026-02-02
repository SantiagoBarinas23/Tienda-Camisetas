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

$nombre = isset($_POST['nombre']) ? cleanInput($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? cleanInput($_POST['descripcion']) : '';

if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

$sql = "INSERT INTO categorias (nombre, descripcion, activo) VALUES ('$nombre', '$descripcion', 1)";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Categoría creada', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear categoría']);
}
?>