// Actualizar cantidad
function updateQuantity(cartId, change, maxStock) {
    const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
    let currentValue = parseInt(input.value);
    let newValue = currentValue + change;
    
    if (newValue < 1 || newValue > maxStock) {
        if (newValue > maxStock) {
            showNotification('Stock insuficiente', 'error');
        }
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('cantidad', newValue);
    
    fetch('../ajax/actualizar_cantidad.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Error al actualizar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al actualizar cantidad', 'error');
    });
}

// Eliminar item
function removeItem(cartId) {
    if (!confirm('¿Eliminar este producto del carrito?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    
    fetch('../ajax/eliminar_carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Producto eliminado');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error al eliminar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar producto', 'error');
    });
}

// Vaciar carrito
function clearCart() {
    if (!confirm('¿Estás seguro de vaciar todo el carrito?')) {
        return;
    }
    
    fetch('../ajax/vaciar_carrito.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Carrito vaciado');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error al vaciar carrito', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al vaciar carrito', 'error');
    });
}

// Proceder al checkout
function proceedToCheckout() {
    // Aquí puedes implementar la lógica del checkout
    showNotification('Redirigiendo al checkout...');
    setTimeout(() => {
        // Redirigir a página de checkout (cuando la implementes)
        alert('Función de checkout en desarrollo.\nPor ahora puedes:\n1. Revisar tu pedido\n2. Agregar más productos\n3. Modificar cantidades');
    }, 1500);
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

// Animaciones de entrada
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.cart-item');
    items.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
});