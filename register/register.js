// ========================================
// JAVASCRIPT COMPLETO DE REGISTRO
// TODO EN UN SOLO ARCHIVO - SIN DEPENDENCIAS
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const btnRegister = document.getElementById('btn-register');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const emailInput = document.getElementById('email');
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    
    // Evento de click en botón de registro
    btnRegister.addEventListener('click', function() {
        validarYEnviarRegistro();
    });
    
    // Validar fortaleza de contraseña en tiempo real
    passwordInput.addEventListener('input', function() {
        validarFortalezaPassword(this.value);
        verificarCoincidenciaPassword();
    });
    
    // Verificar coincidencia de contraseñas
    confirmPasswordInput.addEventListener('input', function() {
        verificarCoincidenciaPassword();
    });
    
    // Validar email en tiempo real
    emailInput.addEventListener('blur', function() {
        validarEmailEnTiempoReal(this);
    });
    
    // Capitalizar nombre y apellido automáticamente
    nombreInput.addEventListener('blur', function() {
        this.value = capitalizarTexto(this.value);
    });
    
    apellidoInput.addEventListener('blur', function() {
        this.value = capitalizarTexto(this.value);
    });
    
    // Permitir registro con Enter en el último campo
    confirmPasswordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            validarYEnviarRegistro();
        }
    });
    
    // Animación de entrada para campos
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            group.style.transition = 'all 0.4s ease';
            group.style.opacity = '1';
            group.style.transform = 'translateX(0)';
        }, index * 50);
    });
});

// Función para validar fortaleza de contraseña
function validarFortalezaPassword(password) {
    const strengthDiv = document.getElementById('password-strength');
    const passwordInput = document.getElementById('password');
    
    if (password.length === 0) {
        strengthDiv.className = 'password-strength';
        passwordInput.classList.remove('valid', 'invalid');
        return;
    }
    
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
    
    if (strength <= 2) {
        strengthDiv.className = 'password-strength weak';
        passwordInput.classList.remove('valid');
        passwordInput.classList.add('invalid');
    } else if (strength <= 3) {
        strengthDiv.className = 'password-strength medium';
        passwordInput.classList.remove('invalid');
        passwordInput.classList.add('valid');
    } else {
        strengthDiv.className = 'password-strength strong';
        passwordInput.classList.remove('invalid');
        passwordInput.classList.add('valid');
    }
}

// Función para verificar coincidencia de contraseñas
function verificarCoincidenciaPassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('password-match');
    const confirmInput = document.getElementById('confirm_password');
    
    if (confirmPassword.length === 0) {
        matchDiv.textContent = '';
        matchDiv.className = 'password-match';
        confirmInput.classList.remove('valid', 'invalid');
        return;
    }
    
    if (password === confirmPassword) {
        matchDiv.textContent = '✓ Las contraseñas coinciden';
        matchDiv.className = 'password-match match';
        confirmInput.classList.remove('invalid');
        confirmInput.classList.add('valid');
        return true;
    } else {
        matchDiv.textContent = '✗ Las contraseñas no coinciden';
        matchDiv.className = 'password-match no-match';
        confirmInput.classList.remove('valid');
        confirmInput.classList.add('invalid');
        return false;
    }
}

// Función para validar email en tiempo real
function validarEmailEnTiempoReal(input) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (input.value.length === 0) {
        input.classList.remove('valid', 'invalid');
        return;
    }
    
    if (regex.test(input.value)) {
        input.classList.remove('invalid');
        input.classList.add('valid');
    } else {
        input.classList.remove('valid');
        input.classList.add('invalid');
    }
}

// Función para capitalizar texto
function capitalizarTexto(texto) {
    return texto
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Función principal de validación y envío
function validarYEnviarRegistro() {
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const telefono = document.getElementById('telefono').value.trim();
    const direccion = document.getElementById('direccion').value.trim();
    
    // Limpiar mensajes de error anteriores
    const alertaAnterior = document.querySelector('.alert');
    if (alertaAnterior && !alertaAnterior.classList.contains('alert-success')) {
        alertaAnterior.remove();
    }
    
    // Validaciones
    if (!nombre || !apellido || !email || !password || !confirmPassword) {
        mostrarError('Por favor completa todos los campos obligatorios');
        return false;
    }
    
    if (nombre.length < 2) {
        mostrarError('El nombre debe tener al menos 2 caracteres');
        document.getElementById('nombre').focus();
        return false;
    }
    
    if (apellido.length < 2) {
        mostrarError('El apellido debe tener al menos 2 caracteres');
        document.getElementById('apellido').focus();
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarError('Por favor ingresa un correo electrónico válido');
        document.getElementById('email').focus();
        return false;
    }
    
    if (password.length < 6) {
        mostrarError('La contraseña debe tener al menos 6 caracteres');
        document.getElementById('password').focus();
        return false;
    }
    
    if (password !== confirmPassword) {
        mostrarError('Las contraseñas no coinciden');
        document.getElementById('confirm_password').focus();
        return false;
    }
    
    if (telefono && !/^[\d\s\+\-\(\)]+$/.test(telefono)) {
        mostrarError('El teléfono contiene caracteres no válidos');
        document.getElementById('telefono').focus();
        return false;
    }
    
    // Si todo está bien, enviar el formulario
    enviarFormularioRegistro({
        nombre,
        apellido,
        email,
        password,
        confirm_password: confirmPassword,
        telefono,
        direccion
    });
}

// Función para enviar formulario
function enviarFormularioRegistro(datos) {
    const btnRegister = document.getElementById('btn-register');
    const textoOriginal = btnRegister.textContent;
    
    btnRegister.disabled = true;
    btnRegister.textContent = 'Creando cuenta...';
    btnRegister.style.cursor = 'wait';
    
    const formData = new FormData();
    Object.keys(datos).forEach(key => {
        formData.append(key, datos[key]);
    });
    
    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        if (html.includes('alert-error')) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const errorDiv = doc.querySelector('.alert-error');
            if (errorDiv) {
                mostrarError(errorDiv.textContent.trim());
            }
            
            btnRegister.disabled = false;
            btnRegister.textContent = textoOriginal;
            btnRegister.style.cursor = 'pointer';
        } else if (html.includes('alert-success')) {
            mostrarNotificacion('¡Cuenta creada exitosamente! Redirigiendo...', 'success');
            
            document.querySelectorAll('#register-form input, #register-form textarea').forEach(el => {
                el.disabled = true;
            });
            
            setTimeout(() => {
                window.location.href = '../login/login.php';
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión. Intenta nuevamente.');
        
        btnRegister.disabled = false;
        btnRegister.textContent = textoOriginal;
        btnRegister.style.cursor = 'pointer';
    });
}

// Función para mostrar error
function mostrarError(mensaje) {
    const alertaAnterior = document.querySelector('.alert-error');
    if (alertaAnterior) {
        alertaAnterior.remove();
    }
    
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-error';
    alerta.textContent = mensaje;
    alerta.style.animation = 'fadeIn 0.3s ease';
    
    const registerForm = document.querySelector('.register-form');
    registerForm.parentNode.insertBefore(alerta, registerForm);
    
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