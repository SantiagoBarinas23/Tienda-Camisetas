// ========================================
// JAVASCRIPT COMPLETO DE LOGIN
// TODO EN UN SOLO ARCHIVO - SIN DEPENDENCIAS
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const btnLogin = document.getElementById('btn-login');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Evento de click en botón de login
    btnLogin.addEventListener('click', function() {
        validarYEnviarLogin();
    });
    
    // Permitir login con Enter
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            validarYEnviarLogin();
        }
    });
    
    passwordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            validarYEnviarLogin();
        }
    });
    
    // Limpiar bordes al escribir
    emailInput.addEventListener('input', function() {
        this.style.borderColor = '#e5e7eb';
    });
    
    passwordInput.addEventListener('input', function() {
        this.style.borderColor = '#e5e7eb';
    });
    
    // Funcionalidad de autocompletar demo
    const demoUsers = document.querySelectorAll('.demo-user');
    demoUsers.forEach((user, index) => {
        user.style.cursor = 'pointer';
        user.title = 'Click para autocompletar';
        user.addEventListener('click', function() {
            autocompletarDemo(index === 0 ? 'admin' : 'cliente');
        });
    });
});

// Función para validar y enviar login
function validarYEnviarLogin() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    
    // Limpiar mensajes de error anteriores
    const alertaAnterior = document.querySelector('.alert');
    if (alertaAnterior) {
        alertaAnterior.remove();
    }
    
    // Validaciones
    if (!email || !password) {
        mostrarError('Por favor completa todos los campos');
        return false;
    }
    
    if (!validarEmail(email)) {
        mostrarError('Por favor ingresa un correo electrónico válido');
        document.getElementById('email').style.borderColor = '#ef4444';
        return false;
    }
    
    if (password.length < 6) {
        mostrarError('La contraseña debe tener al menos 6 caracteres');
        document.getElementById('password').style.borderColor = '#ef4444';
        return false;
    }
    
    // Si todo está bien, enviar el formulario
    enviarFormularioLogin(email, password);
}

// Función para validar formato de email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para enviar formulario por AJAX
function enviarFormularioLogin(email, password) {
    const btnLogin = document.getElementById('btn-login');
    const textoOriginal = btnLogin.textContent;
    
    // Deshabilitar botón y mostrar loading
    btnLogin.disabled = true;
    btnLogin.textContent = 'Ingresando...';
    btnLogin.style.cursor = 'wait';
    
    // Crear FormData
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    
    // Enviar petición
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Si hay error en la respuesta, mostrarla
        if (html.includes('alert-error')) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const errorDiv = doc.querySelector('.alert-error');
            if (errorDiv) {
                mostrarError(errorDiv.textContent.trim());
            }
            
            btnLogin.disabled = false;
            btnLogin.textContent = textoOriginal;
            btnLogin.style.cursor = 'pointer';
        } else {
            mostrarNotificacion('Inicio de sesión exitoso. Redirigiendo...', 'success');
            
            setTimeout(() => {
                window.location.href = '../tienda/tienda.php';
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión. Intenta nuevamente.');
        
        btnLogin.disabled = false;
        btnLogin.textContent = textoOriginal;
        btnLogin.style.cursor = 'pointer';
    });
}

// Función para mostrar error
function mostrarError(mensaje) {
    const alertaAnterior = document.querySelector('.alert');
    if (alertaAnterior) {
        alertaAnterior.remove();
    }
    
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-error';
    alerta.textContent = mensaje;
    alerta.style.animation = 'fadeIn 0.3s ease';
    
    const loginForm = document.querySelector('.login-form');
    loginForm.parentNode.insertBefore(alerta, loginForm);
    
    setTimeout(() => {
        alerta.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => alerta.remove(), 300);
    }, 5000);
}

// Función para mostrar notificación
function mostrarNotificacion(mensaje, tipo) {
    const notificacion = document.createElement('div');
    notificacion.textContent = mensaje;
    
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.right = '20px';
    notificacion.style.padding = '1rem 1.5rem';
    notificacion.style.borderRadius = '8px';
    notificacion.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notificacion.style.zIndex = '9999';
    notificacion.style.fontWeight = '500';
    notificacion.style.animation = 'slideIn 0.3s ease';
    
    if (tipo === 'success') {
        notificacion.style.backgroundColor = '#10b981';
        notificacion.style.color = 'white';
    } else if (tipo === 'error') {
        notificacion.style.backgroundColor = '#ef4444';
        notificacion.style.color = 'white';
    }
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}

// Función para autocompletar usuario demo
function autocompletarDemo(tipo) {
    if (tipo === 'admin') {
        document.getElementById('email').value = 'admin@camisetasfutbol.com';
        document.getElementById('password').value = 'admin123';
    } else {
        document.getElementById('email').value = 'juan@email.com';
        document.getElementById('password').value = 'cliente123';
    }
    mostrarNotificacion('Datos autocompletados. Haz clic en Ingresar', 'info');
}

// Agregar animaciones CSS dinámicamente
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);