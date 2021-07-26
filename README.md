# Wargaming-OAuth-api-from-Laravel-5.8
## Схема для миграции Users
```sql
Schema::create('users', function (Blueprint $table) {
    $table->bigIncrements('id')->unsigned();
    $table->string('wg_nickname');
    $table->string('wg_region');
    $table->bigInteger('wg_account_id')->unique()->unsigned();
    $table->string('wg_access_token');
    $table->timestamp('wg_expires_at');
    $table->string('email')->nullable(true)->unique();
    $table->timestamp('email_verified_at')->nullable(true);
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```
## Создайте приложение на сайте www.developers.wargaming.net/applications/
* Возьмите его ID и пропишите его в файле ApiController.php поле $application_id
## Нужно добавить маршрут (роут) в файл routes/web.php
```php
Route::get('wot/{region}/auth/login', [
    \App\Http\Controllers\Wargaming\AuthController::class, 'login'
])->name('wot.auth.login');
```
