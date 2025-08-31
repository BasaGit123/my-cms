<h1 class="h2">Главная</h1>
<p>Общая сводка по вашему сайту.</p>

<div class="row mt-4">
    <!-- Карточка постов -->
    <div class="col-md-6 col-lg-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header"><strong>Посты блога</strong></div>
            <div class="card-body">
                <h4 class="card-title display-6"><?= $totalPosts ?></h4>
                <p class="card-text">
                    Опубликованных: <?= $publishedPosts ?><br>
                    Черновиков: <?= $draftPosts ?>
                </p>
                <a href="/admin/postList" class="btn btn-outline-light">Перейти к постам &rarr;</a>
            </div>
        </div>
    </div>
    
    <!-- Карточка страниц -->
    <div class="col-md-6 col-lg-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header"><strong>Статические страницы</strong></div>
            <div class="card-body">
                <h4 class="card-title display-6"><?= $totalPages ?></h4>
                <p class="card-text">
                    Опубликованных: <?= $publishedPages ?><br>
                    Черновиков: <?= $draftPages ?><br>
                    Скрытых: <?= $hiddenPages ?>
                </p>
                <a href="/admin/pages" class="btn btn-outline-light">Управлять страницами &rarr;</a>
            </div>
        </div>
    </div>
    
    <!-- Карточка быстрых действий -->
    <div class="col-md-6 col-lg-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header"><strong>Быстрые действия</strong></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/create" class="btn btn-outline-light btn-sm">Новый пост</a>
                    <a href="/admin/pages/create" class="btn btn-outline-light btn-sm">Новая страница</a>
                    <a href="/admin/menu" class="btn btn-outline-light btn-sm">Меню сайта</a>
                    <a href="/admin/settings" class="btn btn-outline-light btn-sm">Настройки</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Последние обновления -->
<?php if (isset($recentUpdates) && !empty($recentUpdates)) : ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Последние обновления</h5>
            </div>
            <div class="card-body">
                <!-- Здесь можно добавить список последних созданных постов и страниц -->
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
