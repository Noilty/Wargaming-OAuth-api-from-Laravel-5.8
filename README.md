# Wargaming-OAuth-api-from-Laravel-5.8
## Нужно добавить маршрут (роут) в файл routes/web.php
```php
Route::get('wot/{region}/auth/login', [
    \App\Http\Controllers\Wargaming\AuthController::class, 'login'
])->name('wot.auth.login');
```
