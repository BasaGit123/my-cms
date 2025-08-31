<?php

require_once ROOT . '/app/controllers/Controller.php';

class AdminController extends Controller
{
    /**
     * Главная страница админ-панели (дашборд) со статистикой.
     */
    public function index()
    {
        $this->checkAuth();
        
        // Статистика по постам
        $blogModel = $this->model('Blog');
        $allPosts = $blogModel->getAllPosts();
        
        $totalPosts = count($allPosts);
        $publishedPosts = count($blogModel->getPublishedPosts());
        $draftPosts = $totalPosts - $publishedPosts;

        // Статистика по страницам
        $pageModel = $this->model('Page');
        $allPages = $pageModel->getAll();
        
        $totalPages = count($allPages);
        $publishedPages = count($pageModel->getAllActive());
        $draftPages = count(array_filter($allPages, function($page) {
            return $page['status'] === 'draft';
        }));
        $hiddenPages = count(array_filter($allPages, function($page) {
            return $page['status'] === 'hidden';
        }));

        $GLOBALS['page_title'] = 'Главная панель';
        $GLOBALS['view_name'] = 'admin/dashboard';
        $GLOBALS['view_data'] = [
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'draftPosts' => $draftPosts,
            'totalPages' => $totalPages,
            'publishedPages' => $publishedPages,
            'draftPages' => $draftPages,
            'hiddenPages' => $hiddenPages
        ];

        $this->view('admin');
    }

    /**
     * Показывает список всех постов с возможностью фильтрации.
     */
    public function postList()
    {
        $this->checkAuth();
        $blogModel = $this->model('Blog');
        
        $filterCategory = $_GET['category'] ?? 'all';
        $filterStatus = $_GET['status'] ?? 'all';
        
        $posts = $blogModel->getFilteredPosts(
            $filterCategory === 'all' ? null : $filterCategory,
            $filterStatus === 'all' ? null : $filterStatus
        );
        
        $allCategories = $blogModel->getAllCategories();
        
        $GLOBALS['page_title'] = 'Все посты';
        $GLOBALS['view_name'] = 'admin/universal_list';
        $GLOBALS['view_data'] = [
            'entities' => $posts,
            'all_categories' => $allCategories,
            'entityType' => 'post',
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('admin');
    }

    /**
     * Страница входа.
     */
    public function login()
    {
        if (isset($_SESSION['user'])) {
            header('Location: /admin');
            exit;
        }

        if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
            $remaining = $_SESSION['lockout_until'] - time();
            $error = 'Вы превысили количество попыток входа. Пожалуйста, подождите ' . ceil($remaining / 60) . ' мин.';
            
            $GLOBALS['page_title'] = 'Вход в панель управления';
            $GLOBALS['view_name'] = 'admin/login';
            $GLOBALS['view_data'] = ['error' => $error];
            $this->view('login');
            return;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            
            $userModel = $this->model('User');
            $user = $userModel->findByUsername($_POST['username']);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                unset($_SESSION['login_attempts'], $_SESSION['lockout_until']);
                $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
                session_regenerate_id(true);
                header('Location: /admin');
                exit;
            } else {
                if (!isset($_SESSION['login_attempts'])) {
                    $_SESSION['login_attempts'] = 0;
                }
                $_SESSION['login_attempts']++;

                $settingsModel = $this->model('Settings');
                $settings = $settingsModel->getSettings();
                $max_attempts = $settings['security']['max_login_attempts'] ?? 5;

                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $lockout_minutes = $settings['security']['lockout_time_minutes'] ?? 15;
                    $_SESSION['lockout_until'] = time() + ($lockout_minutes * 60);
                    unset($_SESSION['login_attempts']);
                    $error = 'Вы превысили количество попыток входа. Доступ заблокирован на ' . $lockout_minutes . ' мин.';
                } else {
                    $error = 'Неверный логин или пароль.';
                }
            }
        }

        $GLOBALS['page_title'] = 'Вход в панель управления';
        $GLOBALS['view_name'] = 'admin/login';
        $GLOBALS['view_data'] = [
            'error' => $error,
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('login');
    }

    public function logout()
    {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }

    public function create()
    {
        $this->checkAuth();
        $blogModel = $this->model('Blog');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => $_POST['title'] ?? 'Без заголовка',
                'slug' => $_POST['slug'] ?? '',
                'content' => $_POST['content'] ?? '',
                'categories' => $_POST['categories'] ?? [],
                'status' => $_POST['status'] ?? 'draft',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? ''
            ];
            if ($blogModel->createPost($data)) {
                $_SESSION['message'] = 'Пост успешно создан!';
                header('Location: /admin/postList');
                exit;
            }
        }

        $GLOBALS['page_title'] = 'Создание нового поста';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entityType' => 'post',
            'csrf_token' => $this->generateCsrfToken(),
            'all_categories' => $blogModel->getAllCategories()
        ];
        $this->view('admin');
    }

    public function edit($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin'); exit; }
        $blogModel = $this->model('Blog');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => $_POST['title'] ?? 'Без заголовка',
                'slug' => $_POST['slug'] ?? '',
                'content' => $_POST['content'] ?? '',
                'categories' => $_POST['categories'] ?? [],
                'status' => $_POST['status'] ?? 'draft',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? ''
            ];
            if ($blogModel->updatePost($id, $data)) {
                $_SESSION['message'] = 'Пост успешно обновлён!';
                header('Location: /admin/postList');
                exit;
            }
        }

        $post = $blogModel->getPostById($id);
        if (!$post) {
            $_SESSION['message_error'] = 'Пост не найден.';
            header('Location: /admin/postList');
            exit;
        }

        $GLOBALS['page_title'] = 'Редактирование поста';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entity' => $post,
            'entityType' => 'post',
            'csrf_token' => $this->generateCsrfToken(),
            'all_categories' => $blogModel->getAllCategories()
        ];
        $this->view('admin');
    }

    public function delete($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin'); exit; }
        $blogModel = $this->model('Blog');

        if ($blogModel->deletePost($id)) {
            $_SESSION['message'] = 'Пост успешно удалён!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при удалении поста.';
        }
        header('Location: /admin/postList');
        exit;
    }

    public function settings()
    {
        $this->checkAuth();
        $settingsModel = $this->model('Settings');
        $userModel = $this->model('User');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $newUserData = [];
            if (!empty($_POST['new_username'])) {
                $newUserData['username'] = $_POST['new_username'];
            }
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    $newUserData['password'] = $_POST['new_password'];
                } else {
                    $_SESSION['message_error'] = 'Пароли не совпадают.';
                    header('Location: /admin/settings');
                    exit;
                }
            }
            if (!empty($newUserData)) {
                $userModel->updateUser(1, $newUserData);
            }
            $newSettings = [
                'general' => [
                    'site_name' => $_POST['site_name'] ?? '',
                    'site_description' => $_POST['site_description'] ?? '',
                    'posts_per_page' => (int)($_POST['posts_per_page'] ?? 10),
                ],
                'seo' => [
                    'default_title' => $_POST['seo_default_title'] ?? '',
                    'default_description' => $_POST['seo_default_description'] ?? '',
                ],
                'security' => [
                    'admin_email' => $_POST['admin_email'] ?? '',
                    'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
                    'lockout_time_minutes' => (int)($_POST['lockout_time_minutes'] ?? 15),
                ],
                // Добавляем обработку настроек главной страницы
                'homepage' => [
                    'home_banner_title' => $_POST['home_banner_title'] ?? '',
                    'home_banner_subtitle' => $_POST['home_banner_subtitle'] ?? '',
                    'home_banner_image' => $_POST['home_banner_image'] ?? '',
                    'home_show_banner' => isset($_POST['home_show_banner']),
                    'featured_text' => $_POST['featured_text'] ?? '',
                    'show_featured' => isset($_POST['show_featured']),
                    'header_template' => $_POST['header_template'] ?? 'default',
                    'footer_template' => $_POST['footer_template'] ?? 'default'
                ]
            ];
            if ($settingsModel->saveSettings($newSettings)) {
                $_SESSION['message'] = 'Настройки успешно сохранены!';
            } else {
                $_SESSION['message_error'] = 'Ошибка при сохранении настроек.';
            }
            header('Location: /admin/settings');
            exit;
        }

        $GLOBALS['page_title'] = 'Настройки сайта';
        $GLOBALS['view_name'] = 'admin/settings';
        $pageModel = $this->model('Page');
        $GLOBALS['view_data'] = [
            'settings' => $settingsModel->getSettings(),
            'csrf_token' => $this->generateCsrfToken(),
            'header_templates' => $pageModel->getHeaderTemplates(),
            'footer_templates' => $pageModel->getFooterTemplates()
        ];
        $this->view('admin');
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function pages($action = null, $id = null)
    {
        $this->checkAuth();
        
        switch ($action) {
            case 'create':
                return $this->createPage();
            case 'edit':
                return $this->editPage($id);
            case 'delete':
                return $this->deletePage($id);
            case 'toggle':
                return $this->togglePageStatus($id);
            default:
                $pageModel = $this->model('Page');
                $filterStatus = $_GET['status'] ?? 'all';
                $pages = $pageModel->getFiltered($filterStatus === 'all' ? null : $filterStatus);
                
                $GLOBALS['page_title'] = 'Все страницы';
                $GLOBALS['view_name'] = 'admin/universal_list';
                $GLOBALS['view_data'] = [
                    'entities' => $pages,
                    'entityType' => 'page',
                    'selectedStatus' => $filterStatus,
                    'csrf_token' => $this->generateCsrfToken(),
                    'templates' => $pageModel->getTemplates()
                ];
                $this->view('admin');
                break;
        }
    }

    private function createPage()
    {
        $this->checkAuth();
        $pageModel = $this->model('Page');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'h1' => trim($_POST['h1'] ?? ''),
                'slug' => trim($_POST['slug'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'status' => $_POST['status'] ?? 'draft',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? '',
                'robots' => $_POST['robots'] ?? 'all', // Добавляем настройку индексирования
                'template' => $_POST['template'] ?? 'default',
                'header_template' => $_POST['header_template'] ?? 'default',
                'footer_template' => $_POST['footer_template'] ?? 'default',
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            $errors = [];
            if (empty($data['title'])) { $errors[] = 'Заголовок обязателен для заполнения'; }
            if (empty($data['slug'])) {
                $errors[] = 'URL (slug) обязателен для заполнения';
            } elseif (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
                $errors[] = 'URL должен содержать только строчные латинские буквы, цифры и дефисы';
            } elseif ($pageModel->findBySlug($data['slug'])) {
                $errors[] = 'Страница с таким URL уже существует';
            }
            if (empty($data['content'])) { $errors[] = 'Содержимое страницы обязательно для заполнения'; }
            
            if (empty($errors)) {
                if ($pageModel->create($data)) {
                    $_SESSION['message'] = 'Страница успешно создана!';
                    header('Location: /admin/pages');
                    exit;
                } else {
                    $errors[] = 'Ошибка при создании страницы';
                }
            }
            
            $GLOBALS['page_title'] = 'Создание новой страницы';
            $GLOBALS['view_name'] = 'admin/universal_form';
            $GLOBALS['view_data'] = [
                'entityType' => 'page',
                'errors' => $errors,
                'old_data' => $data,
                'templates' => $pageModel->getTemplates(),
                'header_templates' => $pageModel->getHeaderTemplates(),
                'footer_templates' => $pageModel->getFooterTemplates(),
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Создание новой страницы';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entityType' => 'page',
            'templates' => $pageModel->getTemplates(),
            'header_templates' => $pageModel->getHeaderTemplates(),
            'footer_templates' => $pageModel->getFooterTemplates(),
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function editPage($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/pages'); exit; }
        
        $pageModel = $this->model('Page');
        $page = $pageModel->findById($id);
        
        if (!$page) {
            $_SESSION['message_error'] = 'Страница не найдена';
            header('Location: /admin/pages');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'h1' => trim($_POST['h1'] ?? ''),
                'slug' => trim($_POST['slug'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'status' => $_POST['status'] ?? 'draft',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? '',
                'robots' => $_POST['robots'] ?? 'all', // Добавляем настройку индексирования
                'template' => $_POST['template'] ?? 'default',
                'header_template' => $_POST['header_template'] ?? 'default',
                'footer_template' => $_POST['footer_template'] ?? 'default',
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            $errors = [];
            if (empty($data['title'])) { $errors[] = 'Заголовок обязателен для заполнения'; }
            if (empty($data['slug'])) {
                $errors[] = 'URL (slug) обязателен для заполнения';
            } elseif (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
                $errors[] = 'URL должен содержать только строчные латинские буквы, цифры и дефисы';
            } elseif ($data['slug'] !== $page['slug'] && $pageModel->findBySlug($data['slug'])) {
                $errors[] = 'Страница с таким URL уже существует';
            }
            if (empty($data['content'])) { $errors[] = 'Содержимое страницы обязательно для заполнения'; }
            
            if (empty($errors)) {
                if ($pageModel->update($id, $data)) {
                    $_SESSION['message'] = 'Страница успешно обновлена!';
                    header('Location: /admin/pages');
                    exit;
                } else {
                    $errors[] = 'Ошибка при обновлении страницы';
                }
            }
            
            $GLOBALS['page_title'] = 'Редактирование страницы';
            $GLOBALS['view_name'] = 'admin/universal_form';
            $GLOBALS['view_data'] = [
                'entity' => $page,
                'entityType' => 'page',
                'errors' => $errors,
                'templates' => $pageModel->getTemplates(),
                'header_templates' => $pageModel->getHeaderTemplates(),
                'footer_templates' => $pageModel->getFooterTemplates(),
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Редактирование страницы';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entity' => $page,
            'entityType' => 'page',
            'templates' => $pageModel->getTemplates(),
            'header_templates' => $pageModel->getHeaderTemplates(),
            'footer_templates' => $pageModel->getFooterTemplates(),
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function deletePage($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/pages'); exit; }
        
        $pageModel = $this->model('Page');
        if ($pageModel->delete($id)) {
            $_SESSION['message'] = 'Страница успешно удалена!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при удалении страницы.';
        }
        header('Location: /admin/pages');
        exit;
    }

    private function togglePageStatus($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/pages'); exit; }
        
        $pageModel = $this->model('Page');
        if ($pageModel->toggleStatus($id)) {
            $_SESSION['message'] = 'Статус страницы успешно изменён!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при изменении статуса страницы.';
        }
        header('Location: /admin/pages');
        exit;
    }

    public function menu($action = null, $id = null)
    {
        $this->checkAuth();
        
        switch ($action) {
            case 'create':
                return $this->createMenuItem();
            case 'edit':
                return $this->editMenuItem($id);
            case 'delete':
                return $this->deleteMenuItem($id);
            case 'toggle':
                return $this->toggleMenuItem($id);
            case 'up':
                return $this->moveMenuItemUp($id);
            case 'down':
                return $this->moveMenuItemDown($id);
            default:
                $menuModel = $this->model('Menu');
                $menuItems = $menuModel->getAll();
                
                $GLOBALS['page_title'] = 'Меню сайта';
                $GLOBALS['view_name'] = 'admin/menu_list';
                $GLOBALS['view_data'] = [
                    'menu_items' => $menuItems,
                    'csrf_token' => $this->generateCsrfToken()
                ];
                $this->view('admin');
                break;
        }
    }

    private function createMenuItem()
    {
        $this->checkAuth();
        $menuModel = $this->model('Menu');
        $pageModel = $this->model('Page');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'url' => trim($_POST['url'] ?? ''),
                'target' => $_POST['target'] ?? '_self',
                'active' => isset($_POST['active']),
                'link_type' => $_POST['link_type'] ?? 'url',
                'page_id' => !empty($_POST['page_id']) ? (int)$_POST['page_id'] : null,
                'order' => (int)($_POST['order'] ?? 1)
            ];
            
            $errors = [];
            if (empty($data['title'])) { $errors[] = 'Заголовок обязателен для заполнения'; }
            
            if ($data['link_type'] === 'url' && empty($data['url'])) {
                $errors[] = 'URL обязателен для заполнения';
            }
            
            if (empty($errors)) {
                if ($menuModel->create($data)) {
                    $_SESSION['message'] = 'Пункт меню успешно создан!';
                    header('Location: /admin/menu');
                    exit;
                } else {
                    $errors[] = 'Ошибка при создании пункта меню';
                }
            }
            
            $GLOBALS['page_title'] = 'Создание пункта меню';
            $GLOBALS['view_name'] = 'admin/menu_form';
            $GLOBALS['view_data'] = [
                'errors' => $errors,
                'old_data' => $data,
                'pages' => $pageModel->getAllActive(),
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Создание пункта меню';
        $GLOBALS['view_name'] = 'admin/menu_form';
        $GLOBALS['view_data'] = [
            'pages' => $pageModel->getAllActive(),
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function editMenuItem($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/menu'); exit; }
        
        $menuModel = $this->model('Menu');
        $pageModel = $this->model('Page');
        $menuItem = $menuModel->findById($id);
        
        if (!$menuItem) {
            $_SESSION['message_error'] = 'Пункт меню не найден';
            header('Location: /admin/menu');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'url' => trim($_POST['url'] ?? ''),
                'target' => $_POST['target'] ?? '_self',
                'active' => isset($_POST['active']),
                'link_type' => $_POST['link_type'] ?? 'url',
                'page_id' => !empty($_POST['page_id']) ? (int)$_POST['page_id'] : null,
                'order' => (int)($_POST['order'] ?? 1)
            ];
            
            $errors = [];
            if (empty($data['title'])) { $errors[] = 'Заголовок обязателен для заполнения'; }
            
            if ($data['link_type'] === 'url' && empty($data['url'])) {
                $errors[] = 'URL обязателен для заполнения';
            }
            
            if (empty($errors)) {
                if ($menuModel->update($id, $data)) {
                    $_SESSION['message'] = 'Пункт меню успешно обновлён!';
                    header('Location: /admin/menu');
                    exit;
                } else {
                    $errors[] = 'Ошибка при обновлении пункта меню';
                }
            }
            
            $GLOBALS['page_title'] = 'Редактирование пункта меню';
            $GLOBALS['view_name'] = 'admin/menu_form';
            $GLOBALS['view_data'] = [
                'entity' => $menuItem,
                'errors' => $errors,
                'pages' => $pageModel->getAllActive(),
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Редактирование пункта меню';
        $GLOBALS['view_name'] = 'admin/menu_form';
        $GLOBALS['view_data'] = [
            'entity' => $menuItem,
            'pages' => $pageModel->getAllActive(),
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function deleteMenuItem($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/menu'); exit; }
        
        $menuModel = $this->model('Menu');
        if ($menuModel->delete($id)) {
            $_SESSION['message'] = 'Пункт меню успешно удалён!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при удалении пункта меню.';
        }
        header('Location: /admin/menu');
        exit;
    }

    private function toggleMenuItem($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/menu'); exit; }
        
        $menuModel = $this->model('Menu');
        if ($menuModel->toggleActive($id)) {
            $_SESSION['message'] = 'Статус пункта меню успешно изменён!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при изменении статуса пункта меню.';
        }
        header('Location: /admin/menu');
        exit;
    }

    private function moveMenuItemUp($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/menu'); exit; }
        
        $menuModel = $this->model('Menu');
        if ($menuModel->moveUp($id)) {
            $_SESSION['message'] = 'Пункт меню успешно перемещён вверх!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при перемещении пункта меню.';
        }
        header('Location: /admin/menu');
        exit;
    }

    private function moveMenuItemDown($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/menu'); exit; }
        
        $menuModel = $this->model('Menu');
        if ($menuModel->moveDown($id)) {
            $_SESSION['message'] = 'Пункт меню успешно перемещён вниз!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при перемещении пункта меню.';
        }
        header('Location: /admin/menu');
        exit;
    }

    public function categories($action = null, $id = null)
    {
        $this->checkAuth();
        
        switch ($action) {
            case 'create':
                return $this->createCategory();
            case 'edit':
                return $this->editCategory($id);
            case 'delete':
                return $this->deleteCategory($id);
            default:
                $blogModel = $this->model('Blog');
                $categories = $blogModel->getAllCategories();
                
                $GLOBALS['page_title'] = 'Категории';
                $GLOBALS['view_name'] = 'admin/universal_list';
                $GLOBALS['view_data'] = [
                    'entities' => $categories,
                    'entityType' => 'category',
                    'csrf_token' => $this->generateCsrfToken()
                ];
                $this->view('admin');
                break;
        }
    }

    private function createCategory()
    {
        $this->checkAuth();
        $blogModel = $this->model('Blog');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'slug' => trim($_POST['slug'] ?? ''),
                'description' => $_POST['description'] ?? '',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? ''
            ];
            
            $errors = [];
            if (empty($data['name'])) { $errors[] = 'Название категории обязательно для заполнения'; }
            if (empty($data['slug'])) {
                $errors[] = 'URL (slug) обязателен для заполнения';
            } elseif (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
                $errors[] = 'URL должен содержать только строчные латинские буквы, цифры и дефисы';
            } elseif ($blogModel->getCategoryBySlug($data['slug'])) {
                $errors[] = 'Категория с таким URL уже существует';
            }
            
            if (empty($errors)) {
                if ($blogModel->createCategory($data)) {
                    $_SESSION['message'] = 'Категория успешно создана!';
                    header('Location: /admin/categories');
                    exit;
                } else {
                    $errors[] = 'Ошибка при создании категории';
                }
            }
            
            $GLOBALS['page_title'] = 'Создание категории';
            $GLOBALS['view_name'] = 'admin/universal_form';
            $GLOBALS['view_data'] = [
                'entityType' => 'category',
                'errors' => $errors,
                'old_data' => $data,
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Создание категории';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entityType' => 'category',
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function editCategory($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/categories'); exit; }
        
        $blogModel = $this->model('Blog');
        $category = $blogModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['message_error'] = 'Категория не найдена';
            header('Location: /admin/categories');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'slug' => trim($_POST['slug'] ?? ''),
                'description' => $_POST['description'] ?? '',
                'seo_title' => $_POST['seo_title'] ?? '',
                'seo_description' => $_POST['seo_description'] ?? ''
            ];
            
            $errors = [];
            if (empty($data['name'])) { $errors[] = 'Название категории обязательно для заполнения'; }
            if (empty($data['slug'])) {
                $errors[] = 'URL (slug) обязателен для заполнения';
            } elseif (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
                $errors[] = 'URL должен содержать только строчные латинские буквы, цифры и дефисы';
            } elseif ($data['slug'] !== $category['slug'] && $blogModel->getCategoryBySlug($data['slug'])) {
                $errors[] = 'Категория с таким URL уже существует';
            }
            
            if (empty($errors)) {
                if ($blogModel->updateCategory($id, $data)) {
                    $_SESSION['message'] = 'Категория успешно обновлена!';
                    header('Location: /admin/categories');
                    exit;
                } else {
                    $errors[] = 'Ошибка при обновлении категории';
                }
            }
            
            $GLOBALS['page_title'] = 'Редактирование категории';
            $GLOBALS['view_name'] = 'admin/universal_form';
            $GLOBALS['view_data'] = [
                'entity' => $category,
                'entityType' => 'category',
                'errors' => $errors,
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->view('admin');
            return;
        }
        
        $GLOBALS['page_title'] = 'Редактирование категории';
        $GLOBALS['view_name'] = 'admin/universal_form';
        $GLOBALS['view_data'] = [
            'entity' => $category,
            'entityType' => 'category',
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->view('admin');
    }

    private function deleteCategory($id = null)
    {
        $this->checkAuth();
        if (!$id) { header('Location: /admin/categories'); exit; }
        
        $blogModel = $this->model('Blog');
        if ($blogModel->deleteCategory($id)) {
            $_SESSION['message'] = 'Категория успешно удалена!';
        } else {
            $_SESSION['message_error'] = 'Ошибка при удалении категории.';
        }
        header('Location: /admin/categories');
        exit;
    }
}