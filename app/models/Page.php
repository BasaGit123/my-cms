<?php

/**
 * Модель для работы со статическими страницами
 * Каждая страница хранится в отдельном JSON файле в папке data/pages/
 */
class Page
{
    private $pagesDir;

    public function __construct()
    {
        // Определяем директорию для хранения страниц
        $this->pagesDir = ROOT . '/data/pages/';
        
        // Автоматически создаем директорию, если она не существует
        if (!is_dir($this->pagesDir)) {
            mkdir($this->pagesDir, 0755, true);
        }
    }

    /**
     * Получает все страницы
     * @return array - массив всех страниц
     */
    public function getAll()
    {
        $pages = [];
        $files = glob($this->pagesDir . '*.json');
        
        foreach ($files as $file) {
            $pageData = json_decode(file_get_contents($file), true);
            if ($pageData) {
                $pages[] = $pageData;
            }
        }
        
        // Сортируем по дате создания (новые первыми)
        usort($pages, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });
        
        return $pages;
    }

    /**
     * Получает все активные (видимые) страницы
     * @return array - массив активных страниц
     */
    public function getAllActive()
    {
        $allPages = $this->getAll();
        return array_filter($allPages, function($page) {
            return isset($page['status']) && $page['status'] === 'published';
        });
    }

    /**
     * Находит страницу по ID
     * @param int $id - ID страницы
     * @return array|null - данные страницы или null
     */
    public function findById($id)
    {
        $files = glob($this->pagesDir . '*.json');
        
        foreach ($files as $file) {
            $page = json_decode(file_get_contents($file), true);
            if (isset($page['id']) && $page['id'] == $id) {
                return $page;
            }
        }
        
        return null;
    }

    /**
     * Находит страницу по slug (быстрый доступ)
     * @param string $slug - уникальный идентификатор страницы в URL
     * @return array|null - данные страницы или null
     */
    public function findBySlug($slug)
    {
        // Прямой доступ к файлу по slug для максимальной производительности
        $filePath = $this->pagesDir . $slug . '.json';
        
        if (file_exists($filePath)) {
            $pageData = json_decode(file_get_contents($filePath), true);
            return $pageData ?: null;
        }
        
        return null;
    }

    /**
     * Создает новую страницу
     * @param array $data - данные для создания страницы
     * @return bool - успешность операции
     */
    public function create($data)
    {
        $newId = time(); // Используем timestamp как уникальный ID
        $slug = $this->generateUniqueSlug($data['slug'] ?? '', $data['title'] ?? 'page');

        $newPage = [
            'id' => $newId,
            'title' => $data['title'] ?? 'Без заголовка',
            'h1' => $data['h1'] ?? '',
            'slug' => $slug,
            'content' => $data['content'] ?? '',
            'status' => $data['status'] ?? 'draft', // draft, published, hidden
            'seo_title' => $data['seo_title'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'robots' => $data['robots'] ?? 'all', // Настройка индексирования
            'template' => $data['template'] ?? 'default',
            'header_template' => $data['header_template'] ?? 'default',
            'footer_template' => $data['footer_template'] ?? 'default',
            'sort_order' => intval($data['sort_order'] ?? 0), // Порядок сортировки
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Сохраняем в файл по имени slug
        $filePath = $this->pagesDir . $slug . '.json';
        $json = json_encode($newPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($filePath, $json) !== false;
    }

    /**
     * Обновляет существующую страницу
     * @param int $id - ID страницы для обновления
     * @param array $data - новые данные
     * @return bool - успешность операции
     */
    public function update($id, $data)
    {
        $oldPage = $this->findById($id);
        if (!$oldPage) {
            return false;
        }

        $oldSlug = $oldPage['slug'];
        $newSlug = $this->generateUniqueSlug($data['slug'] ?? '', $data['title'] ?? 'page', $id);

        // Обновляем данные страницы
        $updatedPage = $oldPage;
        $updatedPage['title'] = $data['title'] ?? $oldPage['title'];
        $updatedPage['h1'] = $data['h1'] ?? '';
        $updatedPage['slug'] = $newSlug;
        $updatedPage['content'] = $data['content'] ?? $oldPage['content'];
        $updatedPage['status'] = $data['status'] ?? $oldPage['status'];
        $updatedPage['seo_title'] = $data['seo_title'] ?? '';
        $updatedPage['seo_description'] = $data['seo_description'] ?? '';
        $updatedPage['robots'] = $data['robots'] ?? $oldPage['robots'] ?? 'all'; // Настройка индексирования
        $updatedPage['template'] = $data['template'] ?? $oldPage['template'] ?? 'default';
        $updatedPage['header_template'] = $data['header_template'] ?? $oldPage['header_template'] ?? 'default';
        $updatedPage['footer_template'] = $data['footer_template'] ?? $oldPage['footer_template'] ?? 'default';
        $updatedPage['sort_order'] = intval($data['sort_order'] ?? $oldPage['sort_order'] ?? 0);
        $updatedPage['updated_at'] = date('Y-m-d H:i:s');

        // Пути к старому и новому файлам
        $oldFilePath = $this->pagesDir . $oldSlug . '.json';
        $newFilePath = $this->pagesDir . $newSlug . '.json';
        
        // Сохраняем обновленные данные
        $json = json_encode($updatedPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($newFilePath, $json) === false) {
            return false;
        }

        // Если slug изменился, удаляем старый файл
        if ($oldSlug !== $newSlug && file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        return true;
    }

    /**
     * Удаляет страницу
     * @param int $id - ID страницы для удаления
     * @return bool - успешность операции
     */
    public function delete($id)
    {
        $page = $this->findById($id);
        if ($page) {
            $filePath = $this->pagesDir . $page['slug'] . '.json';
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
        }
        return false;
    }

    /**
     * Получает отфильтрованные страницы
     * @param string|null $status - статус для фильтрации ('published', 'draft', 'hidden', 'all')
     * @return array - отфильтрованные страницы
     */
    public function getFiltered($status = null)
    {
        $allPages = $this->getAll();
        
        // Фильтрация по статусу
        if ($status && $status !== 'all') {
            $allPages = array_filter($allPages, function($page) use ($status) {
                return isset($page['status']) && $page['status'] === $status;
            });
        }
        
        // Сортировка по порядку, затем по дате
        usort($allPages, function ($a, $b) {
            $sortA = $a['sort_order'] ?? 0;
            $sortB = $b['sort_order'] ?? 0;
            
            if ($sortA === $sortB) {
                return strtotime($b['created_at']) <=> strtotime($a['created_at']);
            }
            
            return $sortB <=> $sortA;
        });
        
        return $allPages;
    }

    /**
     * Генерирует уникальный slug для страницы
     * @param string $slug - предпочитаемый slug
     * @param string $title - заголовок страницы (используется если slug пустой)
     * @param int|null $excludeId - ID страницы, которую нужно исключить из проверки
     * @return string - уникальный slug
     */
    private function generateUniqueSlug($slug, $title, $excludeId = null)
    {
        $baseSlug = !empty($slug) ? $this->slugify($slug) : $this->slugify($title);
        $finalSlug = $baseSlug;
        $counter = 2;
        
        while (true) {
            $existingPage = $this->findBySlug($finalSlug);
            if (!$existingPage || ($excludeId !== null && $existingPage['id'] == $excludeId)) {
                break;
            }
            $finalSlug = $baseSlug . '-' . $counter++;
        }
        
        return $finalSlug;
    }

    /**
     * Получает список доступных шаблонов страниц с их русскими названиями
     * @return array - ассоциативный массив [имя_файла => русское_название]
     */
    public function getTemplates()
    {
        $templatesDir = ROOT . '/app/views/pages/';
        $files = glob($templatesDir . '*.php');
        $templates = [];

        foreach ($files as $file) {
            $fileName = basename($file, '.php');
            $fileContent = file_get_contents($file);
            
            // Ищем комментарий с названием шаблона (однострочный или многострочный)
            if (preg_match('/\/\*\*\s*Template Name:\s*(.*?)\s*\*\//s', $fileContent, $matches) || preg_match('/\/\/\s*Template Name:\s*(.*)/i', $fileContent, $matches)) {
                $templateName = trim($matches[1]);
            } else {
                // Если комментарий не найден, используем имя файла
                $templateName = ucfirst(str_replace('-', ' ', $fileName));
            }
            
            $templates[$fileName] = $templateName;
        }

        return $templates;
    }

    /**
     * Получает список доступных шаблонов для хедера.
     * @return array - ассоциативный массив [имя_файла => русское_название]
     */
    public function getHeaderTemplates()
    {
        $templates = [];
        $defaultTemplatePath = ROOT . '/app/views/partials/header.php';
        $defaultTemplateName = 'По умолчанию (header.php)';

        if (file_exists($defaultTemplatePath)) {
            $fileContent = file_get_contents($defaultTemplatePath);
            if (preg_match('/\/\*\*\s*Template Name:\s*(.*?)\s*\*\//s', $fileContent, $matches) || preg_match('/\/\/\s*Template Name:\s*(.*)/i', $fileContent, $matches)) {
                $defaultTemplateName = trim($matches[1]);
            }
        }
        $templates['default'] = $defaultTemplateName;

        $files = glob(ROOT . '/app/views/partials/header-*.php');
        foreach ($files as $file) {
            $fileName = basename($file);
            $name = str_replace(['header-', '.php'], '', $fileName);
            if ($name === 'admin') {
                continue;
            }

            $fileContent = file_get_contents($file);
            $templateName = '';

            // Ищем комментарий с названием шаблона
            if (preg_match('/\/\*\*\s*Template Name:\s*(.*?)\s*\*\//s', $fileContent, $matches) || preg_match('/\/\/\s*Template Name:\s*(.*)/i', $fileContent, $matches)) {
                $templateName = trim($matches[1]);
            } else {
                // Если комментарий не найден, используем имя файла
                $templateName = ucfirst(str_replace('-', ' ', $name));
            }
            
            $templates[$name] = $templateName;
        }
        return $templates;
    }

    /**
     * Получает список доступных шаблонов для футера.
     * @return array - ассоциативный массив [имя_файла => русское_название]
     */
    public function getFooterTemplates()
    {
        $templates = [];
        $defaultTemplatePath = ROOT . '/app/views/partials/footer.php';
        $defaultTemplateName = 'По умолчанию (footer.php)';

        if (file_exists($defaultTemplatePath)) {
            $fileContent = file_get_contents($defaultTemplatePath);
            if (preg_match('/\/\*\*\s*Template Name:\s*(.*?)\s*\*\//s', $fileContent, $matches) || preg_match('/\/\/\s*Template Name:\s*(.*)/i', $fileContent, $matches)) {
                $defaultTemplateName = trim($matches[1]);
            }
        }
        $templates['default'] = $defaultTemplateName;

        $files = glob(ROOT . '/app/views/partials/footer-*.php');
        foreach ($files as $file) {
            $fileName = basename($file);
            $name = str_replace(['footer-', '.php'], '', $fileName);
            if ($name === 'admin') {
                continue;
            }

            $fileContent = file_get_contents($file);
            $templateName = '';

            // Ищем комментарий с названием шаблона
            if (preg_match('/\/\*\*\s*Template Name:\s*(.*?)\s*\*\//s', $fileContent, $matches) || preg_match('/\/\/\s*Template Name:\s*(.*)/i', $fileContent, $matches)) {
                $templateName = trim($matches[1]);
            } else {
                // Если комментарий не найден, используем имя файла
                $templateName = ucfirst(str_replace('-', ' ', $name));
            }
            
            $templates[$name] = $templateName;
        }
        return $templates;
    }

    /**
     * Преобразует строку в slug (URL-совместимый формат)
     * @param string $string - исходная строка
     * @return string - slug
     */
    private function slugify($string)
    {
        // Таблица транслитерации кириллицы
        $converter = [
            'а' => 'a',   'б' => 'b',   'в' => 'v',   'г' => 'g',   'д' => 'd',
            'е' => 'e',   'ё' => 'e',   'ж' => 'zh',  'з' => 'z',   'и' => 'i',
            'й' => 'y',   'к' => 'k',   'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',   'с' => 's',   'т' => 't',
            'у' => 'u',   'ф' => 'f',   'х' => 'h',   'ц' => 'c',   'ч' => 'ch',
            'ш' => 'sh',  'щ' => 'sch', 'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        ];
        
        $string = mb_strtolower($string, 'UTF-8');
        $string = strtr($string, $converter);
        $string = preg_replace('/[^a-z0-9 -]/', '', $string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        
        return trim($string, '-');
    }
}