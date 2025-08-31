<?php 
//Template Name: Альтернативный шаблон
?>
<div style="background-color: #f0f0f0; padding: 20px; border: 1px solid #ccc;">
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($page['content'])) ?></p>
    <p><em>Это альтернативный шаблон страницы.</em></p>
    
    <!-- Информация о странице (только для админов) -->
    <?php if (isset($_SESSION['user'])) : ?>
        <div class="page-meta mt-3 pt-2 border-top">
            <small class="text-muted">
                <strong>Статус:</strong> 
                <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : ($page['status'] === 'hidden' ? 'warning' : 'secondary') ?>">
                    <?= ucfirst($page['status']) ?>
                </span>
            </small>
            <div class="mt-2">
                <a href="/admin/pages/edit/<?= $page['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Редактировать страницу
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>