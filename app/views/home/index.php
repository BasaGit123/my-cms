<?php
// Используем настройки из данных представления, если они доступны
// В противном случае используем глобальную переменную
$homepageSettings = (isset($settings) ? $settings : (isset($GLOBALS['settings']) ? $GLOBALS['settings'] : []))['homepage'] ?? [];
?>

<!-- Баннер -->
<?php if ($homepageSettings['home_show_banner'] ?? false): ?>
    <div class="hero-section bg-light p-5 rounded-3 mb-5">
        <div class="container-fluid py-5">
            <?php if (!empty($homepageSettings['home_banner_image'])): ?>
                <img src="<?= htmlspecialchars($homepageSettings['home_banner_image']); ?>" alt="Banner" class="img-fluid mb-3">
            <?php endif; ?>
            <h1 class="display-5 fw-bold"><?= htmlspecialchars($homepageSettings['home_banner_title'] ?? 'Добро пожаловать на наш сайт!'); ?></h1>
            <?php if (!empty($homepageSettings['home_banner_subtitle'])): ?>
                <p class="col-md-8 fs-4">
                    <?= htmlspecialchars($homepageSettings['home_banner_subtitle']); ?>
                </p>
            <?php endif; ?>
            <a href="/blog" class="btn btn-primary btn-lg">Перейти к блогу</a>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Основной контент -->
    <div class="col-lg-8">
        <?php if ($homepageSettings['show_featured'] ?? false && !empty($homepageSettings['featured_text'])): ?>
            <h2>О нашем проекте</h2>
            <p class="lead">
                <?= nl2br(htmlspecialchars($homepageSettings['featured_text'])); ?>
            </p>
        <?php endif; ?>
        
        <h3>Основные возможности:</h3>
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item">📝 Система управления контентом (CMS)</li>
            <li class="list-group-item">📚 Блог с категориями и SEO оптимизацией</li>
            <li class="list-group-item">🔧 Административная панель</li>
            <li class="list-group-item">📱 Адаптивный дизайн на Bootstrap 5</li>
            <li class="list-group-item">🚀 Быстрая работа без базы данных</li>
        </ul>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">🔒 Безопасность</h5>
                        <p class="card-text">
                            Встроенная защита от CSRF атак, XSS защита, и безопасная обработка пользовательского ввода.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">⚡ Производительность</h5>
                        <p class="card-text">
                            Минимальные системные требования, быстрая загрузка страниц и эффективное кэширование.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Боковая панель -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>🔗 Быстрые ссылки</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/blog" class="btn btn-outline-primary">Блог</a>
                    <a href="/admin" class="btn btn-outline-secondary">Админ-панель</a>
                    <a href="#" class="btn btn-outline-info">О проекте</a>
                    <a href="#" class="btn btn-outline-success">Контакты</a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>📊 Статистика</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary"><?= isset($publishedPosts) ? count($publishedPosts) : 0 ?></h4>
                        <small>Постов</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success"><?= isset($categories) ? count($categories) : 0 ?></h4>
                        <small>Категорий</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info"><?= isset($allPosts) && isset($publishedPosts) ? (count($allPosts) - count($publishedPosts)) : 0 ?></h4>
                        <small>Черновиков</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Секция с последними постами -->
<?php if (!empty($publishedPosts)) : ?>
<div class="mt-5">
    <h3>Последние записи в блоге</h3>
    <div class="row">
        <?php 
        // Показываем только 3 последних поста на главной
        $latestPosts = array_slice(array_reverse($publishedPosts), 0, 3);
        foreach ($latestPosts as $post) : 
        ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h5>
                        <p class="card-text">
                            <?php 
                            // Используем SEO описание, если доступно, иначе обрезаем содержимое
                            $description = !empty($post['seo_description']) ? $post['seo_description'] : mb_substr(strip_tags($post['content']), 0, 100);
                            echo htmlspecialchars($description) . (mb_strlen($description) >= 100 && empty($post['seo_description']) ? '...' : '');
                            ?>
                        </p>
                        <small class="text-muted">
                            <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
        <a href="/blog" class="btn btn-primary">Смотреть все записи →</a>
    </div>
</div>
<?php endif; ?>