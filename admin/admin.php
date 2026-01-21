<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('../tienda/tienda.php');
}

// Obtener estadísticas
$sql_stats = "
    SELECT 
        (SELECT COUNT(*) FROM productos WHERE activo = 1) as total_productos,
        (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as total_usuarios,
        (SELECT COUNT(*) FROM pedidos) as total_pedidos,
        (SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado != 'cancelado') as ingresos_totales,
        (SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente') as pedidos_pendientes
";
$stats = $conn->query($sql_stats)->fetch_assoc();

// Productos
$sql_productos = "SELECT p.*, e.nombre as equipo, c.nombre as categoria 
                  FROM productos p 
                  LEFT JOIN equipos e ON p.equipo_id = e.id
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  ORDER BY p.id DESC LIMIT 10";
$productos = $conn->query($sql_productos);

// Usuarios
$sql_usuarios = "SELECT * FROM usuarios ORDER BY id DESC LIMIT 10";
$usuarios = $conn->query($sql_usuarios);

// Pedidos recientes
$sql_pedidos = "SELECT p.*, u.nombre, u.apellido 
                FROM pedidos p 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                ORDER BY p.fecha_pedido DESC LIMIT 10";
$pedidos = $conn->query($sql_pedidos);

// Categorías
$sql_categorias = "SELECT * FROM categorias ORDER BY id";
$categorias = $conn->query($sql_categorias);

// Equipos
$sql_equipos = "SELECT * FROM equipos ORDER BY nombre LIMIT 10";
$equipos = $conn->query($sql_equipos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>⚙️ Panel de Administración</h1>
                </div>
                <nav class="nav">
                    <a href="../tienda/tienda.php" class="nav-link">🏠 Tienda</a>
                    <a href="../perfil/perfil.php" class="nav-link">👤 Perfil</a>
                    <a href="../ajax/logout.php" class="nav-link">🚪 Salir</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Admin Container -->
    <div class="admin-container">
        <div class="container">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_productos']; ?></h3>
                        <p>Productos Activos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_usuarios']; ?></h3>
                        <p>Usuarios Registrados</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_pedidos']; ?></h3>
                        <p>Total Pedidos</p>
                    </div>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['ingresos_totales'], 2); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <!-- Admin Tabs -->
            <div class="admin-tabs">
                <button class="tab-btn active" onclick="showAdminTab('productos')">📦 Productos</button>
                <button class="tab-btn" onclick="showAdminTab('usuarios')">👥 Usuarios</button>
                <button class="tab-btn" onclick="showAdminTab('pedidos')">🛒 Pedidos</button>
                <button class="tab-btn" onclick="showAdminTab('categorias')">📂 Categorías</button>
                <button class="tab-btn" onclick="showAdminTab('equipos')">⚽ Equipos</button>
            </div>

            <!-- PRODUCTOS TAB -->
            <div class="admin-content active" id="productos">
                <div class="content-header">
                    <h2>📦 Gestión de Productos</h2>
                    <button class="btn btn-primary" onclick="showAddProductModal()">➕ Nuevo Producto</button>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Equipo</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $productos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['equipo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['categoria']); ?></td>
                                    <td>$<?php echo number_format($p['precio'], 2); ?></td>
                                    <td><?php echo $p['stock']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $p['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-edit" onclick="editProduct(<?php echo $p['id']; ?>)">✏️</button>
                                        <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $p['id']; ?>)">🗑️</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- USUARIOS TAB -->
            <div class="admin-content" id="usuarios">
                <div class="content-header">
                    <h2>👥 Gestión de Usuarios</h2>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $usuarios->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['telefono']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['rol'] === 'administrador' ? 'badge-warning' : 'badge-info'; ?>">
                                            <?php echo ucfirst($u['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-edit" onclick="editUser(<?php echo $u['id']; ?>)">✏️</button>
                                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                            <button class="btn-action btn-delete" onclick="toggleUserStatus(<?php echo $u['id']; ?>)">
                                                <?php echo $u['activo'] ? '🚫' : '✅'; ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PEDIDOS TAB -->
            <div class="admin-content" id="pedidos">
                <div class="content-header">
                    <h2>🛒 Gestión de Pedidos</h2>
                    <span class="alert-badge">⚠️ <?php echo $stats['pedidos_pendientes']; ?> pendientes</span>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Método Pago</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ped = $pedidos->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $ped['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($ped['nombre'] . ' ' . $ped['apellido']); ?></td>
                                    <td>$<?php echo number_format($ped['total'], 2); ?></td>
                                    <td>
                                        <select class="status-select" onchange="updateOrderStatus(<?php echo $ped['id']; ?>, this.value)">
                                            <option value="pendiente" <?php echo $ped['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="procesando" <?php echo $ped['estado'] === 'procesando' ? 'selected' : ''; ?>>Procesando</option>
                                            <option value="enviado" <?php echo $ped['estado'] === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                            <option value="entregado" <?php echo $ped['estado'] === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                            <option value="cancelado" <?php echo $ped['estado'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                    </td>
                                    <td><?php echo htmlspecialchars($ped['metodo_pago']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                    <td>
                                        <button class="btn-action btn-view" onclick="viewOrder(<?php echo $ped['id']; ?>)">👁️</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CATEGORÍAS TAB -->
            <div class="admin-content" id="categorias">
                <div class="content-header">
                    <h2>📂 Gestión de Categorías</h2>
                    <button class="btn btn-primary" onclick="showAddCategoryModal()">➕ Nueva Categoría</button>
                </div>

                <div class="cards-grid">
                    <?php while ($cat = $categorias->fetch_assoc()): ?>
                        <div class="category-card">
                            <h3><?php echo htmlspecialchars($cat['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['descripcion']); ?></p>
                            <div class="card-actions">
                                <button class="btn-action btn-edit" onclick="editCategory(<?php echo $cat['id']; ?>)">✏️</button>
                                <button class="btn-action btn-delete" onclick="deleteCategory(<?php echo $cat['id']; ?>)">🗑️</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- EQUIPOS TAB -->
            <div class="admin-content" id="equipos">
                <div class="content-header">
                    <h2>⚽ Gestión de Equipos</h2>
                    <button class="btn btn-primary" onclick="showAddTeamModal()">➕ Nuevo Equipo</button>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>País</th>
                                <th>Liga</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($eq = $equipos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $eq['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($eq['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($eq['pais']); ?></td>
                                    <td><?php echo htmlspecialchars($eq['liga']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $eq['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $eq['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-edit" onclick="editTeam(<?php echo $eq['id']; ?>)">✏️</button>
                                        <button class="btn-action btn-delete" onclick="deleteTeam(<?php echo $eq['id']; ?>)">🗑️</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>
    <script src="admin.js"></script>
</body>
</html>