<?php
require_once '../config.php';

// Si ya está logueado, redirigir a la tienda
if (isLoggedIn()) {
    redirect('../tienda/tienda.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telefono = cleanInput($_POST['telefono']);
    $direccion = cleanInput($_POST['direccion']);
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar si el email ya existe
        $checkSql = "SELECT id FROM usuarios WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Este correo electrónico ya está registrado';
        } else {
            // Insertar nuevo usuario
            $hashedPassword = hashPassword($password);
            $insertSql = "INSERT INTO usuarios (nombre, apellido, email, password, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssss", $nombre, $apellido, $email, $hashedPassword, $telefono, $direccion);
            
            if ($insertStmt->execute()) {
                $success = 'Registro exitoso. Redirigiendo a inicio de sesión...';
                header("refresh:2;url=../login/login.php");
            } else {
                $error = 'Error al registrar usuario. Intenta nuevamente';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="register.css">
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-box">
            <div class="logo-section">
                <div class="logo-icon">⚽</div>
                <h1>Camisetas de Fútbol</h1>
                <p>Crea tu cuenta</p>
            </div>
            
            <h2>Registro de Usuario</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div id="register-form" class="register-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">
                            <span class="label-icon">👤</span>
                            Nombre *
                        </label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                               placeholder="Juan">
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">
                            <span class="label-icon">👤</span>
                            Apellido *
                        </label>
                        <input type="text" id="apellido" name="apellido" required
                               value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                               placeholder="Pérez">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <span class="label-icon">📧</span>
                        Correo Electrónico *
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="tu@email.com">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <span class="label-icon">🔒</span>
                            Contraseña *
                        </label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Mínimo 6 caracteres">
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <span class="label-icon">🔒</span>
                            Confirmar Contraseña *
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Repite tu contraseña">
                        <div class="password-match" id="password-match"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="telefono">
                        <span class="label-icon">📱</span>
                        Teléfono
                    </label>
                    <input type="tel" id="telefono" name="telefono"
                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                           placeholder="+57 300 123 4567">
                </div>
                
                <div class="form-group">
                    <label for="direccion">
                        <span class="label-icon">📍</span>
                        Dirección
                    </label>
                    <textarea id="direccion" name="direccion" rows="2" 
                              placeholder="Calle 123 #45-67"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                </div>
                
                <button type="button" id="btn-register" class="btn btn-primary btn-block">
                    Crear Cuenta
                </button>
            </div>
            
            <div class="register-footer">
                <p>¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

    <script src="register.js"></script>
</body>
</html>