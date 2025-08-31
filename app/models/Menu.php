<?php

require_once ROOT . '/app/models/Model.php';
require_once ROOT . '/app/models/Page.php'; // Подключаем модель Page для работы со страницами

/**
 * Модель для управления меню сайта
 * Позволяет создавать, редактировать, удалять и упорядочивать пункты меню
 */
class Menu extends Model
{
    public function __construct()
    {
        // Используем файл data/menu.json для хранения данных меню
        parent::__construct('data/menu.json');
    }

    /**
     * Создает новый пункт меню
     * @param array $data - данные пункта меню (title, url, target, order)
     * @return bool - успешность операции
     */
    public function create($data)
    {
        // Определяем максимальный ID для нового пункта
        $maxId = 0;
        foreach ($this->data as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        $newId = $maxId + 1;

        // Определяем порядок сортировки (если не указан)
        $order = isset($data['order']) ? (int)$data['order'] : $this->getNextOrder();

        // Создаем новый пункт меню
        $newMenuItem = [
            'id' => $newId,
            'title' => $data['title'] ?? 'Новый пункт меню',
            'url' => $data['url'] ?? '#',
            'target' => $data['target'] ?? '_self', // _self или _blank
            'order' => $order,
            'active' => isset($data['active']) ? (bool)$data['active'] : true,
            // Новые поля для выбора типа ссылки
            'link_type' => $data['link_type'] ?? 'url', // 'url' или 'page'
            'page_id' => isset($data['page_id']) ? (int)$data['page_id'] : null, // ID статической страницы
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Добавляем в массив данных
        $this->data[] = $newMenuItem;
        
        // Пересортировываем меню по порядку
        $this->sortByOrder();
        
        // Сохраняем в файл
        return $this->save();
    }

    /**
     * Обновляет существующий пункт меню
     * @param int $id - ID пункта меню
     * @param array $data - новые данные
     * @return bool - успешность операции
     */
    public function update($id, $data)
    {
        $menuItemFound = false;
        
        foreach ($this->data as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                // Обновляем данные пункта меню
                $item['title'] = $data['title'] ?? $item['title'];
                $item['url'] = $data['url'] ?? $item['url'];
                $item['target'] = $data['target'] ?? $item['target'];
                $item['order'] = isset($data['order']) ? (int)$data['order'] : $item['order'];
                $item['active'] = isset($data['active']) ? (bool)$data['active'] : $item['active'];
                // Обновляем новые поля
                $item['link_type'] = $data['link_type'] ?? ($item['link_type'] ?? 'url');
                $item['page_id'] = isset($data['page_id']) ? (int)$data['page_id'] : ($item['page_id'] ?? null);
                $item['updated_at'] = date('Y-m-d H:i:s');
                
                $menuItemFound = true;
                break;
            }
        }

        if ($menuItemFound) {
            // Пересортировываем меню по порядку
            $this->sortByOrder();
            return $this->save();
        }
        
        return false;
    }

    /**
     * Удаляет пункт меню
     * @param int $id - ID пункта меню
     * @return bool - успешность операции
     */
    public function delete($id)
    {
        $originalCount = count($this->data);
        
        // Удаляем элемент с указанным ID
        $this->data = array_filter($this->data, function($item) use ($id) {
            return !isset($item['id']) || $item['id'] != $id;
        });
        
        // Проверяем, был ли удален элемент
        if (count($this->data) < $originalCount) {
            // Перенумеровываем порядок после удаления
            $this->reorderAfterDelete();
            return $this->save();
        }
        
        return false;
    }

    /**
     * Получает все активные пункты меню, отсортированные по порядку
     * @return array - массив активных пунктов меню
     */
    public function getAllActive()
    {
        $activeItems = array_filter($this->data, function($item) {
            return isset($item['active']) && $item['active'] === true;
        });
        
        // Сортируем по порядку
        usort($activeItems, function($a, $b) {
            $orderA = isset($a['order']) ? $a['order'] : 999;
            $orderB = isset($b['order']) ? $b['order'] : 999;
            return $orderA <=> $orderB;
        });
        
        // Обрабатываем URL для пунктов меню с типом 'page'
        foreach ($activeItems as &$item) {
            if (isset($item['link_type']) && $item['link_type'] === 'page' && isset($item['page_id'])) {
                $pageModel = new Page();
                $page = $pageModel->findById($item['page_id']);
                if ($page && $page['status'] === 'published') {
                    $item['url'] = '/' . $page['slug'];
                }
            }
        }
        
        return $activeItems;
    }

    /**
     * Изменяет порядок пункта меню
     * @param int $id - ID пункта меню
     * @param int $newOrder - новый порядок
     * @return bool - успешность операции
     */
    public function changeOrder($id, $newOrder)
    {
        foreach ($this->data as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $item['order'] = (int)$newOrder;
                $this->sortByOrder();
                return $this->save();
            }
        }
        return false;
    }

    /**
     * Переключает активность пункта меню
     * @param int $id - ID пункта меню
     * @return bool - успешность операции
     */
    public function toggleActive($id)
    {
        foreach ($this->data as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $item['active'] = !($item['active'] ?? true);
                return $this->save();
            }
        }
        return false;
    }

    /**
     * Получает следующий порядковый номер для нового пункта меню
     * @return int - следующий номер порядка
     */
    private function getNextOrder()
    {
        $maxOrder = 0;
        foreach ($this->data as $item) {
            $order = isset($item['order']) ? $item['order'] : 0;
            if ($order > $maxOrder) {
                $maxOrder = $order;
            }
        }
        return $maxOrder + 1;
    }

    /**
     * Сортирует меню по порядку
     */
    private function sortByOrder()
    {
        usort($this->data, function($a, $b) {
            $orderA = isset($a['order']) ? $a['order'] : 999;
            $orderB = isset($b['order']) ? $b['order'] : 999;
            return $orderA <=> $orderB;
        });
    }

    /**
     * Перенумеровывает порядок пунктов меню после удаления
     */
    private function reorderAfterDelete()
    {
        $this->sortByOrder();
        $order = 1;
        foreach ($this->data as &$item) {
            $item['order'] = $order++;
        }
    }
}