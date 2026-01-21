<?php
require_once '../config.php';

// Verificar que esté logueado
if (!isLoggedIn()) {
    redirect('../login/login.php');
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener items del carrito
$sql = "SELECT c.*, p.nombre, p.precio, p.precio_oferta, p.imagen_principal, p.stock, e.nombre as equipo_nombre 
        FROM carrito c 
        INNER JOIN productos p ON c.producto_id = p.id 
        LEFT JOIN equipos e ON p.equipo_id = e.id
        WHERE c.usuario_id = $usuario_id 
        ORDER BY c.fecha_agregado DESC";

$result = $conn->query($sql);

$items = [];
$total = 0;
$descuento = 0;

while ($row = $result->fetch_assoc()) {
    $precio = $row['precio_oferta'] && $row['precio_oferta'] < $row['precio'] ? $row['precio_oferta'] : $row['precio'];
    $subtotal = $precio * $row['cantidad'];
    $total += $subtotal;
    
    if ($row['precio_oferta'] && $row['precio_oferta'] < $row['precio']) {
        $descuento += ($row['precio'] - $row['precio_oferta']) * $row['cantidad'];
    }
    
    $items[] = [
        'id' => $row['id'],
        'producto_id' => $row['producto_id'],
        'nombre' => $row['nombre'],
        'equipo' => $row['equipo_nombre'],
        'precio' => $precio,
        'cantidad' => $row['cantidad'],
        'talla' => $row['talla'],
        'subtotal' => $subtotal,
        'imagen' => $row['imagen_principal'],
        'stock' => $row['stock']
    ];
}

$envio = $total >= 100 ? 0 : 15;
$total_final = $total + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="carrito.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../tienda/tienda.php">
                        <h1>⚽ <?php echo SITE_NAME; ?></h1>
                    </a>
                </div>
                <nav class="nav">
                    <a href="../tienda/tienda.php" class="nav-link">Tienda</a>
                    <a href="../productos/productos.php" class="nav-link">Productos</a>
                    <a href="../carrito/carrito.php" class="nav-link active">
                        🛒 Carrito
                        <?php if (count($items) > 0): ?>
                            <span class="badge"><?php echo count($items); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="../admin/admin.php" class="nav-link">⚙️ Admin</a>
                    <?php endif; ?>
                    <a href="../perfil/perfil.php" class="nav-link">👤 Perfil</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Cart Content -->
    <div class="cart-container">
        <div class="container">
            <div class="cart-header">
                <h1>🛒 Mi Carrito de Compras</h1>
                <p>Tienes <?php echo count($items); ?> producto(s) en tu carrito</p>
            </div>

            <?php if (count($items) > 0): ?>
                <div class="cart-grid">
                    <!-- Items -->
                    <div class="cart-items">
                        <?php foreach ($items as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                                <div class="item-image">
                                    <?php if ($item['imagen']): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($item['imagen']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder">👕</div>
                                    <?php endif; ?>
                                </div>

                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                    <p class="item-team">⚽ <?php echo htmlspecialchars($item['equipo']); ?></p>
                                    <p class="item-talla">👕 Talla: <strong><?php echo htmlspecialchars($item['talla']); ?></strong></p>
                                    <?php if ($item['cantidad'] > $item['stock']): ?>
                                        <p class="item-warning">⚠️ Stock insuficiente (disponibles: <?php echo $item['stock']; ?>)</p>
                                    <?php endif; ?>
                                </div>

                                <div class="item-quantity">
                                    <label>Cantidad:</label>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1, <?php echo $item['stock']; ?>)">-</button>
                                        <input type="number" 
                                               value="<?php echo $item['cantidad']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock']; ?>"
                                               class="quantity-input"
                                               data-cart-id="<?php echo $item['id']; ?>"
                                               readonly>
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1, <?php echo $item['stock']; ?>)">+</button>
                                    </div>
                                </div>

                                <div class="item-price">
                                    <span class="price-label">Precio:</span>
                                    <span class="price-value">$<?php echo number_format($item['precio'], 2); ?></span>
                                </div>

                                <div class="item-subtotal">
                                    <span class="subtotal-label">Subtotal:</span>
                                    <span class="subtotal-value">$<?php echo number_format($item['subtotal'], 2); ?></span>
                                </div>

                                <button class="item-remove" onclick="removeItem(<?php echo $item['id']; ?>)">
                                    🗑️
                                </button>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-actions">
                            <a href="../tienda/tienda.php" class="btn btn-secondary">
                                ← Seguir comprando
                            </a>
                            <button class="btn btn-danger" onclick="clearCart()">
                                🗑️ Vaciar carrito
                            </button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="cart-summary">
                        <h2>📋 Resumen del pedido</h2>

                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <?php if ($descuento > 0): ?>
                            <div class="summary-row discount">
                                <span>Descuento:</span>
                                <span>-$<?php echo number_format($descuento, 2); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="summary-row">
                            <span>Envío:</span>
                            <?php if ($envio > 0): ?>
                                <span>$<?php echo number_format($envio, 2); ?></span>
                            <?php else: ?>
                                <span class="free-shipping">¡GRATIS! 🎉</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($total < 100 && $total > 0): ?>
                            <div class="shipping-notice">
                                <p>💡 Agrega $<?php echo number_format(100 - $total, 2); ?> más para envío gratis</p>
                            </div>
                        <?php endif; ?>

                        <div class="summary-total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total_final, 2); ?></span>
                        </div>

                        <button class="btn btn-primary btn-checkout" onclick="proceedToCheckout()">
                            💳 Proceder al pago
                        </button>

                        <div class="payment-methods">
                            <p>Métodos de pago:</p>
                            <div class="payment-icons">
                                💳 💰 📱 🏦
                            </div>
                        </div>

                        <div class="security-badge">
                            <p>🔒 Compra 100% segura</p>
                            <p>✅ Productos originales</p>
                            <p>📦 Envío rápido</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Explora nuestra tienda y encuentra las mejores camisetas!</p>
                    <a href="../tienda/tienda.php" class="btn btn-primary">
                        Ver productos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script src="carrito.js"></script>
</body>
</html>