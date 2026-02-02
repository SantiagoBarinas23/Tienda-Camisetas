<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$accion = isset($_POST['accion']) ? cleanInput($_POST['accion']) : 'agregar';

if ($producto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido']);
    exit();
}

if ($accion === 'agregar') {
    // Agregar a favoritos
    $sql = "INSERT INTO Carrito (usuario_id, producto_id) VALUES ($usuario_id, $producto_id)";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Agregado a favoritos']);
    } else {
        if ($conn->errno === 1062) { // Duplicate entry
            echo json_encode(['success' => false, 'message' => 'Ya está en favoritos']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar']);
        }
    }
} else {
    // Eliminar de favoritos
    $sql = "DELETE FROM favoritos WHERE usuario_id = $usuario_id AND producto_id = $producto_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Eliminado de favoritos']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
    }
}
?>