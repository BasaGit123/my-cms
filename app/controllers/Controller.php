<?php

/**
 * Базовый контроллер
 * Содержит общую логику, которую будут наследовать все остальные контроллеры.
 */
class Controller
{
    /**
     * Загружает и возвращает экземпляр модели.
     */
    public function model($modelName)
    {
        $modelFile = ROOT . '/app/models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        } else {
            die("Ошибка: Модель не найдена по пути: {$modelFile}");
        }
    }

    /**
     * Рендерит (отображает) основной макет (layout).
     * Контент страницы будет подгружен хелпером get_content().
     */
    public function view($layout = 'main')
    {
        $layoutFile = ROOT . '/app/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require_once $layoutFile;
        } else {
            die("Ошибка: Основной шаблон не найден по пути: {$layoutFile}");
        }
    }

    /**
     * Генерирует и сохраняет в сессии CSRF-токен.
     * @return string - Сгенерированный токен
     */
    protected function generateCsrfToken()
    {
        // Генерируем токен, только если его еще нет в сессии
        if (empty($_SESSION['csrf_token'])) {
            // random_bytes создает криптографически безопасные случайные байты
            // bin2hex преобразует их в строку
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверяет CSRF-токен, полученный из формы.
     * Если токен неверный, скрипт прекращает работу.
     */
    protected function verifyCsrfToken()
    {
        // Проверяем, что токен пришел в POST-запросе и он совпадает с токеном в сессии
        // hash_equals используется для безопасного сравнения строк, предотвращая атаки по времени
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // Если проверка не пройдена, удаляем токен из сессии и прерываем выполнение
            unset($_SESSION['csrf_token']);
            die('Ошибка CSRF: Неверный токен безопасности. Попробуйте отправить форму еще раз.');
        }
        // Если токен верный, удаляем его, чтобы сделать его одноразовым
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * Подготавливает SEO-данные для передачи в шаблон
     * @param array $seoData SEO-данные (title, description)
     * @return array Данные для передачи в шаблон
     */
    protected function prepareSeoData($seoData)
    {
        return [
            'page_title' => $seoData['title'] ?? '',
            'page_description' => $seoData['description'] ?? ''
        ];
    }
}