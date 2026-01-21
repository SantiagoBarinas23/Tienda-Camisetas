<?php
require_once '../config.php';

// Si ya está logueado, redirigir a la tienda
if (isLoggedIn()) {
    redirect('../tienda/tienda.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    // Consultar usuario
    $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Verificar contraseña
        if (verifyPassword($password, $usuario['password'])) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            
            // Actualizar última conexión
            $updateSql = "UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $usuario['id']);
            $updateStmt->execute();
            
            // Redirigir a la tienda
            redirect('../tienda/tienda.php');
        } else {
            $error = 'Contraseña incorrecta';
        }
    } else {
        $error = 'Usuario no encontrado';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo-section">
                <div class="logo-icon">⚽</div>
                <h1>Camisetas de Fútbol</h1>
                <p>Tu tienda de camisetas deportivas</p>
            </div>
            
            <h2>Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="login-form">
                <div class="form-group">
                    <label for="email">
                        <span class="label-icon">📧</span>
                        Correo Electrónico
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="********">
                </div>
                
                <button type="button" id="btn-login" class="btn btn-primary btn-block">
                    Ingresar
                </button>
            </div>
            
            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="../register/register.php">Regístrate aquí</a></p>
            </div>
            
            <div class="demo-users">
                <p class="demo-title">👤 Usuarios de prueba:</p>
                <div class="demo-user">
                    <strong>Admin:</strong> admin@camisetasfutbol.com / admin123
                </div>
                <div class="demo-user">
                    <strong>Cliente:</strong> juan@email.com / cliente123
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>