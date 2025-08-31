<?php
/*
 * Фронт-контроллер (Точка входа)
 * Все запросы проходят через этот файл.
 */

// Запускаем сессию, это понадобится для аутентификации в админ-панели
session_start();

// Включаем отображение всех ошибок на этапе разработки для удобной отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Определяем константу ROOT с абсолютным путем к корневой директории проекта
define('ROOT', dirname(__FILE__));

// ==================================================================
// ГЛОБАЛЬНАЯ ЗАГРУЗКА НАСТРОЕК И ХЕЛПЕРОВ
// ==================================================================
// Комментарий: Этот блок загружает все необходимые хелперы и
// глобальные настройки, чтобы они были доступны во всем приложении.

// 1. Загружаем настройки
require_once ROOT . '/app/models/Settings.php';
$settingsModel = new Settings();
$GLOBALS['settings'] = $settingsModel->getSettings();

// 2. Автоматически подключаем все файлы хелперов из папки app/helpers
foreach (glob(ROOT . "/app/helpers/*.php") as $filename) {
    require_once $filename;
}
// ==================================================================

// Подключаем базовый контроллер, от которого наследуются все остальные
require_once ROOT . '/app/controllers/Controller.php';

/**
 * Класс Router
 * Отвечает за разбор URL и вызов соответствующего контроллера и метода.
 */
class Router
{
    // Свойства для хранения имени контроллера, метода и параметров по умолчанию
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // 1. Определение контроллера
        // Проверяем, указан ли контроллер в URL (первая часть URL)
        // и существует ли соответствующий файл контроллера.
        if (!empty($url[0])) {
            // Специальная маршрутизация для блога
            if ($url[0] === 'blog' && !empty($url[1])) {
                // Проверяем, является ли это категорией (если slug категории совпадает с существующей категорией)
                // Сначала проверяем, есть ли пост с таким slug
                require_once ROOT . '/app/models/Blog.php';
                $blogModel = new Blog();
                $post = $blogModel->getPostBySlug($url[1]);
                
                if ($post) {
                    // Это пост
                    $this->controller = 'BlogController';
                    $this->method = 'post';
                    $this->params = [$url[1]];
                    unset($url[0], $url[1]);
                } else {
                    // Проверяем, является ли это категорией
                    $category = $blogModel->getCategoryBySlug($url[1]);
                    if ($category) {
                        // Это категория
                        $this->controller = 'BlogController';
                        $this->method = 'category';
                        $this->params = [$url[1]];
                        unset($url[0], $url[1]);
                    } else {
                        // Не найдено ни поста, ни категории - показываем 404
                        http_response_code(404);
                        require_once ROOT . '/app/controllers/Controller.php';
                        $controller = new Controller();
                        $controller->view('blog/404');
                        return;
                    }
                }
            } elseif ($url[0] === 'blog' && empty($url[1])) {
                // Маршрут для списка постов: /blog
                $this->controller = 'BlogController';
                $this->method = 'index';
                unset($url[0]);
            } elseif (file_exists(ROOT . '/app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                // Обычная маршрутизация контроллеров
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            } else {
                // Контроллер не найден - проверяем статические страницы
                require_once ROOT . '/app/models/Page.php';
                $pageModel = new Page();
                $page = $pageModel->findBySlug($url[0]);
                
                if ($page && $page['status'] === 'published') {
                    // Найдена статическая страница - используем PageController
                    $this->controller = 'PageController';
                    // Параметром будет slug страницы
                    $this->params = [$url[0]];
                } elseif ($page) {
                    // Страница найдена, но не опубликована - проверяем авторизацию
                    if (isset($_SESSION['user'])) {
                        // Админ может видеть неопубликованные страницы
                        $this->controller = 'PageController';
                        $this->params = [$url[0]];
                    } else {
                        // Обычные пользователи получают 404
                        http_response_code(404);
                        require_once ROOT . '/app/controllers/Controller.php';
                        $controller = new Controller();
                        $controller->view('blog/404');
                        return;
                    }
                }
                // Если страница не найдена, продолжаем с дефолтным контроллером
            }
        }

        // Подключаем файл контроллера (либо дефолтный HomeController, либо выбранный из URL)
        if ($this->controller !== 'PageController') {
            require_once ROOT . '/app/controllers/' . $this->controller . '.php';
            // Создаем экземпляр этого контроллера
            $this->controller = new $this->controller;
        } else {
            // Для PageController подключаем его отдельно
            require_once ROOT . '/app/controllers/' . $this->controller . '.php';
            $this->controller = new $this->controller;
        }

        // 2. Определение метода (только если параметры не установлены ранее)
        if (empty($this->params)) {
            // Проверяем, указан ли метод в URL (вторая часть URL)
            if (isset($url[1])) {
                // и существует ли такой метод в нашем контроллере
                if (method_exists($this->controller, $url[1])) {
                    $this->method = $url[1];
                    unset($url[1]);
                }
            }

            // 3. Определение параметров
            // Все, что осталось в массиве URL - это параметры для метода
            $this->params = $url ? array_values($url) : [];
        }

        // 4. Вызов метода
        // Вызываем метод контроллера и передаем ему параметры
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Парсит URL-строку, полученную из GET-параметра 'url'
     * @return array - массив из частей URL
     */
    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            // Разбиваем URL по символу '/'
            // rtrim удаляет слэш в конце, filter_var очищает URL от небезопасных символов
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}

// Создаем экземпляр роутера, который запускает все приложение
$app = new Router();