<?php
// Template Name: Полный футер
?>
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><?= get_site_name();?></h5>
                    <p><?= htmlspecialchars($settings['general']['site_description'] ?? ''); ?></p>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Навигация</h5>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white text-decoration-none">Главная</a></li>
                        <li><a href="/blog" class="text-white text-decoration-none">Блог</a></li>
                        <li><a href="/about" class="text-white text-decoration-none">О нас</a></li>
                        <li><a href="/contacts" class="text-white text-decoration-none">Контакты</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Категории</h5>
                    <ul class="list-unstyled">
                        <?php 
                        $blogModel = new Blog();
                        $categories = $blogModel->getAllCategories();
                        foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li><a href="/blog/category/<?= htmlspecialchars($category['slug']); ?>" class="text-white text-decoration-none"><?= htmlspecialchars($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Контакты</h5>
                    <address>
                        <p class="mb-1"><i class="bi bi-geo-alt"></i> Москва, Россия</p>
                        <p class="mb-1"><i class="bi bi-telephone"></i> +7 (123) 456-78-90</p>
                        <p class="mb-1"><i class="bi bi-envelope"></i> info@<?= $_SERVER['HTTP_HOST']; ?></p>
                    </address>
                </div>
            </div>
            <hr class="mt-0 mb-4 bg-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y'); ?> <?= get_site_name();?>. Все права защищены.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="text-white text-decoration-none me-3">Политика конфиденциальности</a>
                    <a href="/terms" class="text-white text-decoration-none">Условия использования</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>