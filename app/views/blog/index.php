<!-- Заголовок страницы -->
<h1>Последние записи в блоге</h1>

<div class="row gy-4 mt-4">
    <?php if (!empty($posts)) : ?>
        <?php // Отображаем посты в обратном порядке, чтобы новые были сверху
        foreach (array_reverse($posts) as $post) : ?>
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">
                            <!-- Ссылка на страницу полного поста. URL будет вида /blog/slug -->
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none text-dark">
                                <!-- htmlspecialchars защищает от XSS-атак, экранируя HTML-теги -->
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
                        <!-- Форматируем дату в более привычный вид -->
                        Опубликовано: <?= date('d.m.Y в H:i', strtotime($post['created_at'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <!-- Сообщение, если постов еще нет -->
        <div class="alert alert-info">Пока нет ни одного поста. Вы можете добавить первый через админ-панель.</div>
    <?php endif; ?>
</div>