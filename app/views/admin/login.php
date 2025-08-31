<div class="row justify-content-center mt-5">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body p-4">
                <h1 class="card-title text-center mb-4">Вход в панель управления</h1>
                
                <?php // Если есть сообщение об ошибке, выводим его
                if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/admin/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Войти</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <p class="text-muted">Логин по умолчанию: <strong>vasa</strong><br>Пароль по умолчанию: <strong>123456</strong></p>
        </div>
    </div>
</div>
