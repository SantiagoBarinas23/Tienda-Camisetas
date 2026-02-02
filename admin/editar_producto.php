<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('../tienda/tienda.php');
}

$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

if ($producto_id <= 0) {
    redirect('admin.php');
}

// Obtener datos del producto
$sql = "SELECT * FROM productos WHERE id = $producto_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    redirect('admin.php');
}

$producto = $result->fetch_assoc();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $descripcion = cleanInput($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $precio_oferta = !empty($_POST['precio_oferta']) ? floatval($_POST['precio_oferta']) : null;
    $stock = intval($_POST['stock']);
    $talla = cleanInput($_POST['talla']);
    $temporada = cleanInput($_POST['temporada']);
    $equipo_id = intval($_POST['equipo_id']);
    $categoria_id = intval($_POST['categoria_id']);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    
    if (empty($nombre)) {
        $error = 'El nombre es requerido';
    } elseif ($precio <= 0) {
        $error = 'El precio debe ser mayor a 0';
    } elseif ($stock < 0) {
        $error = 'El stock no puede ser negativo';
    } else {
        $sql_update = "UPDATE productos SET 
                      nombre = '$nombre',
                      descripcion = '$descripcion',
                      precio = $precio,
                      stock = $stock,
                      talla = '$talla',
                      temporada = '$temporada',
                      equipo_id = $equipo_id,
                      categoria_id = $categoria_id,
                      destacado = $destacado";
        
        if ($precio_oferta !== null && $precio_oferta > 0) {
            $sql_update .= ", precio_oferta = $precio_oferta";
        } else {
            $sql_update .= ", precio_oferta = NULL";
        }
        
        $sql_update .= " WHERE id = $producto_id";
        
        if ($conn->query($sql_update)) {
            $success = '✓ Producto actualizado correctamente';
            // Recargar datos
            $result = $conn->query("SELECT * FROM productos WHERE id = $producto_id");
            $producto = $result->fetch_assoc();
        } else {
            $error = '✗ Error al actualizar producto: ' . $conn->error;
        }
    }
}

// Obtener categorías
$sql_cat = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias = $conn->query($sql_cat);

// Obtener equipos
$sql_eq = "SELECT * FROM equipos WHERE activo = 1 ORDER BY nombre";
$equipos = $conn->query($sql_eq);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .edit-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .edit-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .edit-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group-full {
            grid-column: 1 / -1;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin.php" class="back-link">← Volver al Panel Admin</a>
        
        <div class="edit-card">
            <div class="edit-header">
                <h1>✏️ Editar Producto</h1>
                <p>ID: #<?php echo $producto['id']; ?></p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="temporada">Temporada</label>
                        <input type="text" id="temporada" name="temporada" 
                               value="<?php echo htmlspecialchars($producto['temporada']); ?>"
                               placeholder="2024">
                    </div>
                </div>
                
                <div class="form-group form-group-full">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="categoria_id">Categoría *</label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccionar...</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $producto['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="equipo_id">Equipo *</label>
                        <select id="equipo_id" name="equipo_id" required>
                            <option value="">Seleccionar...</option>
                            <?php while ($eq = $equipos->fetch_assoc()): ?>
                                <option value="<?php echo $eq['id']; ?>"
                                        <?php echo $producto['equipo_id'] == $eq['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($eq['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="precio">Precio Regular ($) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0"
                               value="<?php echo $producto['precio']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_oferta">Precio Oferta ($)</label>
                        <input type="number" id="precio_oferta" name="precio_oferta" step="0.01" min="0"
                               value="<?php echo $producto['precio_oferta']; ?>"
                               placeholder="Dejar vacío si no hay oferta">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="stock">Stock *</label>
                        <input type="number" id="stock" name="stock" min="0"
                               value="<?php echo $producto['stock']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="talla">Talla</label>
                        <select id="talla" name="talla">
                            <option value="XS" <?php echo $producto['talla'] == 'XS' ? 'selected' : ''; ?>>XS</option>
                            <option value="S" <?php echo $producto['talla'] == 'S' ? 'selected' : ''; ?>>S</option>
                            <option value="M" <?php echo $producto['talla'] == 'M' ? 'selected' : ''; ?>>M</option>
                            <option value="L" <?php echo $producto['talla'] == 'L' ? 'selected' : ''; ?>>L</option>
                            <option value="XL" <?php echo $producto['talla'] == 'XL' ? 'selected' : ''; ?>>XL</option>
                            <option value="XXL" <?php echo $producto['talla'] == 'XXL' ? 'selected' : ''; ?>>XXL</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="destacado" name="destacado" 
                               <?php echo $producto['destacado'] ? 'checked' : ''; ?>>
                        <label for="destacado">⭐ Marcar como producto destacado</label>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar Cambios
                    </button>
                    <a href="admin.php" class="btn btn-secondary">
                        ✖ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>