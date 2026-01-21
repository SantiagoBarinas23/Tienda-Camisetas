// Cambiar tabs
function showTab(tabId) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    document.getElementById(tabId).classList.add('active');
    
    // Actualizar menú
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Eliminar favorito
function removeFavorite(productoId) {
    if (!confirm('¿Eliminar de favoritos?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('producto_id', productoId);
    formData.append('accion', 'eliminar');
    
    fetch('../ajax/agregar_favorito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Eliminado de favoritos');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar', 'error');
    });
}

// Sistema de notificaciones
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = 'notification show';
    
    if (type === 'error') {
        notification.classList.add('error');
    }
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Validación de contraseña
document.querySelector('.password-form')?.addEventListener('submit', function(e) {
    const newPassword = document.querySelector('input[name="new_password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showNotification('Las contraseñas no coinciden', 'error');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
        return false;
    }
});

// Auto-ocultar alertas
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);