<?php
// Загружаем настройки сайта
require_once ROOT . '/app/models/Settings.php';
$settingsModel = new Settings();
$settings = $settingsModel->getSettings();

// Загружаем SEO хелперы
require_once ROOT . '/app/helpers/seo_helper.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_title('Вход в панель управления') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body class="text-center">
    <main style="width: 100%; max-width: 500px; padding: 15px; margin: auto;">
        <?= get_content(); ?>
    </main>
</body>
</html>