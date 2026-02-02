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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS del nuevo diseño -->
    <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
    <!-- Elementos decorativos del login 2 -->
    <div class="wave-container">
        <div class="wave wave-1"></div>
        <div class="wave wave-2"></div>
        <div class="wave wave-3"></div>
        <div class="wave wave-4"></div>
    </div>

    <div class="gradient-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Contenedor principal del login 1 -->
    <div class="login-container">
        <div class="login-card"> <!-- Clase del login 2 -->
            <div class="card-glow"></div> <!-- Elemento decorativo -->
            
            <!-- Logo y título del login 1 -->
            <div class="logo-section">
                <div class="gradient-icon"> <!-- Icono mejorado -->
                    <div class="icon-wave"></div>
                    <div class="logo-icon">⚽</div>
                </div>
                <h1>Sports Club</h1>
                <p>Tienda Deportiva</p>
            </div>
            
            <h2>Inicia Sesión</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <!-- Icono del login 2 -->
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['logout_message'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo $_SESSION['logout_message']; unset($_SESSION['logout_message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario del login 1 con mejoras visuales -->
            <div class="login-form">
                <div class="Usuario"> <!-- Estructura del login 2 -->
                    <div class="icono-usuario">
                        <i class="fas fa-envelope"></i> <!-- Icono para email -->
                    </div>
                    <div class="campo">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder=" "
                            aria-label="Email"
                            autofocus
                        />
                        <label for="email">Email</label>
                    </div>
                </div>
                
                <div class="Usuario">
                    <div class="icono-usuario">
                        <i class="fas fa-lock"></i> <!-- Icono del login 2 -->
                    </div>
                    <div class="campo">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder=" "
                            aria-label="Contraseña"
                        />
                        <label for="password">Contraseña</label>
                    </div>
                </div>
                
                <!-- Botón mejorado del login 2 -->
                <button type="button" id="btn-login" class="gradient-button">
                    <div class="button-bg"></div>
                    <div class="button-content">
                        <span class="btn-text">Ingresar</span>
                        <div class="btn-loader">
                            <div class="loader-wave"></div>
                            <div class="loader-wave"></div>
                            <div class="loader-wave"></div>
                        </div>
                    </div>
                    <div class="button-ripple"></div>
                </button>
            </div>
            
            <!-- Separador y login social del login 2 -->
            <div class="divider">
                <div class="divider-line">
                    <div class="line-gradient"></div>
                </div>
                <span>o continuar con</span>
                <div class="divider-line">
                    <div class="line-gradient"></div>
                </div>
            </div>

            <div class="social-login">
                <button type="button" class="social-btn">
                    <div class="social-bg google-bg"></div>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </button>
                <button type="button" class="social-btn">
                    <div class="social-bg facebook-bg"></div>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
                <button type="button" class="social-btn">
                    <div class="social-bg apple-bg"></div>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                </button>
            </div>

            <!-- Mensaje de éxito (oculto por defecto) -->
            <div class="success-message" id="successMessage" style="display: none;">
                <div class="success-wave"></div>
                <div class="success-content">
                    <div class="success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <h3>¡Bienvenido de nuevo!</h3>
                    <p>Redirigiendo al panel...</p>
                </div>
            </div>
            
            <!-- Footer del login 1 -->
            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="../register/register.php">Regístrate aquí</a></p>
            </div>
        </div>
    </div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="login.js"></script>
</body>
</html>