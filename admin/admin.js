// Cambiar tabs
function showAdminTab(tabId) {
    // Ocultar todos los tabs
    document.querySelectorAll('.admin-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    document.getElementById(tabId).classList.add('active');
    
    // Actualizar botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Actualizar estado de pedido
function updateOrderStatus(orderId, newStatus) {
    const formData = new FormData();
    formData.append('pedido_id', orderId);
    formData.append('estado', newStatus);
    
    fetch('../ajax/actualizar_pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Estado actualizado correctamente');
        } else {
            showNotification(data.message || 'Error al actualizar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al actualizar estado', 'error');
    });
}

// Ver detalles de pedido
function viewOrder(orderId) {
    // Aquí puedes implementar un modal o redirigir a una página de detalles
    showNotification('Ver pedido #' + orderId);
    // window.location.href = 'ver_pedido.php?id=' + orderId;
}

// Productos
function showAddProductModal() {
    showNotification('Función de agregar producto en desarrollo');
    // Aquí implementarías un modal para agregar productos
}

function editProduct(productId) {
    showNotification('Editar producto #' + productId);
    // window.location.href = 'editar_producto.php?id=' + productId;
}

function deleteProduct(productId) {
    if (!confirm('¿Estás seguro de eliminar este producto?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('producto_id', productId);
    
    fetch('../ajax/eliminar_producto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Producto eliminado');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error al eliminar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar producto', 'error');
    });
}

// Usuarios
function editUser(userId) {
    showNotification('Editar usuario #' + userId);
}

function toggleUserStatus(userId) {
    if (!confirm('¿Cambiar el estado de este usuario?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('usuario_id', userId);
    
    fetch('../ajax/toggle_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Estado actualizado');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al cambiar estado', 'error');
    });
}

// Categorías
function showAddCategoryModal() {
    const nombre = prompt('Nombre de la categoría:');
    if (!nombre) return;
    
    const descripcion = prompt('Descripción:');
    
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('descripcion', descripcion);
    
    fetch('../ajax/crear_categoria.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Categoría creada');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    });
}

function editCategory(categoryId) {
    showNotification('Editar categoría #' + categoryId);
}

function deleteCategory(categoryId) {
    if (!confirm('¿Eliminar esta categoría?')) {
        return;
    }
    showNotification('Eliminar categoría #' + categoryId);
}

// Equipos
function showAddTeamModal() {
    const nombre = prompt('Nombre del equipo:');
    if (!nombre) return;
    
    const pais = prompt('País:');
    const liga = prompt('Liga:');
    
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('pais', pais);
    formData.append('liga', liga);
    
    fetch('../ajax/crear_equipo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Equipo creado');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error', 'error');
        }
    });
}

function editTeam(teamId) {
    showNotification('Editar equipo #' + teamId);
}

function deleteTeam(teamId) {
    if (!confirm('¿Eliminar este equipo?')) {
        return;
    }
    showNotification('Eliminar equipo #' + teamId);
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

// Confirmación antes de salir si hay cambios sin guardar
window.addEventListener('beforeunload', function(e) {
    const selects = document.querySelectorAll('.status-select');
    let hasChanges = false;
    
    selects.forEach(select => {
        if (select.dataset.original && select.value !== select.dataset.original) {
            hasChanges = true;
        }
    });
    
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Guardar valor original de selects
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.dataset.original = select.value;
    });
});