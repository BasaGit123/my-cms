<?php
// Подключаем модель Page для отображения ссылок на страницы
require_once ROOT . '/app/models/Page.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Управление меню</h1>
    <a href="/admin/menu/create" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Создать пункт меню
    </a>
</div>

<?php if (!empty($_SESSION['message'])) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['message_error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['message_error']); unset($_SESSION['message_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($menu_items)) : ?>
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-list-ul"></i> Все пункты меню
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 60px;">№</th>
                        <th scope="col">Название</th>
                        <th scope="col">URL</th>
                        <th scope="col" style="width: 100px;">Цель</th>
                        <th scope="col" style="width: 80px;">Порядок</th>
                        <th scope="col" style="width: 100px;">Статус</th>
                        <th scope="col" style="width: 200px;" class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Сортируем пункты меню по порядку для отображения
                    usort($menu_items, function($a, $b) {
                        $orderA = isset($a['order']) ? $a['order'] : 999;
                        $orderB = isset($b['order']) ? $b['order'] : 999;
                        return $orderA <=> $orderB;
                    });
                    
                    foreach ($menu_items as $item) : 
                    $isActive = isset($item['active']) ? $item['active'] : true;
                    ?>
                    <tr class="<?= !$isActive ? 'table-secondary' : '' ?>">
                        <td class="fw-bold"><?= htmlspecialchars($item['id']) ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!$isActive) : ?>
                                    <i class="bi bi-eye-slash text-muted me-2" title="Скрыт"></i>
                                <?php else : ?>
                                    <i class="bi bi-eye text-success me-2" title="Активен"></i>
                                <?php endif; ?>
                                <span class="<?= !$isActive ? 'text-muted' : '' ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $linkType = $item['link_type'] ?? 'url';
                            if ($linkType === 'page' && !empty($item['page_id'])) {
                                // Получаем информацию о странице
                                $pageModel = new Page();
                                $page = $pageModel->findById($item['page_id']);
                                if ($page) {
                                    echo '<div class="d-flex align-items-center">';
                                    echo '<span class="badge bg-info me-2">Страница</span>';
                                    echo '<code class="small">/' . htmlspecialchars($page['slug']) . '</code>';
                                    echo '</div>';
                                    echo '<small class="text-muted">' . htmlspecialchars($page['title']) . '</small>';
                                } else {
                                    echo '<span class="text-danger">Страница не найдена</span>';
                                }
                            } else {
                                echo '<div class="d-flex align-items-center">';
                                echo '<span class="badge bg-secondary me-2">Ссылка</span>';
                                echo '<code class="small">' . htmlspecialchars($item['url']) . '</code>';
                                echo '</div>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= ($item['target'] ?? '_self') === '_blank' ? 'info' : 'secondary' ?>">
                                <?= ($item['target'] ?? '_self') === '_blank' ? 'Новое окно' : 'То же окно' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= htmlspecialchars($item['order'] ?? 1) ?></span>
                        </td>
                        <td>
                            <?php if ($isActive) : ?>
                                <span class="badge bg-success">Активен</span>
                            <?php else : ?>
                                <span class="badge bg-secondary">Скрыт</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <!-- Кнопка редактирования -->
                                <a href="/admin/menu/edit/<?= $item['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" 
                                   title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- Кнопка переключения активности -->
                                <form method="POST" action="/admin/menu/toggle/<?= $item['id'] ?>" style="display:inline;" class="me-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <button type="submit" 
                                            class="btn btn-outline-<?= $isActive ? 'warning' : 'success' ?> btn-sm" 
                                            title="<?= $isActive ? 'Скрыть' : 'Показать' ?>">
                                        <i class="bi bi-<?= $isActive ? 'eye-slash' : 'eye' ?>"></i>
                                    </button>
                                </form>
                                
                                <!-- Кнопка удаления -->
                                <form method="POST" action="/admin/menu/delete/<?= $item['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <button type="submit" 
                                            class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Вы уверены, что хотите удалить этот пункт меню?\\n\\nНазвание: <?= htmlspecialchars($item['title']) ?>');"
                                            title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Информация:</strong>
        <ul class="mb-0 mt-2">
            <li>Пункты меню отображаются на сайте в указанном порядке</li>
            <li>Скрытые пункты не отображаются на сайте, но остаются в системе</li>
            <li>Можно создавать обычные ссылки или ссылки на статические страницы</li>
            <li>Цель "_blank" открывает ссылку в новом окне</li>
        </ul>
    </div>
</div>

<?php else : ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-list-ul display-1 text-muted mb-3"></i>
            <h4 class="text-muted">Меню пока не создано</h4>
            <p class="text-muted mb-4">Создайте первый пункт меню для навигации по сайту.</p>
            <a href="/admin/menu/create" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Создать первый пункт меню
            </a>
        </div>
    </div>
<?php endif; ?>