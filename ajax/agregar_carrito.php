<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
$talla = isset($_POST['talla']) ? cleanInput($_POST['talla']) : 'M';

if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Verificar que el producto existe y tiene stock
$sql_producto = "SELECT stock FROM productos WHERE id = $producto_id AND activo = 1";
$result = $conn->query($sql_producto);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}

$producto = $result->fetch_assoc();

if ($producto['stock'] < $cantidad) {
    echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
    exit;
}

// Verificar si el producto ya está en el carrito
$sql_check = "SELECT id, cantidad FROM carrito 
              WHERE usuario_id = $usuario_id 
              AND producto_id = $producto_id 
              AND talla = '$talla'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    // Actualizar cantidad
    $row = $result_check->fetch_assoc();
    $nueva_cantidad = $row['cantidad'] + $cantidad;
    
    if ($nueva_cantidad > $producto['stock']) {
        echo json_encode(['success' => false, 'message' => 'No hay suficiente stock']);
        exit;
    }
    
    $sql_update = "UPDATE carrito SET cantidad = $nueva_cantidad WHERE id = {$row['id']}";
    
    if ($conn->query($sql_update)) {
        echo json_encode(['success' => true, 'message' => 'Cantidad actualizada en el carrito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
} else {
    // Insertar nuevo item
    $sql_insert = "INSERT INTO carrito (usuario_id, producto_id, cantidad, talla) 
                   VALUES ($usuario_id, $producto_id, $cantidad, '$talla')";
    
    if ($conn->query($sql_insert)) {
        echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar producto']);
    }
}
?>