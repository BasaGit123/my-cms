<?php
// Template Name: Минималистичный хедер
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
<body class="minimal-header">
    <header class="bg-dark text-white p-3">
        <div class="container">
            <h1><a href="/" class="text-white text-decoration-none"><?= get_site_name();?></a></h1>
        </div>
    </header>