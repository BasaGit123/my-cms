<?php
// Template Name: Полный хедер
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= get_meta(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="full-header">
    <header class="bg-primary text-white p-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><a href="/" class="text-white text-decoration-none"><?= get_site_name();?></a></h1>
                    <p class="lead"><?= htmlspecialchars($settings['general']['site_description'] ?? ''); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="contact-info">
                        <p class="mb-1"><i class="bi bi-telephone"></i> +7 (123) 456-78-90</p>
                        <p class="mb-1"><i class="bi bi-envelope"></i> info@<?= $_SERVER['HTTP_HOST']; ?></p>
                        <p class="mb-0"><i class="bi bi-geo-alt"></i> Москва, Россия</p>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?= get_menu(); ?>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Поиск" aria-label="Поиск">
                    <button class="btn btn-outline-light" type="submit">Найти</button>
                </form>
            </div>
        </div>
    </nav>
</body>
</html>