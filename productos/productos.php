<?php
require_once '../config.php';

// Obtener ID del producto
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($producto_id <= 0) {
    redirect('../tienda/tienda.php');
}

// Obtener producto
$sql = "SELECT p.*, c.nombre as categoria_nombre, e.nombre as equipo_nombre, e.pais, e.liga 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN equipos e ON p.equipo_id = e.id
        WHERE p.id = $producto_id AND p.activo = 1";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    redirect('../tienda/tienda.php');
}

$producto = $result->fetch_assoc();

// Verificar si está en favoritos
$en_favoritos = false;
if (isLoggedIn()) {
    $usuario_id = $_SESSION['usuario_id'];
    $sql_fav = "SELECT id FROM favoritos WHERE usuario_id = $usuario_id AND producto_id = $producto_id";
    $en_favoritos = $conn->query($sql_fav)->num_rows > 0;
}

// Productos relacionados
$sql_relacionados = "SELECT p.*, e.nombre as equipo_nombre 
                     FROM productos p 
                     LEFT JOIN equipos e ON p.equipo_id = e.id
                     WHERE p.activo = 1 
                     AND p.id != $producto_id 
                     AND (p.categoria_id = {$producto['categoria_id']} OR p.equipo_id = {$producto['equipo_id']})
                     ORDER BY RAND()
                     LIMIT 4";
$relacionados = $conn->query($sql_relacionados);

// Contador carrito
$cart_count = 0;
if (isLoggedIn()) {
    $usuario_id = $_SESSION['usuario_id'];
    $sql_cart = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = $usuario_id";
    $result_cart = $conn->query($sql_cart);
    if ($row_cart = $result_cart->fetch_assoc()) {
        $cart_count = $row_cart['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="productos.css">
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
                    <a href="../productos/productos.php" class="nav-link active">Productos</a>
                    <a href="../carrito/carrito.php" class="nav-link">
                        🛒 Carrito 
                        <?php if ($cart_count > 0): ?>
                            <span class="badge" id="cartBadge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="../admin/admin.php" class="nav-link">⚙️ Admin</a>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <a href="../perfil/perfil.php" class="nav-link">👤 Perfil</a>
                    <?php else: ?>
                        <a href="../login/login.php" class="nav-link">Iniciar Sesión</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../tienda/tienda.php">🏠 Inicio</a>
            <span>/</span>
            <a href="../tienda/tienda.php?categoria=<?php echo $producto['categoria_id']; ?>">
                <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
            </a>
            <span>/</span>
            <span class="current"><?php echo htmlspecialchars($producto['nombre']); ?></span>
        </div>
    </div>

    <!-- Product Detail -->
    <section class="product-detail">
        <div class="container">
            <div class="product-grid">
                <!-- Galería de imágenes -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainImage" 
                             src="<?php echo $producto['imagen_principal'] ? '../uploads/' . htmlspecialchars($producto['imagen_principal']) : '../assets/placeholder.png'; ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <?php if ($producto['destacado']): ?>
                            <div class="badge-destacado">⭐ Destacado</div>
                        <?php endif; ?>
                        <?php if ($producto['precio_oferta'] && $producto['precio_oferta'] < $producto['precio']): ?>
                            <div class="badge-oferta">
                                🔥 -<?php echo round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="thumbnail-images">
                        <img class="thumbnail active" 
                             src="<?php echo $producto['imagen_principal'] ? '../uploads/' . htmlspecialchars($producto['imagen_principal']) : '../assets/placeholder.png'; ?>" 
                             onclick="changeImage(this.src)">
                        <?php if ($producto['imagen_2']): ?>
                            <img class="thumbnail" 
                                 src="../uploads/<?php echo htmlspecialchars($producto['imagen_2']); ?>" 
                                 onclick="changeImage(this.src)">
                        <?php endif; ?>
                        <?php if ($producto['imagen_3']): ?>
                            <img class="thumbnail" 
                                 src="../uploads/<?php echo htmlspecialchars($producto['imagen_3']); ?>" 
                                 onclick="changeImage(this.src)">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información del producto -->
                <div class="product-info">
                    <div class="product-meta">
                        <span class="category-badge">
                            <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                        </span>
                        <?php if ($producto['temporada']): ?>
                            <span class="season-badge">
                                📅 Temporada <?php echo htmlspecialchars($producto['temporada']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="product-title">
                        <?php echo htmlspecialchars($producto['nombre']); ?>
                    </h1>

                    <div class="product-team">
                        <h2>⚽ <?php echo htmlspecialchars($producto['equipo_nombre']); ?></h2>
                        <?php if ($producto['pais']): ?>
                            <p>🌍 <?php echo htmlspecialchars($producto['pais']); ?> - <?php echo htmlspecialchars($producto['liga']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="product-price">
                        <?php if ($producto['precio_oferta'] && $producto['precio_oferta'] < $producto['precio']): ?>
                            <span class="price-original">$<?php echo number_format($producto['precio'], 2); ?></span>
                            <span class="price-current">$<?php echo number_format($producto['precio_oferta'], 2); ?></span>
                            <span class="price-save">
                                Ahorras $<?php echo number_format($producto['precio'] - $producto['precio_oferta'], 2); ?>
                            </span>
                        <?php else: ?>
                            <span class="price-current">$<?php echo number_format($producto['precio'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="product-stock">
                        <?php if ($producto['stock'] > 0): ?>
                            <span class="stock-available">
                                ✓ En stock (<?php echo $producto['stock']; ?> disponibles)
                            </span>
                        <?php else: ?>
                            <span class="stock-unavailable">✗ Producto agotado</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <h3>📝 Descripción</h3>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                    </div>

                    <?php if ($producto['stock'] > 0): ?>
                        <div class="product-options">
                            <div class="option-group">
                                <label>👕 Talla:</label>
                                <div class="talla-selector">
                                    <button class="talla-btn active" data-talla="XS">XS</button>
                                    <button class="talla-btn" data-talla="S">S</button>
                                    <button class="talla-btn" data-talla="M">M</button>
                                    <button class="talla-btn" data-talla="L">L</button>
                                    <button class="talla-btn" data-talla="XL">XL</button>
                                    <button class="talla-btn" data-talla="XXL">XXL</button>
                                </div>
                            </div>

                            <div class="option-group">
                                <label>🔢 Cantidad:</label>
                                <div class="quantity-selector">
                                    <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $producto['stock']; ?>" readonly>
                                    <button class="quantity-btn" onclick="increaseQuantity(<?php echo $producto['stock']; ?>)">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="product-actions">
                            <button class="btn btn-primary btn-large" id="addToCartBtn">
                                🛒 Agregar al carrito
                            </button>
                            <button class="btn btn-favorite" id="favoriteBtn" data-favorito="<?php echo $en_favoritos ? '1' : '0'; ?>">
                                <?php echo $en_favoritos ? '❤️' : '🤍'; ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="product-unavailable">
                            <p>😔 Este producto está agotado actualmente</p>
                            <button class="btn btn-secondary">🔔 Notificarme cuando esté disponible</button>
                        </div>
                    <?php endif; ?>

                    <div class="product-features">
                        <div class="feature">
                            <span>🚚</span>
                            <div>
                                <strong>Envío gratis</strong>
                                <p>En compras mayores a $100.000</p>
                            </div>
                        </div>
                        <div class="feature">
                            <span>✅</span>
                            <div>
                                <strong>Producto oficial</strong>
                                <p>100% original garantizado</p>
                            </div>
                        </div>
                        <div class="feature">
                            <span>↩️</span>
                            <div>
                                <strong>Devoluciones fáciles</strong>
                                <p>30 días para cambios</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos relacionados -->
    <?php if ($relacionados->num_rows > 0): ?>
        <section class="related-products">
            <div class="container">
                <h2 class="section-title">🎯 Productos relacionados</h2>
                <div class="related-grid">
                    <?php while ($rel = $relacionados->fetch_assoc()): ?>
                        <a href="productos.php?id=<?php echo $rel['id']; ?>" class="related-card">
                            <div class="related-image">
                                <?php if ($rel['imagen_principal']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($rel['imagen_principal']); ?>" 
                                         alt="<?php echo htmlspecialchars($rel['nombre']); ?>">
                                <?php else: ?>
                                    <div class="placeholder">👕</div>
                                <?php endif; ?>
                            </div>
                            <div class="related-info">
                                <h4><?php echo htmlspecialchars($rel['nombre']); ?></h4>
                                <p><?php echo htmlspecialchars($rel['equipo_nombre']); ?></p>
                                <span class="price">$<?php echo number_format($rel['precio'], 2); ?></span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        const productoId = <?php echo $producto_id; ?>;
        const productoNombre = "<?php echo htmlspecialchars($producto['nombre']); ?>";
        const enFavoritos = <?php echo $en_favoritos ? 'true' : 'false'; ?>;
    </script>
    <script src="productos.js"></script>
</body>
</html>