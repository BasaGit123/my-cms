<h1>Настройки сайта</h1>

<?php if (!empty($_SESSION['message'])) : ?>
    <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['message_error'])) : ?>
    <div class="alert alert-danger"><?= $_SESSION['message_error']; unset($_SESSION['message_error']); ?></div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form method="POST" action="/admin/settings">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Навигация по вкладкам -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">Общие</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab" aria-controls="homepage" aria-selected="false">Главная страница</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Безопасность</button>
                </li>
            </ul>

            <!-- Содержимое вкладок -->
            <div class="tab-content pt-4">
                <!-- Вкладка "Общие" -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Название сайта</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['general']['site_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Краткое описание сайта</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?= htmlspecialchars($settings['general']['site_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="posts_per_page" class="form-label">Постов на главной странице</label>
                        <input type="number" class="form-control" id="posts_per_page" name="posts_per_page" value="<?= (int)($settings['general']['posts_per_page'] ?? 10) ?>">
                    </div>
                </div>
                <!-- Вкладка "SEO" -->
                <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                    <h5 class="mb-3">Базовые SEO-настройки</h5>
                    <div class="mb-3">
                        <label for="seo_default_title" class="form-label">Заголовок по умолчанию (title)</label>
                        <input type="text" class="form-control" id="seo_default_title" name="seo_default_title" value="<?= htmlspecialchars($settings['seo']['default_title'] ?? '') ?>">
                        <div class="form-text">Используется как заголовок страниц, если не задан индивидуальный</div>
                    </div>
                    <div class="mb-3">
                        <label for="seo_default_description" class="form-label">Описание по умолчанию (description)</label>
                        <textarea class="form-control" id="seo_default_description" name="seo_default_description" rows="3"><?= htmlspecialchars($settings['seo']['default_description'] ?? '') ?></textarea>
                        <div class="form-text">Используется как мета-описание, если не задано индивидуальное</div>
                    </div>
                </div>
                <!-- Вкладка "Главная страница" -->
                <div class="tab-pane fade" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                    <h5 class="mb-3">Настройки главной страницы</h5>
                    
                    <!-- Баннер -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <input type="checkbox" id="home_show_banner" name="home_show_banner" <?= ($settings['homepage']['home_show_banner'] ?? true) ? 'checked' : '' ?>>
                            <label for="home_show_banner" class="form-label mb-0 ms-2">Показывать баннер</label>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="home_banner_title" class="form-label">Заголовок баннера</label>
                                <input type="text" class="form-control" id="home_banner_title" name="home_banner_title" value="<?= htmlspecialchars($settings['homepage']['home_banner_title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="home_banner_subtitle" class="form-label">Подзаголовок баннера</label>
                                <input type="text" class="form-control" id="home_banner_subtitle" name="home_banner_subtitle" value="<?= htmlspecialchars($settings['homepage']['home_banner_subtitle'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="home_banner_image" class="form-label">Изображение баннера (URL)</label>
                                <input type="text" class="form-control" id="home_banner_image" name="home_banner_image" value="<?= htmlspecialchars($settings['homepage']['home_banner_image'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Блок с описанием -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <input type="checkbox" id="show_featured" name="show_featured" <?= ($settings['homepage']['show_featured'] ?? true) ? 'checked' : '' ?>>
                            <label for="show_featured" class="form-label mb-0 ms-2">Показывать блок с описанием</label>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="featured_text" class="form-label">Текст описания</label>
                                <textarea class="form-control" id="featured_text" name="featured_text" rows="4"><?= htmlspecialchars($settings['homepage']['featured_text'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Шаблоны -->
                    <div class="card mb-3">
                        <div class="card-header">
                            Шаблоны отображения
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="header_template" class="form-label">Шаблон хедера</label>
                                <select class="form-select" id="header_template" name="header_template">
                                    <?php foreach ($header_templates as $value => $label) : ?>
                                        <option value="<?= htmlspecialchars($value) ?>" <?= ($settings['homepage']['header_template'] ?? 'default') === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="footer_template" class="form-label">Шаблон футера</label>
                                <select class="form-select" id="footer_template" name="footer_template">
                                    <?php foreach ($footer_templates as $value => $label) : ?>
                                        <option value="<?= htmlspecialchars($value) ?>" <?= ($settings['homepage']['footer_template'] ?? 'default') === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Вкладка "Безопасность" -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    
                    <h5 class="mb-3">Смена логина и пароля</h5>
                    <div class="mb-3">
                        <label for="new_username" class="form-label">Новый логин администратора</label>
                        <input type="text" class="form-control" id="new_username" name="new_username" placeholder="Оставьте пустым, чтобы не менять">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Оставьте пустым, чтобы не менять">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтвердите новый пароль</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Защита от подбора пароля</h5>
                    <div class="mb-3">
                        <label for="max_login_attempts" class="form-label">Максимум неудачных попыток входа</label>
                        <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" value="<?= (int)($settings['security']['max_login_attempts'] ?? 5) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="lockout_time_minutes" class="form-label">Время блокировки (в минутах)</label>
                        <input type="number" class="form-control" id="lockout_time_minutes" name="lockout_time_minutes" value="<?= (int)($settings['security']['lockout_time_minutes'] ?? 15) ?>">
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Контактные данные</h5>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Email администратора</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($settings['security']['admin_email'] ?? '') ?>">
                        <div class="form-text">Используется для уведомлений и восстановления пароля (в будущих версиях).</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Сохранить настройки</button>
        </form>
    </div>
</div>