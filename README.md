# Wargaming-OAuth-api-from-Laravel-5.8
## Создайте приложение на сайте www.developers.wargaming.net/applications/
* Возьмите его ID и пропишите его в файле ApiController.php поле $application_id
## Нужно добавить маршрут (роут) в файл routes/web.php
```php
Route::get('wot/{region}/auth/login', [
    \App\Http\Controllers\Wargaming\AuthController::class, 'login'
])->name('wot.auth.login');
```
