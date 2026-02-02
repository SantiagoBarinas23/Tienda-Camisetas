<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'cantidad' => 0, 'items' => []]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener items del carrito con detalles
$sql = "SELECT c.id, c.producto_id, c.cantidad, c.talla,
        p.nombre, p.precio, p.precio_oferta, p.stock
        FROM carrito c 
        INNER JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = $usuario_id";

$result = $conn->query($sql);

$items = [];
$total_cantidad = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'producto_id' => $row['producto_id'],
        'nombre' => $row['nombre'],
        'cantidad' => $row['cantidad'],
        'talla' => $row['talla'],
        'precio' => $row['precio_oferta'] ?? $row['precio'],
        'stock' => $row['stock']
    ];
    $total_cantidad += $row['cantidad'];
}

echo json_encode([
    'success' => true,
    'cantidad' => $total_cantidad,
    'items' => $items
]);
?>