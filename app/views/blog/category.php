<!-- Заголовок страницы с названием категории -->
<h1 class="mb-2"><?= htmlspecialchars($category['h1'] ?? $category['name']) ?></h1>

<?php if (!empty($category['description'])) : ?>
<div class="lead text-muted mb-4">
    <?= htmlspecialchars($category['description']) ?>
</div>
<?php endif; ?>

<hr>

<h4 class="mt-4 mb-4">Посты в этой категории:</h4>

<div class="row gy-4">
    <?php if (!empty($posts)) : ?>
        <?php // Посты уже отсортированы моделью, просто выводим их
        foreach ($posts as $post) : ?>
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none text-dark">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h2>

                        <?php if (!empty($post['categories'])) : ?>
                            <div class="mb-2">
                                <?php foreach ($post['categories'] as $category) : ?>
                                    <a href="/blog/<?= urlencode(mb_strtolower($category, 'UTF-8')) ?>" class="badge bg-secondary text-decoration-none"><?= htmlspecialchars($category) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <p class="card-text">
                            <?php
                            // Используем SEO описание, если доступно, иначе обрезаем содержимое
                            $description = !empty($post['seo_description']) ? $post['seo_description'] : mb_substr($post['content'], 0, 250);
                            echo nl2br(htmlspecialchars($description));
                            // Добавляем многоточие, если текст был обрезан
                            if (empty($post['seo_description']) && mb_strlen($post['content']) > 250) {
                                echo '...';
                            }
                            ?>
                        </p>
                        <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-primary">Читать далее &rarr;</a>
                    </div>
                    <div class="card-footer text-muted">
                        Опубликовано: <?= date('d.m.Y в H:i', strtotime($post['created_at'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="alert alert-info">В этой категории пока нет постов.</div>
    <?php endif; ?>
</div>

<a href="/blog" class="btn btn-outline-primary mt-4">&larr; Назад ко всем постам</a>