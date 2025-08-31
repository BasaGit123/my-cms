<?php

// Подключаем базовый класс модели
require_once ROOT . '/app/models/Model.php';

/**
 * Модель для работы с пользователями (User)
 * Отвечает за операции, связанные с пользователями, в основном - за их поиск.
 */
class User extends Model
{
    /**
     * Конструктор.
     * Указывает, с каким файлом данных будет работать эта модель.
     */
    public function __construct()
    {
        // Передаем в родительский конструктор путь к файлу с пользователями
        parent::__construct('data/users.json');
    }

    /**
     * Находит пользователя по его имени (логину).
     * Это ключевая функция для системы аутентификации.
     * @param string $username - Логин пользователя для поиска
     * @return array|null - Возвращает массив с данными пользователя или null, если он не найден
     */
    public function findByUsername($username)
    {
        foreach ($this->data as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                return $user;
            }
        }
        return null; // Пользователь не найден
    }

    /**
     * Обновляет данные пользователя (логин и/или пароль).
     * @param int $id - ID пользователя для обновления
     * @param array $newData - Массив с новыми данными ('username', 'password')
     * @return bool
     */
    public function updateUser($id, $newData)
    {
        $userFound = false;
        foreach ($this->data as &$user) { // Обратите внимание на & - работаем с элементом по ссылке
            if (isset($user['id']) && $user['id'] == $id) {
                // Обновляем логин, если он передан и не пуст
                if (!empty($newData['username'])) {
                    $user['username'] = $newData['username'];
                }
                // Обновляем пароль, если он передан и не пуст
                if (!empty($newData['password'])) {
                    $user['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
                }
                $userFound = true;
                break;
            }
        }

        if ($userFound) {
            return $this->save(); // Метод save() унаследован от базовой модели Model
        }

        return false;
    }
}
