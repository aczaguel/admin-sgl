<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<?= $this->section('content') ?>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Notificaciones</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Notificaciones</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <button class="btn btn-primary" id="markAllReadBtn">
                            <i class="icon-copy fa fa-check-double"></i> Marcar todas como leídas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-30">
                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark" id="unreadCount"><?= $unread_count ?></div>
                                <div class="font-14 text-secondary weight-500">Sin Leer</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-bell" style="font-size: 48px; color: #ff5370;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark" id="totalCount"><?= count($notifications) ?></div>
                                <div class="font-14 text-secondary weight-500">Total de Notificaciones</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-list" style="font-size: 48px; color: #00e091;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark" id="readCount"><?= count($notifications) - $unread_count ?></div>
                                <div class="font-14 text-secondary weight-500">Leídas</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-check-circle" style="font-size: 48px; color: #1b00ff;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Notificaciones -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Todas las Notificaciones</h4>
                </div>
                <div class="pb-20">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="icon-copy fa fa-bell-slash" style="font-size: 64px; color: #ddd;"></i>
                            <h5 class="mt-3 text-muted">No tienes notificaciones</h5>
                            <p class="text-muted">Las notificaciones aparecerán aquí cuando haya actividad</p>
                        </div>
                    <?php else: ?>
                        <div class="notification-timeline" id="notificationTimeline">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-card <?= $notification['is_read'] == 0 ? 'unread' : '' ?>" 
                                     data-id="<?= $notification['id'] ?>"
                                     data-url="<?= $notification['url'] ?>">
                                    <div class="notification-card-icon bg-<?= $notification['color'] ?>">
                                        <i class="icon-copy <?= $notification['icon'] ?>"></i>
                                    </div>
                                    <div class="notification-card-content">
                                        <div class="notification-card-header">
                                            <h5 class="notification-card-title"><?= esc($notification['title']) ?></h5>
                                            <span class="notification-card-time">
                                                <i class="icon-copy fa fa-clock"></i>
                                                <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                            </span>
                                        </div>
                                        <p class="notification-card-message"><?= esc($notification['message']) ?></p>
                                        <?php if ($notification['created_by_name']): ?>
                                            <small class="text-muted">
                                                <i class="icon-copy fa fa-user"></i> Por: <?= esc($notification['created_by_name']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <div class="notification-card-actions mt-2">
                                            <?php if ($notification['is_read'] == 0): ?>
                                                <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                                                        data-id="<?= $notification['id'] ?>">
                                                    <i class="icon-copy fa fa-check"></i> Marcar como leída
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($notification['url']): ?>
                                                <a href="<?= $notification['url'] ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="icon-copy fa fa-external-link-alt"></i> Ver Detalles
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-id="<?= $notification['id'] ?>">
                                                <i class="icon-copy fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center py-5" id="emptyNotifications" style="display:none;">
                            <i class="icon-copy fa fa-bell-slash" style="font-size: 64px; color: #ddd;"></i>
                            <h5 class="mt-3 text-muted">No tienes notificaciones</h5>
                            <p class="text-muted">Las notificaciones aparecerán aquí cuando haya actividad</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.notification-timeline {
    padding: 0 20px;
}

.notification-card {
    display: flex;
    padding: 20px;
    margin-bottom: 15px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s;
}

.notification-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.notification-card.unread {
    background: #e3f2fd;
    border-left: 4px solid #1b00ff;
}

.notification-card-icon {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 22px;
    color: white;
}

.notification-card-icon.bg-info { background-color: #17a2b8; }
.notification-card-icon.bg-success { background-color: #28a745; }
.notification-card-icon.bg-warning { background-color: #ffc107; color: #333; }
.notification-card-icon.bg-danger { background-color: #dc3545; }
.notification-card-icon.bg-primary { background-color: #1b00ff; }

.notification-card-content {
    flex: 1;
    min-width: 0;
}

.notification-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 8px;
}

.notification-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.notification-card-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.notification-card-message {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
    line-height: 1.5;
}

.notification-card-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>

<script>
$(document).ready(function() {
    const $unreadCount = $('#unreadCount');
    const $readCount = $('#readCount');
    const $totalCount = $('#totalCount');
    const $timeline = $('#notificationTimeline');
    const $emptyState = $('#emptyNotifications');

    const getCount = ($el) => parseInt($el.text(), 10) || 0;
    const setCount = ($el, value) => $el.text(Math.max(0, value));

    const updateCounts = ({ unreadDelta = 0, readDelta = 0, totalDelta = 0 }) => {
        if ($unreadCount.length) setCount($unreadCount, getCount($unreadCount) + unreadDelta);
        if ($readCount.length) setCount($readCount, getCount($readCount) + readDelta);
        if ($totalCount.length) setCount($totalCount, getCount($totalCount) + totalDelta);
    };

    const showEmptyStateIfNeeded = () => {
        if ($timeline.length && $timeline.find('.notification-card').length === 0) {
            $emptyState.show();
        }
    };

    // Marcar todas como leídas
    $('#markAllReadBtn').click(function() {
        if (confirm('¿Marcar todas las notificaciones como leídas?')) {
            $.post('<?= site_url('deskapp/notifications/api_mark_all_read') ?>', function(response) {
                if (response.success) {
                    const $unreadCards = $('.notification-card.unread');
                    const unreadCount = $unreadCards.length;
                    if (unreadCount > 0) {
                        $unreadCards.removeClass('unread');
                        $unreadCards.find('.mark-read-btn').remove();
                        updateCounts({ unreadDelta: -unreadCount, readDelta: unreadCount });
                    }
                }
            });
        }
    });

    // Marcar individual como leída
    $('.mark-read-btn').click(function() {
        const id = $(this).data('id');
        const card = $(this).closest('.notification-card');
        
        $.post(`<?= site_url('deskapp/notifications/api_mark_read') ?>/${id}`, function(response) {
            if (response.success) {
                if (card.hasClass('unread')) {
                    card.removeClass('unread');
                    updateCounts({ unreadDelta: -1, readDelta: 1 });
                }
                $(this).remove();
            }
        }.bind(this));
    });

    // Eliminar notificación
    $('.delete-btn').click(function() {
        if (confirm('¿Eliminar esta notificación?')) {
            const id = $(this).data('id');
            const card = $(this).closest('.notification-card');
            
            $.ajax({
                url: `<?= site_url('deskapp/notifications/api_delete') ?>/${id}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        const wasUnread = card.hasClass('unread');
                        card.fadeOut(300, function() {
                            $(this).remove();
                            updateCounts({
                                totalDelta: -1,
                                unreadDelta: wasUnread ? -1 : 0,
                                readDelta: wasUnread ? 0 : -1
                            });
                            showEmptyStateIfNeeded();
                        });
                    }
                }
            });
        }
    });

    // Click en la card para ir al detalle
    $('.notification-card').click(function(e) {
        if (!$(e.target).closest('button, a').length) {
            const url = $(this).data('url');
            if (url && url !== '#') {
                window.location.href = url;
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
