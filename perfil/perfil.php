<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login/login.php');
}

$usuario_id = $_SESSION['usuario_id'];
$success = '';
$error = '';

// Actualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $telefono = cleanInput($_POST['telefono']);
    $direccion = cleanInput($_POST['direccion']);
    
    $sql = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', 
            telefono = '$telefono', direccion = '$direccion' 
            WHERE id = $usuario_id";
    
    if ($conn->query($sql)) {
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $success = '✓ Perfil actualizado correctamente';
    } else {
        $error = '✗ Error al actualizar perfil';
    }
}

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $sql_check = "SELECT password FROM usuarios WHERE id = $usuario_id";
    $result = $conn->query($sql_check);
    $user = $result->fetch_assoc();
    
    if (!verifyPassword($current_password, $user['password'])) {
        $error = '✗ Contraseña actual incorrecta';
    } elseif ($new_password !== $confirm_password) {
        $error = '✗ Las contraseñas nuevas no coinciden';
    } elseif (strlen($new_password) < 6) {
        $error = '✗ La contraseña debe tener al menos 6 caracteres';
    } else {
        $new_hash = hashPassword($new_password);
        $sql = "UPDATE usuarios SET password = '$new_hash' WHERE id = $usuario_id";
        if ($conn->query($sql)) {
            $success = '✓ Contraseña actualizada';
        } else {
            $error = '✗ Error al cambiar contraseña';
        }
    }
}

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id = $usuario_id";
$usuario = $conn->query($sql)->fetch_assoc();

// Obtener pedidos
$sql_pedidos = "SELECT * FROM pedidos WHERE usuario_id = $usuario_id ORDER BY fecha_pedido DESC LIMIT 5";
$pedidos = $conn->query($sql_pedidos);

// Obtener favoritos
$sql_favoritos = "SELECT p.*, e.nombre as equipo_nombre 
                  FROM favoritos f 
                  INNER JOIN productos p ON f.producto_id = p.id 
                  LEFT JOIN equipos e ON p.equipo_id = e.id
                  WHERE f.usuario_id = $usuario_id 
                  ORDER BY f.fecha_agregado DESC";
$favoritos = $conn->query($sql_favoritos);

// Contador carrito
$sql_cart = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = $usuario_id";
$result_cart = $conn->query($sql_cart);
$cart_count = $result_cart->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="perfil.css">
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
                    <a href="../carrito/carrito.php" class="nav-link">
                        🛒 Carrito
                        <?php if ($cart_count > 0): ?>
                            <span class="badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="../admin/admin.php" class="nav-link">⚙️ Admin</a>
                    <?php endif; ?>
                    <a href="../perfil/perfil.php" class="nav-link active">👤 Perfil</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h1>👋 Hola, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h1>
                    <p>Bienvenido a tu perfil</p>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1)); ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-grid">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="sidebar-menu">
                        <button class="menu-item active" onclick="showTab('info')">
                            📋 Información Personal
                        </button>
                        <button class="menu-item" onclick="showTab('pedidos')">
                            📦 Mis Pedidos
                        </button>
                        <button class="menu-item" onclick="showTab('favoritos')">
                            ❤️ Favoritos
                        </button>
                        <button class="menu-item" onclick="showTab('password')">
                            🔒 Cambiar Contraseña
                        </button>
                        <a href="../ajax/logout.php" class="menu-item logout">
                            🚪 Cerrar Sesión
                        </a>
                    </div>

                    <div class="user-stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo $pedidos->num_rows; ?></div>
                            <div class="stat-label">Pedidos</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $favoritos->num_rows; ?></div>
                            <div class="stat-label">Favoritos</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $cart_count; ?></div>
                            <div class="stat-label">En Carrito</div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="profile-content">
                    <!-- Información Personal -->
                    <div class="tab-content active" id="info">
                        <h2>📋 Información Personal</h2>
                        <form method="POST" class="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>👤 Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>👤 Apellido</label>
                                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>📧 Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                <small>El email no se puede modificar</small>
                            </div>

                            <div class="form-group">
                                <label>📱 Teléfono</label>
                                <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                            </div>

                            <div class="form-group">
                                <label>📍 Dirección</label>
                                <textarea name="direccion" rows="3"><?php echo htmlspecialchars($usuario['direccion']); ?></textarea>
                            </div>

                            <div class="form-info">
                                <p><strong>Rol:</strong> <?php echo $usuario['rol'] === 'administrador' ? '⚙️ Administrador' : '👤 Cliente'; ?></p>
                                <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                            </div>

                            <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                        </form>
                    </div>

                    <!-- Pedidos -->
                    <div class="tab-content" id="pedidos">
                        <h2>📦 Mis Pedidos</h2>
                        <?php if ($pedidos->num_rows > 0): ?>
                            <div class="pedidos-list">
                                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                                    <div class="pedido-card">
                                        <div class="pedido-header">
                                            <h3>Pedido #<?php echo $pedido['id']; ?></h3>
                                            <span class="estado estado-<?php echo $pedido['estado']; ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                        </div>
                                        <div class="pedido-info">
                                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                                            <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                                            <p><strong>Método:</strong> <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>📭 No tienes pedidos aún</p>
                                <a href="../tienda/tienda.php" class="btn btn-primary">Ir a comprar</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Favoritos -->
                    <div class="tab-content" id="favoritos">
                        <h2>❤️ Mis Favoritos</h2>
                        <?php if ($favoritos->num_rows > 0): ?>
                            <div class="favoritos-grid">
                                <?php while ($fav = $favoritos->fetch_assoc()): ?>
                                    <div class="fav-card">
                                        <div class="fav-image">
                                            <?php if ($fav['imagen_principal']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($fav['imagen_principal']); ?>" 
                                                     alt="<?php echo htmlspecialchars($fav['nombre']); ?>">
                                            <?php else: ?>
                                                <div class="placeholder">👕</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fav-info">
                                            <h4><?php echo htmlspecialchars($fav['nombre']); ?></h4>
                                            <p><?php echo htmlspecialchars($fav['equipo_nombre']); ?></p>
                                            <div class="fav-actions">
                                                <span class="price">$<?php echo number_format($fav['precio'], 2); ?></span>
                                                <button class="btn-remove-fav" onclick="removeFavorite(<?php echo $fav['id']; ?>)">
                                                    🗑️
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>💔 No tienes favoritos guardados</p>
                                <a href="../tienda/tienda.php" class="btn btn-primary">Explorar productos</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div class="tab-content" id="password">
                        <h2>🔒 Cambiar Contraseña</h2>
                        <form method="POST" class="password-form">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label>🔒 Contraseña Actual</label>
                                <input type="password" name="current_password" required>
                            </div>

                            <div class="form-group">
                                <label>🔑 Nueva Contraseña</label>
                                <input type="password" name="new_password" minlength="6" required>
                            </div>

                            <div class="form-group">
                                <label>🔑 Confirmar Nueva Contraseña</label>
                                <input type="password" name="confirm_password" minlength="6" required>
                            </div>

                            <button type="submit" class="btn btn-primary">🔄 Cambiar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>
    <script src="perfil.js"></script>
</body>
</html>