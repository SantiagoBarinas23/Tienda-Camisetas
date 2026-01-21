// Variables globales
let tallaSeleccionada = 'XS';
let cantidadSeleccionada = 1;

// Cambiar imagen principal
function changeImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Actualizar thumbnail activo
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) {
            thumb.classList.add('active');
        }
    });
}

// Selector de talla
document.querySelectorAll('.talla-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        tallaSeleccionada = this.getAttribute('data-talla');
    });
});

// Cantidad
function increaseQuantity(max) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value);
    if (value < max) {
        input.value = value + 1;
        cantidadSeleccionada = value + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value);
    if (value > 1) {
        input.value = value - 1;
        cantidadSeleccionada = value - 1;
    }
}

// Agregar al carrito
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Agregando...';
    
    fetch('../ajax/check_login.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Debes iniciar sesión para agregar productos', 'error');
                setTimeout(() => {
                    window.location.href = '../login/login.php';
                }, 1500);
                return;
            }
            
            const formData = new FormData();
            formData.append('producto_id', productoId);
            formData.append('cantidad', cantidadSeleccionada);
            formData.append('talla', tallaSeleccionada);
            
            fetch('../ajax/agregar_carrito.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${productoNombre} agregado al carrito ✓`);
                    updateCartBadge();
                    
                    // Animación del botón
                    btn.textContent = '✓ Agregado';
                    btn.style.background = '#2ecc71';
                    setTimeout(() => {
                        btn.textContent = '🛒 Agregar al carrito';
                        btn.style.background = '';
                        btn.disabled = false;
                    }, 2000);
                } else {
                    showNotification(data.message || 'Error al agregar', 'error');
                    btn.disabled = false;
                    btn.textContent = '🛒 Agregar al carrito';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al agregar producto', 'error');
                btn.disabled = false;
                btn.textContent = '🛒 Agregar al carrito';
            });
        });
});

// Favoritos
document.getElementById('favoriteBtn')?.addEventListener('click', function() {
    const btn = this;
    const esFavorito = btn.getAttribute('data-favorito') === '1';
    
    fetch('../ajax/check_login.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Debes iniciar sesión', 'error');
                setTimeout(() => {
                    window.location.href = '../login/login.php';
                }, 1500);
                return;
            }
            
            const formData = new FormData();
            formData.append('producto_id', productoId);
            formData.append('accion', esFavorito ? 'eliminar' : 'agregar');
            
            fetch('../ajax/agregar_favorito.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (esFavorito) {
                        btn.textContent = '🤍';
                        btn.setAttribute('data-favorito', '0');
                        showNotification('Eliminado de favoritos');
                    } else {
                        btn.textContent = '❤️';
                        btn.setAttribute('data-favorito', '1');
                        showNotification('Agregado a favoritos');
                    }
                } else {
                    showNotification(data.message || 'Error', 'error');
                }
            });
        });
});

// Actualizar badge del carrito
function updateCartBadge() {
    fetch('../ajax/obtener_carrito.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('cartBadge');
                if (badge) {
                    const total = data.items.reduce((sum, item) => sum + item.cantidad, 0);
                    badge.textContent = total;
                }
            }
        })
        .catch(error => console.error('Error:', error));
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

// Zoom en imagen (opcional)
const mainImage = document.getElementById('mainImage');
if (mainImage) {
    mainImage.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.2)';
        this.style.transition = 'transform 0.3s';
    });
    
    mainImage.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
}

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const productInfo = document.querySelector('.product-info');
    if (productInfo) {
        productInfo.style.opacity = '0';
        productInfo.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            productInfo.style.transition = 'all 0.5s ease';
            productInfo.style.opacity = '1';
            productInfo.style.transform = 'translateY(0)';
        }, 200);
    }
});