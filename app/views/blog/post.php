<div class="card shadow-sm">
    <div class="card-body">
        <!-- Заголовок поста -->
        <h1 class="card-title"><?= htmlspecialchars($post['title']) ?></h1>
        <!-- Дата публикации -->
        <p class="text-muted">Опубликовано: <?= date('d.m.Y в H:i', strtotime($post['created_at'])) ?></p>
        
        <?php if (!empty($post['categories'])) : ?>
            <div class="mb-3">
                <?php foreach ($post['categories'] as $category) : ?>
                    <a href="/blog/<?= urlencode(mb_strtolower($category, 'UTF-8')) ?>" class="badge bg-secondary text-decoration-none"><?= htmlspecialchars($category) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr>
        <!-- Полное содержимое поста -->
        <div class="card-text fs-5">
            <?php // nl2br нужен, чтобы сохранить переносы строк, введенные пользователем в тексте ?>
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
    </div>
</div>

<!-- Кнопка для возврата к списку постов -->
<a href="/blog" class="btn btn-outline-primary mt-4">&larr; Назад к списку постов</a>