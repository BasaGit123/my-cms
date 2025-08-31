<?php
// Универсальная форма для создания/редактирования различных сущностей
// Поддерживаемые типы: post, category, page

// Определяем тип сущности
$entityType = $entityType ?? 'unknown';
$isEditing = isset($entity) && !empty($entity);

// Устанавливаем заголовки и URL в зависимости от типа сущности
switch ($entityType) {
    case 'post':
        $pageTitle = $isEditing ? 'Редактирование поста' : 'Создание нового поста';
        $actionUrl = $isEditing ? '/admin/edit/' . $entity['id'] : '/admin/create';
        $cancelUrl = '/admin/posts';
        break;
    case 'category':
        $pageTitle = $isEditing ? 'Редактирование категории' : 'Создание новой категории';
        $actionUrl = $isEditing ? '/admin/editCategory/' . $entity['id'] : '/admin/createCategory';
        $cancelUrl = '/admin/categories';
        break;
    case 'page':
        $pageTitle = $isEditing ? 'Редактирование страницы' : 'Создание новой страницы';
        $actionUrl = $isEditing ? "/admin/pages/edit/{$entity['id']}" : '/admin/pages/create';
        $cancelUrl = '/admin/pages';
        break;
    default:
        $pageTitle = $isEditing ? 'Редактирование' : 'Создание';
        $actionUrl = $isEditing ? '#' : '#';
        $cancelUrl = '/';
}

// Устанавливаем значения полей в зависимости от типа сущности
if ($entityType === 'post') {
    $title = $entity['title'] ?? '';
    $content = $entity['content'] ?? '';
    $slug = $entity['slug'] ?? '';
    $categories = $entity['categories'] ?? [];
    $status = $entity['status'] ?? 'draft';
    $seoTitle = $entity['seo_title'] ?? '';
    $seoDescription = $entity['seo_description'] ?? '';
} elseif ($entityType === 'category') {
    $title = $entity['name'] ?? '';
    $h1 = $entity['h1'] ?? '';
    $slug = $entity['slug'] ?? '';
    $description = $entity['description'] ?? '';
    $seoTitle = $entity['seo_title'] ?? '';
    $seoDescription = $entity['seo_description'] ?? '';
} elseif ($entityType === 'page') {
    $title = $entity['title'] ?? '';
    $h1 = $entity['h1'] ?? '';
    $content = $entity['content'] ?? '';
    $slug = $entity['slug'] ?? '';
    $status = $entity['status'] ?? 'draft';
    $seoTitle = $entity['seo_title'] ?? '';
    $seoDescription = $entity['seo_description'] ?? '';
    $template = $entity['template'] ?? 'default';
    $header_template = $entity['header_template'] ?? 'default';
    $footer_template = $entity['footer_template'] ?? 'default';
    $sortOrder = $entity['sort_order'] ?? 0;
}
?>

<div class="d-flex justify-content-between align-items-center">
    <h1><?= $pageTitle ?></h1>
    <?php if ($entityType === 'page' && isset($entity)): ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= $cancelUrl ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> К списку страниц
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Отображение ошибок -->
<?php if (isset($errors) && !empty($errors)) : ?>
    <div class="alert alert-danger">
        <h5>Ошибки при сохранении:</h5>
        <ul class="mb-0">
            <?php foreach ($errors as $error) : ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Отображение сообщения об успехе -->
<?php if (isset($success)) : ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form method="POST" action="<?= $actionUrl ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <div class="row">
                <div class="<?= ($entityType === 'category') ? 'col-md-12' : 'col-md-9' ?>">
                    <?php if ($entityType !== 'category'): ?>
                    <div class="mb-3">
                        <label for="title" class="form-label"><?= ($entityType === 'page') ? 'Заголовок страницы' : 'Заголовок (H1)' ?> *</label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    <?php if ($entityType === 'page'): ?>
                    <div class="mb-3">
                        <label for="h1" class="form-label">Заголовок H1</label>
                        <input type="text" class="form-control form-control-lg" id="h1" name="h1" value="<?= htmlspecialchars($h1 ?? '') ?>">
                        <div class="form-text">Если оставить пустым, будет использован заголовок страницы</div>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Название категории</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="h1" class="form-label">Заголовок H1</label>
                        <input type="text" class="form-control form-control-lg" id="h1" name="h1" value="<?= htmlspecialchars($h1 ?? '') ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Слаг (часть URL)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>">
                        <div class="form-text">
                            <?php if ($entityType === 'category'): ?>
                            Если оставить пустым, будет сгенерирован из названия.
                            <?php else: ?>
                            Если оставить пустым, будет сгенерирован из заголовка.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($entityType !== 'category'): ?>
                    <div class="mb-3">
                        <label for="content" class="form-label">
                            <?= ($entityType === 'page') ? 'Содержимое страницы' : 'Содержимое' ?> *
                        </label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?= htmlspecialchars($content) ?></textarea>
                    </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание категории</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($entityType !== 'category'): ?>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <h5>Публикация</h5>
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select name="status" id="status" class="form-select">
                                <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Опубликован</option>
                                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Черновик</option>
                                <?php if ($entityType === 'page'): ?>
                                <option value="hidden" <?= ($status === 'hidden') ? 'selected' : '' ?>>Скрыто</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php if ($entityType === 'page'): ?>
                        <div class="mb-3">
                            <label for="template" class="form-label">Шаблон отображения</label>
                            <select class="form-select" id="template" name="template">
                                <?php 
                                $currentTemplate = $template ?? 'default';
                                if (!empty($templates)):
                                    foreach ($templates as $fileName => $templateName):
                                        $selected = ($fileName === $currentTemplate) ? 'selected' : '';
                                ?>
                                        <option value="<?= htmlspecialchars($fileName) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($templateName) ?>
                                        </option>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <option value="default" selected>По умолчанию</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="header_template" class="form-label">Шаблон хедера</label>
                            <select class="form-select" id="header_template" name="header_template">
                                <?php 
                                $currentHeader = $header_template ?? 'default';
                                if (!empty($header_templates)):
                                    foreach ($header_templates as $fileName => $templateName):
                                        $selected = ($fileName === $currentHeader) ? 'selected' : '';
                                ?>
                                        <option value="<?= htmlspecialchars($fileName) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($templateName) ?>
                                        </option>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <option value="default" selected>По умолчанию</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="footer_template" class="form-label">Шаблон футера</label>
                            <select class="form-select" id="footer_template" name="footer_template">
                                <?php 
                                $currentFooter = $footer_template ?? 'default';
                                if (!empty($footer_templates)):
                                    foreach ($footer_templates as $fileName => $templateName):
                                        $selected = ($fileName === $currentFooter) ? 'selected' : '';
                                ?>
                                        <option value="<?= htmlspecialchars($fileName) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($templateName) ?>
                                        </option>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <option value="default" selected>По умолчанию</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="<?= $sortOrder ?>" 
                                   min="0" 
                                   max="999">
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">Сохранить</button>
                    </div>
                    
                    <?php if ($entityType === 'post' && !empty($all_categories)): ?>
                    <div class="border rounded p-3 mt-3">
                        <h5>Категории</h5>
                        <div style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($all_categories as $category) : ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="<?= htmlspecialchars($category['slug']) ?>" id="cat_<?= $category['id'] ?>" <?= in_array($category['slug'], $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cat_<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <hr class="my-4">
            <h4>SEO-настройки</h4>
            <div class="mb-3">
                <label for="seo_title" class="form-label">SEO Title</label>
                <input type="text" class="form-control" id="seo_title" name="seo_title" value="<?= htmlspecialchars($seoTitle) ?>">
                <?php if ($entityType === 'category'): ?>
                <div class="form-text">Если оставить пустым, будет использовано название категории.</div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="seo_description" class="form-label">SEO Description</label>
                <textarea class="form-control" id="seo_description" name="seo_description" rows="3"><?= htmlspecialchars($seoDescription) ?></textarea>
                <?php if ($entityType === 'category'): ?>
                <div class="form-text">Если оставить пустым, будет использовано описание категории.</div>
                <?php endif; ?>
            </div>

            <?php if ($entityType === 'page'): ?>
            <div class="mb-3">
                <label for="robots" class="form-label">Индексация</label>
                <select name="robots" id="robots" class="form-select">
                    <option value="all" <?= (($entity['robots'] ?? 'all') === 'all') ? 'selected' : '' ?>>Разрешить индексацию (all)</option>
                    <option value="noindex, follow" <?= (($entity['robots'] ?? '') === 'noindex, follow') ? 'selected' : '' ?>>Не индексировать, но следовать по ссылкам (noindex, follow)</option>
                    <option value="noindex, nofollow" <?= (($entity['robots'] ?? '') === 'noindex, nofollow') ? 'selected' : '' ?>>Запретить индексацию и переходы по ссылкам (noindex, nofollow)</option>
                </select>
                <div class="form-text">Управляет мета-тегом robots для поисковых систем.</div>
            </div>
            <?php endif; ?>
            
            <?php if ($entityType !== 'page'): ?>
            <button type="submit" class="btn btn-primary mt-3">Сохранить</button>
            <?php endif; ?>
            <a href="<?= $cancelUrl ?>" class="btn btn-secondary mt-3">Отмена</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title') || document.getElementById('name');
    const slugInput = document.getElementById('slug');

    const slugify = (text) => {
        const converter = {
            'а': 'a',   'б': 'b',   'в': 'v',   'г': 'g',   'д': 'd',
            'е': 'e',   'ё': 'e',   'ж': 'zh',  'з': 'z',   'и': 'i',
            'й': 'y',   'к': 'k',   'л': 'l',   'м': 'm',   'н': 'n',
            'о': 'o',   'п': 'p',   'р': 'r',   'с': 's',   'т': 't',
            'у': 'u',   'ф': 'f',   'х': 'h',   'ц': 'c',   'ч': 'ch',
            'ш': 'sh',  'щ': 'sch', 'ь': '',    'ы': 'y',   'ъ': '',
            'э': 'e',   'ю': 'yu',  'я': 'ya',
        };
        text = text.toString().toLowerCase().trim();
        text = text.replace(/[\s\_]+/g, '-');
        let newText = '';
        for (let i = 0; i < text.length; i++) {
            newText += converter[text[i]] || text[i];
        }
        newText = newText.replace(/[^a-z0-9-]/g, '');
        newText = newText.replace(/-+/g, '-');
        return newText;
    };

    if(titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            slugInput.value = slugify(this.value);
        });
    }
});
</script>
