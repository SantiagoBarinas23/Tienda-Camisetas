<?php
require_once '../config.php';

// Obtener categorías
$sql_categorias = "SELECT * FROM categorias WHERE activo = 1";
$categorias = $conn->query($sql_categorias);

// Obtener equipos
$sql_equipos = "SELECT * FROM equipos WHERE activo = 1 ORDER BY nombre";
$equipos = $conn->query($sql_equipos);

// Filtros
$categoria_filtro = isset($_GET['categoria']) ? cleanInput($_GET['categoria']) : '';
$equipo_filtro = isset($_GET['equipo']) ? cleanInput($_GET['equipo']) : '';
$busqueda = isset($_GET['busqueda']) ? cleanInput($_GET['busqueda']) : '';
$orden = isset($_GET['orden']) ? cleanInput($_GET['orden']) : 'recientes';
$precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0;
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 999999;

// Construir query de productos
$sql = "SELECT p.*, c.nombre as categoria_nombre, e.nombre as equipo_nombre, e.pais 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN equipos e ON p.equipo_id = e.id
        WHERE p.activo = 1";

if ($categoria_filtro) {
    $sql .= " AND p.categoria_id = '$categoria_filtro'";
}

if ($equipo_filtro) {
    $sql .= " AND p.equipo_id = '$equipo_filtro'";
}

if ($busqueda) {
    $sql .= " AND (p.nombre LIKE '%$busqueda%' OR e.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

$sql .= " AND p.precio BETWEEN $precio_min AND $precio_max";

// Ordenamiento
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY p.precio DESC";
        break;
    case 'nombre':
        $sql .= " ORDER BY p.nombre ASC";
        break;
    case 'destacados':
        $sql .= " ORDER BY p.destacado DESC, p.fecha_agregado DESC";
        break;
    default:
        $sql .= " ORDER BY p.fecha_agregado DESC";
}

$productos = $conn->query($sql);

// Contar items en carrito
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
    <title><?php echo SITE_NAME; ?> - Tienda</title>
    <link rel="stylesheet" href="tienda.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>⚽ <?php echo SITE_NAME; ?></h1>
                </div>
                <nav class="nav">
                    <a href="tienda.php" class="nav-link active">Tienda</a>
                    <a href="../productos/productos.php" class="nav-link">Productos</a>
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
                        <a href="../login/login.php" class="nav-link btn-login">Iniciar Sesión</a>
                        <a href="../register/register.php" class="nav-link btn-register">Registrarse</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2 class="hero-title">🏆 Las mejores camisetas de fútbol</h2>
            <p class="hero-subtitle">Encuentra la camiseta de tu equipo favorito - Envíos a toda Colombia</p>
            <?php if (isLoggedIn()): ?>
                <p class="hero-welcome">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>! 👋</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Filters -->
    <section class="filters">
        <div class="container">
            <form method="GET" class="filters-form" id="filtersForm">
                <div class="filter-group">
                    <label for="busqueda">🔍 Buscar:</label>
                    <input type="text" 
                           id="busqueda" 
                           name="busqueda" 
                           placeholder="Equipo, producto..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>

                <div class="filter-group">
                    <label for="categoria">📂 Categoría:</label>
                    <select id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <?php 
                        $categorias->data_seek(0);
                        while ($cat = $categorias->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $categoria_filtro == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="equipo">⚽ Equipo:</label>
                    <select id="equipo" name="equipo">
                        <option value="">Todos</option>
                        <?php while ($eq = $equipos->fetch_assoc()): ?>
                            <option value="<?php echo $eq['id']; ?>" 
                                    <?php echo $equipo_filtro == $eq['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($eq['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="precio_min">💰 Precio:</label>
                    <div class="price-inputs">
                        <input type="number" 
                               id="precio_min" 
                               name="precio_min" 
                               placeholder="Mín" 
                               value="<?php echo $precio_min > 0 ? $precio_min : ''; ?>"
                               min="0">
                        <span>-</span>
                        <input type="number" 
                               id="precio_max" 
                               name="precio_max" 
                               placeholder="Máx"
                               value="<?php echo $precio_max < 999999 ? $precio_max : ''; ?>"
                               min="0">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="orden">🔃 Ordenar:</label>
                    <select id="orden" name="orden">
                        <option value="recientes" <?php echo $orden == 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="destacados" <?php echo $orden == 'destacados' ? 'selected' : ''; ?>>Destacados</option>
                        <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                        <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                        <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="tienda.php" class="btn-clear">Limpiar</a>
            </form>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products">
        <div class="container">
            <div class="products-header">
                <h3>📦 Productos disponibles (<?php echo $productos->num_rows; ?>)</h3>
            </div>
            
            <div class="products-grid">
                <?php if ($productos->num_rows > 0): ?>
                    <?php while ($producto = $productos->fetch_assoc()): ?>
                        <div class="product-card" data-product-id="<?php echo $producto['id']; ?>">
                            <?php if ($producto['destacado']): ?>
                                <div class="product-badge destacado">⭐ Destacado</div>
                            <?php endif; ?>
                            
                            <?php if ($producto['precio_oferta'] && $producto['precio_oferta'] < $producto['precio']): ?>
                                <div class="product-badge oferta">🔥 Oferta</div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <?php if ($producto['imagen_principal']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($producto['imagen_principal']); ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <?php else: ?>
                                    <div class="product-placeholder">👕</div>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <div class="product-meta">
                                    <span class="product-category">
                                        <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                    </span>
                                    <?php if ($producto['temporada']): ?>
                                        <span class="product-season">
                                            📅 <?php echo htmlspecialchars($producto['temporada']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="product-name">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </h3>
                                
                                <p class="product-team">
                                    <?php echo htmlspecialchars($producto['equipo_nombre']); ?>
                                    <?php if ($producto['pais']): ?>
                                        - <?php echo htmlspecialchars($producto['pais']); ?>
                                    <?php endif; ?>
                                </p>
                                
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)); ?>...
                                </p>

                                <div class="product-footer">
                                    <div class="product-pricing">
                                        <?php if ($producto['precio_oferta'] && $producto['precio_oferta'] < $producto['precio']): ?>
                                            <span class="price-original">$<?php echo number_format($producto['precio'], 2); ?></span>
                                            <span class="price-current">$<?php echo number_format($producto['precio_oferta'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="price-current">$<?php echo number_format($producto['precio'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-stock">
                                        <?php if ($producto['stock'] > 0): ?>
                                            <span class="stock-available">✓ Stock: <?php echo $producto['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="stock-unavailable">✗ Agotado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="product-actions">
                                    <a href="../productos/productos.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-secondary">
                                        👁️ Ver detalles
                                    </a>
                                    <?php if ($producto['stock'] > 0): ?>
                                        <button class="btn btn-primary add-to-cart" 
                                                data-product-id="<?php echo $producto['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                            🛒 Agregar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>
                                            😔 Agotado
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <div class="no-products-icon">😔</div>
                        <h3>No se encontraron productos</h3>
                        <p>Intenta ajustar los filtros de búsqueda</p>
                        <a href="tienda.php" class="btn btn-primary">Ver todos los productos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
            <p>Envíos a toda Colombia 🇨🇴</p>
        </div>
    </footer>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script src="tienda.js"></script>
</body>
</html>