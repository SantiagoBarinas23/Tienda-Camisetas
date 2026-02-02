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
$pais = isset($_POST['pais']) ? cleanInput($_POST['pais']) : '';
$liga = isset($_POST['liga']) ? cleanInput($_POST['liga']) : '';

if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

$sql = "INSERT INTO equipos (nombre, pais, liga, activo) VALUES ('$nombre', '$pais', '$liga', 1)";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Equipo creado', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear equipo']);
}
?>