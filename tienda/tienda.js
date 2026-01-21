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

// Actualizar contador del carrito
function updateCartBadge() {
    fetch('../ajax/obtener_carrito.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('cartBadge');
                const total = data.items.reduce((sum, item) => sum + item.cantidad, 0);
                badge.textContent = total;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Agregar al carrito
function addToCart(productId, productName) {
    // Verificar si el usuario está logueado
    fetch('../ajax/check_login.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Debes iniciar sesión para agregar productos al carrito', 'error');
                setTimeout(() => {
                    window.location.href = '../login/login.php';
                }, 1500);
                return;
            }

            // Agregar al carrito
            const formData = new FormData();
            formData.append('producto_id', productId);
            formData.append('cantidad', 1);

            fetch('../ajax/agregar_carrito.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${productName} agregado al carrito ✓`);
                    updateCartBadge();
                } else {
                    showNotification(data.message || 'Error al agregar producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al agregar producto', 'error');
            });
        });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Botones agregar al carrito
    const addButtons = document.querySelectorAll('.add-to-cart');
    addButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            addToCart(productId, productName);
        });
    });

    // Auto-submit de filtros con delay
    let filterTimeout;
    const searchInput = document.getElementById('busqueda');
    const categorySelect = document.getElementById('categoria');
    const orderSelect = document.getElementById('orden');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                document.getElementById('filtersForm').submit();
            }, 500);
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    }

    if (orderSelect) {
        orderSelect.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    }

    // Animación de entrada de las tarjetas
    const cards = document.querySelectorAll('.product-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Prevenir envío duplicado del formulario
let formSubmitting = false;
document.getElementById('filtersForm').addEventListener('submit', function(e) {
    if (formSubmitting) {
        e.preventDefault();
        return false;
    }
    formSubmitting = true;
});