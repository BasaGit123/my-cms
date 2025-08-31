<?php
// Определяем, редактируем ли мы существующий пункт меню или создаем новый
$isEditing = isset($entity) && !empty($entity);
$pageTitle = $isEditing ? 'Редактирование пункта меню' : 'Создание нового пункта меню';
$actionUrl = $isEditing ? '/admin/menu/edit/' . $entity['id'] : '/admin/menu/create';

// Устанавливаем значения для полей формы
$menuTitle = $entity['title'] ?? '';
$menuUrl = $entity['url'] ?? '';
$menuTarget = $entity['target'] ?? '_self';
$menuOrder = $entity['order'] ?? 1;
$menuActive = isset($entity['active']) ? $entity['active'] : true;
// Новые поля для выбора типа ссылки
$linkType = $entity['link_type'] ?? 'url';
$pageId = $entity['page_id'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2"><?= $pageTitle ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin">Главная</a></li>
            <li class="breadcrumb-item"><a href="/admin/menu">Меню</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEditing ? 'Редактирование' : 'Создание' ?></li>
        </ol>
    </nav>
</div>

<?php if (!empty($_SESSION['message_error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['message_error']); unset($_SESSION['message_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-<?= $isEditing ? 'pencil' : 'plus-circle' ?>"></i>
                    <?= $isEditing ? 'Редактирование пункта меню' : 'Новый пункт меню' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $actionUrl ?>" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <!-- Название пункта меню -->
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            <i class="bi bi-type text-primary"></i> Название пункта меню *
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="title" 
                               name="title" 
                               value="<?= htmlspecialchars($menuTitle) ?>" 
                               required 
                               placeholder="Например: Главная, О нас, Контакты">
                        <div class="form-text">Текст, который будет отображаться в меню</div>
                    </div>

                    <!-- Выбор типа ссылки -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-link-45deg text-info"></i> Тип ссылки
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="link_type" id="link_type_url" value="url" <?= $linkType === 'url' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="link_type_url">
                                        <i class="bi bi-globe"></i> Обычная ссылка
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="link_type" id="link_type_page" value="page" <?= $linkType === 'page' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="link_type_page">
                                        <i class="bi bi-file-earmark-text"></i> Статическая страница
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Выберите, куда будет вести пункт меню</div>
                    </div>

                    <!-- Поле для обычной ссылки -->
                    <div class="mb-4" id="url_field">
                        <label for="url" class="form-label">
                            <i class="bi bi-link-45deg text-success"></i> URL ссылки *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="url" 
                               name="url" 
                               value="<?= htmlspecialchars($menuUrl) ?>" 
                               placeholder="Например: /, /about, https://example.com">
                        <div class="form-text">
                            Укажите относительный (/about) или абсолютный (https://example.com) адрес
                        </div>
                    </div>

                    <!-- Поле для выбора страницы -->
                    <div class="mb-4" id="page_field" style="display: none;">
                        <label for="page_id" class="form-label">
                            <i class="bi bi-file-earmark-text text-warning"></i> Выбор страницы *
                        </label>
                        <select class="form-select" id="page_id" name="page_id">
                            <option value="">Выберите страницу...</option>
                            <?php if (!empty($pages)) : ?>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?= $page['id'] ?>" <?= $pageId == $page['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($page['title']) ?> (<?= htmlspecialchars($page['slug']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Выберите статическую страницу из списка</div>
                    </div>

                    <div class="row">
                        <!-- Цель ссылки -->
                        <div class="col-md-6 mb-4">
                            <label for="target" class="form-label">
                                <i class="bi bi-box-arrow-up-right text-info"></i> Цель ссылки
                            </label>
                            <select class="form-select" id="target" name="target">
                                <option value="_self" <?= $menuTarget === '_self' ? 'selected' : '' ?>>
                                    То же окно (_self)
                                </option>
                                <option value="_blank" <?= $menuTarget === '_blank' ? 'selected' : '' ?>>
                                    Новое окно (_blank)
                                </option>
                            </select>
                            <div class="form-text">Где откроется ссылка при клике</div>
                        </div>

                        <!-- Порядок отображения -->
                        <div class="col-md-6 mb-4">
                            <label for="order" class="form-label">
                                <i class="bi bi-sort-numeric-up text-warning"></i> Порядок отображения
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="order" 
                                   name="order" 
                                   value="<?= htmlspecialchars($menuOrder) ?>" 
                                   min="1" 
                                   max="999">
                            <div class="form-text">Чем меньше число, тем выше в меню</div>
                        </div>
                    </div>

                    <!-- Статус активности -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="active" 
                                   name="active" 
                                   value="1" 
                                   <?= $menuActive ? 'checked' : '' ?>>
                            <label class="form-check-label" for="active">
                                <i class="bi bi-eye text-success"></i> Показывать пункт меню на сайте
                            </label>
                        </div>
                        <div class="form-text">Если выключено, пункт меню не будет отображаться на сайте</div>
                    </div>

                    <!-- Кнопки управления -->
                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $isEditing ? 'check-lg' : 'plus-circle' ?>"></i>
                            <?= $isEditing ? 'Сохранить изменения' : 'Создать пункт меню' ?>
                        </button>
                        <a href="/admin/menu" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Боковая панель с подсказками -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb"></i> Полезные советы
                </h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Примеры URL:</h6>
                <ul class="small mb-3">
                    <li><code>/</code> - главная страница</li>
                    <li><code>/about</code> - страница "О нас"</li>
                    <li><code>/contacts</code> - контакты</li>
                    <li><code>/admin</code> - админ-панель</li>
                    <li><code>https://google.com</code> - внешняя ссылка</li>
                </ul>

                <h6 class="fw-bold">Типы ссылок:</h6>
                <ul class="small mb-3">
                    <li><strong>Обычная ссылка</strong> - ручной ввод URL</li>
                    <li><strong>Статическая страница</strong> - выбор из списка</li>
                </ul>

                <h6 class="fw-bold">Порядок отображения:</h6>
                <p class="small mb-3">
                    Пункты с меньшим номером отображаются первыми. 
                    Например: 1, 2, 3, 4...
                </p>

                <h6 class="fw-bold">Цель ссылки:</h6>
                <ul class="small mb-0">
                    <li><strong>То же окно</strong> - ссылка откроется в текущей вкладке</li>
                    <li><strong>Новое окно</strong> - ссылка откроется в новой вкладке</li>
                </ul>
            </div>
        </div>

        <?php if ($isEditing) : ?>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Информация о пункте
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>ID:</strong> <?= htmlspecialchars($entity['id']) ?></p>
                <p class="small mb-2"><strong>Создан:</strong> <?= htmlspecialchars($entity['created_at'] ?? 'Неизвестно') ?></p>
                <?php if (isset($entity['updated_at'])) : ?>
                <p class="small mb-0"><strong>Обновлен:</strong> <?= htmlspecialchars($entity['updated_at']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Получаем элементы
    const titleInput = document.getElementById('title');
    const form = document.querySelector('form');
    const linkTypeUrl = document.getElementById('link_type_url');
    const linkTypePage = document.getElementById('link_type_page');
    const urlField = document.getElementById('url_field');
    const pageField = document.getElementById('page_field');
    const urlInput = document.getElementById('url');
    const pageSelect = document.getElementById('page_id');
    
    // Автофокус на поле названия
    if (titleInput && !titleInput.value) {
        titleInput.focus();
    }
    
    // Функция переключения полей
    function toggleFields() {
        if (linkTypePage.checked) {
            // Показываем поле выбора страницы
            pageField.style.display = 'block';
            urlField.style.display = 'none';
            // Убираем required с URL и добавляем к странице
            urlInput.removeAttribute('required');
            pageSelect.setAttribute('required', 'required');
        } else {
            // Показываем поле URL
            urlField.style.display = 'block';
            pageField.style.display = 'none';
            // Убираем required со страницы и добавляем к URL
            pageSelect.removeAttribute('required');
            urlInput.setAttribute('required', 'required');
        }
    }
    
    // Начальное состояние полей
    toggleFields();
    
    // Обработчики событий для радиокнопок
    linkTypeUrl.addEventListener('change', toggleFields);
    linkTypePage.addEventListener('change', toggleFields);

    // Валидация формы
    form.addEventListener('submit', function(e) {
        const title = titleInput.value.trim();
        
        if (!title) {
            e.preventDefault();
            alert('Пожалуйста, укажите название пункта меню');
            titleInput.focus();
            return false;
        }
        
        // Проверяем в зависимости от типа ссылки
        if (linkTypeUrl.checked) {
            const url = urlInput.value.trim();
            if (!url) {
                e.preventDefault();
                alert('Пожалуйста, укажите URL ссылки');
                urlInput.focus();
                return false;
            }
        } else if (linkTypePage.checked) {
            const pageId = pageSelect.value;
            if (!pageId) {
                e.preventDefault();
                alert('Пожалуйста, выберите страницу');
                pageSelect.focus();
                return false;
            }
        }
    });
});
</script>