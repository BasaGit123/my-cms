<?php

// Проверяем, определена ли константа ROOT, если нет - определяем её
if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__DIR__)));
}

require_once ROOT . '/app/models/Model.php';

/**
 * Модель для управления блогом
 * Объединяет функциональность моделей Post и Category
 */
class Blog extends Model
{
    private $postsDir;

    public function __construct()
    {
        parent::__construct('data/categories.json');
        $this->postsDir = ROOT . '/data/posts/';
    }

    // --- Методы для работы с постами ---

    /**
     * Получает все посты
     * @return array - массив всех постов
     */
    public function getAllPosts()
    {
        $posts = [];
        $files = glob($this->postsDir . '*.json');
        foreach ($files as $file) {
            $posts[] = json_decode(file_get_contents($file), true);
        }
        usort($posts, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });
        return $posts;
    }

    /**
     * Получает все опубликованные посты
     * @return array - массив опубликованных постов
     */
    public function getPublishedPosts()
    {
        $allPosts = $this->getAllPosts();
        return array_filter($allPosts, function($post) {
            return isset($post['status']) && $post['status'] === 'published';
        });
    }

    /**
     * Находит пост по ID
     * @param int $id - ID поста
     * @return array|null - данные поста или null
     */
    public function getPostById($id)
    {
        $files = glob($this->postsDir . '*.json');
        foreach ($files as $file) {
            $post = json_decode(file_get_contents($file), true);
            if (isset($post['id']) && $post['id'] == $id) {
                return $post;
            }
        }
        return null;
    }

    /**
     * Находит пост по slug
     * @param string $slug - slug поста
     * @return array|null - данные поста или null
     */
    public function getPostBySlug($slug)
    {
        $filePath = $this->postsDir . $slug . '.json';
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true);
        }
        return null;
    }

    /**
     * Создает новый пост
     * @param array $data - данные для создания поста
     * @return bool - успешность операции
     */
    public function createPost($data)
    {
        $newId = time();
        $slug = $this->generateUniquePostSlug($data['slug'] ?? '', $data['title'] ?? 'post');

        $newPost = [
            'id' => $newId,
            'title' => $data['title'] ?? 'Без заголовка',
            'slug' => $slug,
            'content' => $data['content'] ?? '',
            'categories' => $data['categories'] ?? [],
            'status' => $data['status'] ?? 'draft',
            'seo_title' => $data['seo_title'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $filePath = $this->postsDir . $slug . '.json';
        $json = json_encode($newPost, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($filePath, $json) !== false;
    }

    /**
     * Обновляет существующий пост
     * @param int $id - ID поста для обновления
     * @param array $data - новые данные
     * @return bool - успешность операции
     */
    public function updatePost($id, $data)
    {
        $oldPost = $this->getPostById($id);
        if (!$oldPost) return false;

        $oldSlug = $oldPost['slug'];
        $newSlug = $this->generateUniquePostSlug($data['slug'] ?? '', $data['title'] ?? 'post', $id);

        $updatedPost = $oldPost;
        $updatedPost['title'] = $data['title'] ?? $oldPost['title'];
        $updatedPost['slug'] = $newSlug;
        $updatedPost['content'] = $data['content'] ?? $oldPost['content'];
        $updatedPost['categories'] = $data['categories'] ?? $oldPost['categories'] ?? [];
        $updatedPost['status'] = $data['status'] ?? $oldPost['status'] ?? 'draft';
        $updatedPost['seo_title'] = $data['seo_title'] ?? '';
        $updatedPost['seo_description'] = $data['seo_description'] ?? '';

        $oldFilePath = $this->postsDir . $oldSlug . '.json';
        $newFilePath = $this->postsDir . $newSlug . '.json';
        
        $json = json_encode($updatedPost, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($newFilePath, $json) === false) {
            return false;
        }

        if ($oldSlug !== $newSlug && file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        return true;
    }

    /**
     * Удаляет пост
     * @param int $id - ID поста для удаления
     * @return bool - успешность операции
     */
    public function deletePost($id)
    {
        $post = $this->getPostById($id);
        if ($post) {
            $filePath = $this->postsDir . $post['slug'] . '.json';
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
        }
        return false;
    }

    /**
     * Получает посты по категории
     * @param string $categoryName - имя категории
     * @return array - массив постов в категории
     */
    public function getPostsByCategory($categoryName)
    {
        $allPosts = $this->getPublishedPosts();
        $filteredPosts = [];
        $lowerCategoryName = mb_strtolower(urldecode($categoryName), 'UTF-8');

        foreach ($allPosts as $post) {
            if (!empty($post['categories']) && is_array($post['categories'])) {
                $lowerPostCategories = array_map('mb_strtolower', $post['categories']);
                if (in_array($lowerCategoryName, $lowerPostCategories)) {
                    $filteredPosts[] = $post;
                }
            }
        }
        return $filteredPosts;
    }

    /**
     * Получает все категории постов
     * @return array - массив всех категорий
     */
    public function getAllPostCategories()
    {
        $allPosts = $this->getPublishedPosts();
        $allCategories = [];
        foreach ($allPosts as $post) {
            if (!empty($post['categories']) && is_array($post['categories'])) {
                $allCategories = array_merge($allCategories, $post['categories']);
            }
        }
        return array_values(array_unique(array_filter($allCategories)));
    }

    /**
     * Получает отфильтрованные посты
     * @param string|null $category - категория для фильтрации (по slug)
     * @param string|null $status - статус для фильтрации ('published', 'draft', 'all')
     * @return array - отфильтрованные посты
     */
    public function getFilteredPosts($category = null, $status = null)
    {
        $allPosts = $this->getAllPosts();
        
        // Фильтрация по статусу
        if ($status && $status !== 'all') {
            $allPosts = array_filter($allPosts, function($post) use ($status) {
                return isset($post['status']) && $post['status'] === $status;
            });
        }
        
        // Фильтрация по категории (по slug категории)
        if ($category && $category !== 'all') {
            $allPosts = array_filter($allPosts, function($post) use ($category) {
                // Посты хранят категории как slug, поэтому сравниваем напрямую
                return !empty($post['categories']) && is_array($post['categories']) && in_array($category, $post['categories']);
            });
        }
        
        // Сортировка по дате создания (новые первыми)
        usort($allPosts, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });
        
        return $allPosts;
    }

    // --- Методы для работы с категориями ---

    /**
     * Получает все категории
     * @return array - массив всех категорий
     */
    public function getAllCategories()
    {
        return $this->data;
    }

    /**
     * Находит категорию по ID
     * @param int $id - ID категории
     * @return array|null - данные категории или null
     */
    public function getCategoryById($id)
    {
        foreach ($this->data as $category) {
            if (isset($category['id']) && $category['id'] == $id) {
                return $category;
            }
        }
        return null;
    }

    /**
     * Находит категорию по slug
     * @param string $slug - slug категории
     * @return array|null - данные категории или null
     */
    public function getCategoryBySlug($slug)
    {
        foreach ($this->data as $category) {
            if (isset($category['slug']) && $category['slug'] === $slug) {
                return $category;
            }
        }
        return null;
    }

    /**
     * Создает новую категорию
     * @param array $data - данные для создания категории
     * @return bool - успешность операции
     */
    public function createCategory($data)
    {
        $maxId = 0;
        foreach ($this->data as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        $newId = $maxId + 1;

        $slug = $this->generateUniqueCategorySlug($data['slug'] ?? '', $data['name'] ?? 'category');

        $newCategory = [
            'id' => $newId,
            'name' => $data['name'] ?? 'Новая категория',
            'h1' => $data['h1'] ?? '',
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'seo_title' => $data['seo_title'] ?? '',
            'seo_description' => $data['seo_description'] ?? ''
        ];

        $this->data[] = $newCategory;
        return $this->save();
    }

    /**
     * Обновляет существующую категорию
     * @param int $id - ID категории для обновления
     * @param array $data - новые данные
     * @return bool - успешность операции
     */
    public function updateCategory($id, $data)
    {
        $categoryFound = false;
        foreach ($this->data as &$item) {
            if ($item['id'] == $id) {
                $slug = $this->generateUniqueCategorySlug($data['slug'] ?? '', $data['name'] ?? 'category', $id);

                $item['h1'] = $data['h1'] ?? $data['name'] ?? $item['name'];
                $item['name'] = $data['name'] ?? $item['name'];
                $item['slug'] = $slug;
                $item['description'] = $data['description'] ?? $item['description'];
                $item['seo_title'] = $data['seo_title'] ?? $item['seo_title'];
                $item['seo_description'] = $data['seo_description'] ?? $item['seo_description'];
                
                $categoryFound = true;
                break;
            }
        }

        if ($categoryFound) {
            return $this->save();
        }
        return false;
    }

    /**
     * Удаляет категорию
     * @param int $id - ID категории для удаления
     * @return bool - успешность операции
     */
    public function deleteCategory($id)
    {
        $this->data = array_filter($this->data, function($item) use ($id) {
            return $item['id'] != $id;
        });
        return $this->save();
    }

    // --- Вспомогательные методы ---

    private function generateUniquePostSlug($slug, $title, $excludeId = null)
    {
        $baseSlug = !empty($slug) ? $this->slugify($slug) : $this->slugify($title);
        $finalSlug = $baseSlug;
        $counter = 2;
        while (true) {
            $existingPost = $this->getPostBySlug($finalSlug);
            if (!$existingPost || ($excludeId !== null && $existingPost['id'] == $excludeId)) {
                break;
            }
            $finalSlug = $baseSlug . '-' . $counter++;
        }
        return $finalSlug;
    }

    private function generateUniqueCategorySlug($slug, $name, $excludeId = null)
    {
        $baseSlug = !empty($slug) ? $this->slugify($slug) : $this->slugify($name);
        $finalSlug = $baseSlug;
        $counter = 2;
        while (true) {
            $existing = $this->getCategoryBySlug($finalSlug);
            if (!$existing || ($excludeId !== null && $existing['id'] == $excludeId)) {
                break;
            }
            $finalSlug = $baseSlug . '-' . $counter++;
        }
        return $finalSlug;
    }

    private function slugify($string)
    {
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