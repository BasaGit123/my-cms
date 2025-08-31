<?php
// Универсальный файл для отображения списков различных сущностей
// Поддерживаемые типы: post, category, page

$entityType = $entityType ?? 'unknown';

// Устанавливаем заголовки и URL в зависимости от типа сущности
switch ($entityType) {
    case 'post':
        $pageTitle = 'Управление постами';
        $createUrl = '/admin/create';
        $createButtonText = 'Создать пост';
        $editUrlPrefix = '/admin/edit/';
        $deleteUrlPrefix = '/admin/delete/';
        break;
    case 'category':
        $pageTitle = 'Управление категориями';
        $createUrl = '/admin/createCategory';
        $createButtonText = 'Создать категорию';
        $editUrlPrefix = '/admin/editCategory/';
        $deleteUrlPrefix = '/admin/deleteCategory/';
        break;
    case 'page':
        $pageTitle = 'Управление страницами';
        $createUrl = '/admin/pages/create';
        $createButtonText = 'Создать страницу';
        $editUrlPrefix = '/admin/pages/edit/';
        $deleteUrlPrefix = '/admin/pages/delete/';
        break;
    default:
        $pageTitle = 'Список';
        $createUrl = '#';
        $createButtonText = 'Создать';
        $editUrlPrefix = '';
        $deleteUrlPrefix = '';
}

// Для постов определяем фильтры
if ($entityType === 'post') {
    $statusFilter = $_GET['status'] ?? 'all';
    $categoryFilter = $_GET['category'] ?? 'all';
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $pageTitle ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= $createUrl ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> <?= $createButtonText ?>
        </a>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Фильтры для постов -->
<?php if ($entityType === 'post' && !empty($all_categories)): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Статус</label>
                <select name="status" id="status" class="form-select">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Все статусы</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Опубликован</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Черновик</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="category" class="form-label">Категория</label>
                <select name="category" id="category" class="form-select">
                    <option value="all" <?= $categoryFilter === 'all' ? 'selected' : '' ?>>Все категории</option>
                    <?php foreach ($all_categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['slug']) ?>" <?= $categoryFilter === $category['slug'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Фильтровать</button>
                <a href="/admin/posts" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($entities)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <?php if ($entityType === 'post'): ?>
                        <th>Заголовок</th>
                        <th>Категории</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                    <?php elseif ($entityType === 'category'): ?>
                        <th>Название</th>
                        <th>Слаг</th>
                        <th>Дата создания</th>
                    <?php elseif ($entityType === 'page'): ?>
                        <th>Заголовок</th>
                        <th>URL</th>
                        <th>Статус</th>
                        <th>Порядок</th>
                        <th>Дата обновления</th>
                    <?php endif; ?>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entities as $item): ?>
                    <tr>
                        <?php if ($entityType === 'post'): ?>
                            <td>
                                <a href="<?= $editUrlPrefix . $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                            </td>
                            <td>
                                <?php if (!empty($item['categories'])): ?>
                                    <?php foreach ($item['categories'] as $cat): ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($cat) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Без категории</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['status'] === 'published'): ?>
                                    <span class="badge bg-success">Опубликован</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Черновик</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></td>
                        <?php elseif ($entityType === 'category'): ?>
                            <td>
                                <a href="<?= $editUrlPrefix . $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                            </td>
                            <td><?= htmlspecialchars($item['slug']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($item['created_at'] ?? '')) ?></td>
                        <?php elseif ($entityType === 'page'): ?>
                            <td>
                                <a href="<?= $editUrlPrefix . $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                            </td>
                            <td>
                                <a href="/<?= htmlspecialchars($item['slug']) ?>" target="_blank">
                                    /<?= htmlspecialchars($item['slug']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($item['status'] === 'published'): ?>
                                    <span class="badge bg-success">Опубликована</span>
                                <?php elseif ($item['status'] === 'draft'): ?>
                                    <span class="badge bg-warning">Черновик</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Скрыта</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['sort_order'] ?? 0) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($item['updated_at'] ?? $item['created_at'])) ?></td>
                        <?php endif; ?>
                        <td>
                            <a href="<?= $editUrlPrefix . $item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Редактировать
                            </a>
                            <a href="<?= $deleteUrlPrefix . $item['id'] ?>" 
                               class="btn btn-sm btn-outline-danger ms-1"
                               onclick="return confirm('Вы уверены, что хотите удалить эту запись?')">
                                <i class="bi bi-trash"></i> Удалить
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($entityType === 'post' && !empty($pagination)): ?>
        <!-- Пагинация -->
        <nav aria-label="Навигация по страницам">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['current'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>&status=<?= urlencode($statusFilter) ?>&category=<?= urlencode($categoryFilter) ?>">Предыдущая</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                    <li class="page-item <?= $i == $pagination['current'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&category=<?= urlencode($categoryFilter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagination['current'] < $pagination['total']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>&status=<?= urlencode($statusFilter) ?>&category=<?= urlencode($categoryFilter) ?>">Следующая</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-info">
        <?php if ($entityType === 'post'): ?>
            Посты не найдены. <a href="/admin/create">Создать первый пост</a>.
        <?php elseif ($entityType === 'category'): ?>
            Категории не найдены. <a href="/admin/createCategory">Создать первую категорию</a>.
        <?php elseif ($entityType === 'page'): ?>
            Страницы не найдены. <a href="/admin/pages/create">Создать первую страницу</a>.
        <?php else: ?>
            Записи не найдены.
        <?php endif; ?>
    </div>
<?php endif; ?>