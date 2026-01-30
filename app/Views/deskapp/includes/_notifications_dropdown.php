<!-- Dropdown de Notificaciones -->
<li class="dropdown">
    <a class="dropdown-toggle no-arrow" href="javascript:;" role="button" data-toggle="dropdown" id="notificationDropdown">
        <i class="dw dw-notification"></i>
        <span class="badge notification-badge badge-pill badge-danger" id="notification-count" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right notifications-menu">
        <div class="notification-header">
            <h5 class="mb-0">Notificaciones</h5>
            <a href="javascript:void(0);" class="mark-all-read" id="markAllRead">
                <small>Marcar todas como leídas</small>
            </a>
        </div>
        <div class="notification-list customscroll" id="notificationList">
            <!-- Las notificaciones se cargarán aquí dinámicamente -->
            <div class="text-center py-3">
                <i class="icon-copy fa fa-spinner fa-spin"></i>
                <p class="mt-2">Cargando...</p>
            </div>
        </div>
        <div class="notification-footer">
            <a href="<?= base_url('deskapp/notifications') ?>">Ver todas las notificaciones</a>
        </div>
    </div>
</li>

<style>
/* Estilos para el dropdown de notificaciones */
.notifications-menu {
    width: 380px;
    max-width: 90vw;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    border: none;
    border-radius: 8px;
    padding: 0;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.notification-header h5 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.mark-all-read {
    color: #1b00ff;
    font-size: 12px;
    text-decoration: none;
}

.mark-all-read:hover {
    text-decoration: underline;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
    padding: 0;
}

.notification-item {
    display: flex;
    padding: 15px 20px;
    border-bottom: 1px solid #f1f1f1;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #1b00ff;
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 18px;
    color: white;
}

.notification-icon.bg-info { background-color: #17a2b8; }
.notification-icon.bg-success { background-color: #28a745; }
.notification-icon.bg-warning { background-color: #ffc107; color: #333; }
.notification-icon.bg-danger { background-color: #dc3545; }
.notification-icon.bg-primary { background-color: #1b00ff; }

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.notification-message {
    font-size: 13px;
    color: #666;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-time {
    font-size: 11px;
    color: #999;
}

.notification-footer {
    padding: 12px 20px;
    text-align: center;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
}

.notification-footer a {
    color: #1b00ff;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
}

.notification-footer a:hover {
    text-decoration: underline;
}

.notification-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    min-width: 18px;
    height: 18px;
    padding: 2px 5px;
    font-size: 10px;
    line-height: 14px;
    border-radius: 9px;
}

.no-notifications {
    padding: 40px 20px;
    text-align: center;
    color: #999;
}

.no-notifications i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 10px;
}

/* Animación de entrada */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-item {
    animation: slideDown 0.3s ease-out;
}
</style>

<script>
// Manejo de notificaciones
(function() {
    let notificationCheckInterval;
    const REFRESH_INTERVAL = 60000; // 1 minuto

    // Cargar notificaciones al iniciar
    function loadNotifications() {
        fetch('<?= base_url('deskapp/notifications/api_unread') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.count);
                    renderNotifications(data.notifications);
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
    }

    // Actualizar badge contador
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notification-count');
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // Renderizar notificaciones
    function renderNotifications(notifications) {
        const container = document.getElementById('notificationList');
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="no-notifications">
                    <i class="icon-copy fa fa-bell-slash"></i>
                    <p>No tienes notificaciones</p>
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(n => `
            <div class="notification-item ${n.is_read == 0 ? 'unread' : ''}" 
                 data-id="${n.id}" 
                 data-url="${n.url || '#'}"
                 title="${escapeHtml(n.message)}">
                <div class="notification-icon bg-${n.color}">
                    <i class="icon-copy ${n.icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(n.title)}</div>
                    <div class="notification-message">${escapeHtml(n.message)}</div>
                    <div class="notification-time">${formatTime(n.created_at)}</div>
                </div>
            </div>
        `).join('');

        // Agregar event listeners a las notificaciones
        container.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                const url = this.dataset.url;
                markAsRead(id, url);
            });
        });
    }

    // Marcar como leída
    function markAsRead(notificationId, redirectUrl) {
        fetch(`<?= base_url('deskapp/notifications/api_mark_read') ?>/${notificationId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar el contador
                loadNotifications();
                
                // Redirigir si hay URL
                if (redirectUrl && redirectUrl !== '#') {
                    window.location.href = redirectUrl;
                }
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    // Marcar todas como leídas
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.preventDefault();
        fetch('<?= base_url('deskapp/notifications/api_mark_all_read') ?>', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    });

    // Formatear tiempo relativo
    function formatTime(datetime) {
        const date = new Date(datetime);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // diferencia en segundos

        if (diff < 60) return 'Justo ahora';
        if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
        if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;
        
        return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
    }

    // Escape HTML para prevenir XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        loadNotifications();
        
        // Auto-refresh cada minuto
        notificationCheckInterval = setInterval(loadNotifications, REFRESH_INTERVAL);
    });

    // Limpiar intervalo al salir
    window.addEventListener('beforeunload', function() {
        if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
        }
    });
})();
</script>
