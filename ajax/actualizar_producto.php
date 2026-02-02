<?php
require_once '../config.php';

header('Content-Type: application/json');

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$nombre = isset($_POST['nombre']) ? cleanInput($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? cleanInput($_POST['descripcion']) : '';
$precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
$precio_oferta = isset($_POST['precio_oferta']) ? floatval($_POST['precio_oferta']) : null;
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$talla = isset($_POST['talla']) ? cleanInput($_POST['talla']) : 'M';
$temporada = isset($_POST['temporada']) ? cleanInput($_POST['temporada']) : '';
$equipo_id = isset($_POST['equipo_id']) ? intval($_POST['equipo_id']) : null;
$categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
$destacado = isset($_POST['destacado']) ? 1 : 0;

// Validaciones
if ($producto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit;
}

if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

if ($precio <= 0) {
    echo json_encode(['success' => false, 'message' => 'El precio debe ser mayor a 0']);
    exit;
}

if ($stock < 0) {
    echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
    exit;
}

// Construir query de actualización
$sql = "UPDATE productos SET 
        nombre = '$nombre',
        descripcion = '$descripcion',
        precio = $precio,
        stock = $stock,
        talla = '$talla',
        temporada = '$temporada',
        destacado = $destacado";

// Agregar precio_oferta si existe
if ($precio_oferta !== null && $precio_oferta > 0) {
    $sql .= ", precio_oferta = $precio_oferta";
} else {
    $sql .= ", precio_oferta = NULL";
}

// Agregar equipo_id si existe
if ($equipo_id !== null && $equipo_id > 0) {
    $sql .= ", equipo_id = $equipo_id";
}

// Agregar categoria_id si existe
if ($categoria_id !== null && $categoria_id > 0) {
    $sql .= ", categoria_id = $categoria_id";
}

$sql .= " WHERE id = $producto_id";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Producto actualizado correctamente',
        'producto_id' => $producto_id
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al actualizar producto: ' . $conn->error
    ]);
}
?>