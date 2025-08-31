<?php
/**
 * Menu Helper Functions
 * Provides functions for rendering menu elements in templates.
 */

/**
 * Генерирует и возвращает HTML-код для навигационного меню.
 * @return string HTML-код меню.
 */
function get_menu() {
    // Подключаем модель для работы с меню
    require_once ROOT . '/app/models/Menu.php';
    $menuModel = new Menu();
    $menu_items = $menuModel->getAllActive();

    // Если меню пусто, возвращаем пустую строку
    if (empty($menu_items)) {
        return '';
    }

    // Начинаем генерацию HTML
    $html = '';
    foreach ($menu_items as $menu_item) {
        $target = ($menu_item['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : '';
        $icon = ($menu_item['target'] ?? '_self') === '_blank' ? ' <i class="bi bi-box-arrow-up-right small"></i>' : '';

        $html .= '<li class="nav-item">';
        $html .= '<a class="nav-link" href="' . htmlspecialchars($menu_item['url']) . '" ' . $target . '>';
        $html .= htmlspecialchars($menu_item['title']);
        $html .= $icon;
        $html .= '</a>';
        $html .= '</li>';
    }

    return $html;
}
