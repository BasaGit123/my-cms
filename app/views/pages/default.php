<?php 
//Template Name: По умолчанию
?>

<div class="page-content">
    <!-- Заголовок страницы -->
    <div class="page-header mb-4">
        <h1 class="page-title"><?= htmlspecialchars($page['h1'] ?? $page['title']) ?></h1>
        
        <?php if (!empty($page['seo_description'])) : ?>
            <p class="lead text-muted"><?= htmlspecialchars($page['seo_description']) ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Содержимое страницы -->
    <div class="page-body">
        <?= nl2br(htmlspecialchars($page['content'])) ?>
    </div>
    
    <!-- Информация о странице (только для админов) -->
    <?php if (isset($_SESSION['user'])) : ?>
        <div class="page-meta mt-5 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>Статус:</strong> 
                        <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : ($page['status'] === 'hidden' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($page['status']) ?>
                        </span>
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Обновлено: <?= date('d.m.Y в H:i', strtotime($page['updated_at'])) ?>
                    </small>
                </div>
            </div>
            
            <div class="mt-2">
                <a href="/admin/pages/edit/<?= $page['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Редактировать страницу
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>