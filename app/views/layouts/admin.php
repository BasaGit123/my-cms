<?php
$current_uri = $_SERVER['REQUEST_URI'];

// Логика для определения активных пунктов меню
$is_dashboard_active = ($current_uri == '/admin');

$is_posts_list_active = strpos($current_uri, '/admin/postList') !== false || strpos($current_uri, '/admin/edit') !== false;
$is_post_create_active = strpos($current_uri, '/admin/create') !== false;
$is_categories_active = strpos($current_uri, '/admin/categories') !== false;

// Логика для страниц
$is_pages_list_active = strpos($current_uri, '/admin/pages') !== false && strpos($current_uri, '/admin/pages/create') === false && strpos($current_uri, '/admin/pages/edit') === false && strpos($current_uri, '/admin/createPage') === false && strpos($current_uri, '/admin/editPage') === false;
$is_page_create_active = strpos($current_uri, '/admin/pages/create') !== false || strpos($current_uri, '/admin/createPage') !== false;
$is_page_edit_active = strpos($current_uri, '/admin/pages/edit') !== false || strpos($current_uri, '/admin/editPage') !== false;

$is_menu_active = strpos($current_uri, '/admin/menu') !== false || strpos($current_uri, '/admin/createMenu') !== false || strpos($current_uri, '/admin/editMenu') !== false;
$is_settings_active = strpos($current_uri, '/admin/settings') !== false;

$is_posts_submenu_active = $is_posts_list_active || $is_post_create_active || $is_categories_active;
$is_pages_submenu_active = $is_pages_list_active || $is_page_create_active || $is_page_edit_active;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - Админ-панель' : 'Админ-панель' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Наши кастомные стили -->
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="/admin">CMS Админ-панель</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <?php if (isset($_SESSION['user'])) : ?>
                <a class="nav-link px-3" href="/admin/logout">Выйти</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3 sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $is_dashboard_active ? 'active' : '' ?>" href="/admin">
                            <i class="bi bi-house-door"></i>
                            Главная
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_posts_submenu_active ? 'active' : '' ?>" href="#posts-submenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_posts_submenu_active ? 'true' : 'false' ?>" aria-controls="posts-submenu">
                            <i class="bi bi-file-earmark-text"></i>
                            Посты
                        </a>
                        <div class="collapse <?= $is_posts_submenu_active ? 'show' : '' ?>" id="posts-submenu">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link <?= $is_posts_list_active ? 'active' : '' ?>" href="/admin/postList">Все посты</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $is_post_create_active ? 'active' : '' ?>" href="/admin/create">Добавить пост</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $is_categories_active ? 'active' : '' ?>" href="/admin/categories">Категории</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_pages_submenu_active ? 'active' : '' ?>" href="#pages-submenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_pages_submenu_active ? 'true' : 'false' ?>" aria-controls="pages-submenu">
                            <i class="bi bi-file-earmark"></i>
                            Статические страницы
                        </a>
                        <div class="collapse <?= $is_pages_submenu_active ? 'show' : '' ?>" id="pages-submenu">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link <?= $is_pages_list_active ? 'active' : '' ?>" href="/admin/pages">Все страницы</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $is_page_create_active ? 'active' : '' ?>" href="/admin/pages/create">Добавить страницу</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_menu_active ? 'active' : '' ?>" href="/admin/menu">
                            <i class="bi bi-list-ul"></i>
                            Меню сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_settings_active ? 'active' : '' ?>" href="/admin/settings">
                            <i class="bi bi-gear"></i>
                            Настройки
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                    <span>Внешний сайт</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="/" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Посмотреть сайт
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="pt-3 pb-2 mb-3">
                <?= get_content(); ?>
            </div>
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/admin.js"></script>
</body>
</html>
