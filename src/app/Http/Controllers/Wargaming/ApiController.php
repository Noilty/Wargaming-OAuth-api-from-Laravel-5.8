<?php

namespace App\Http\Controllers\Wargaming;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    private $auth_url;
    private $prolongate;
    private static $application_id = '';
    private static $nofollow = 1;
    private static $expires = 86400; // 60*60*24*1 = 86400 - one day

    /**
     * Доступные регионы для регистрации
     */
    protected $arrRegions = [
        'ru',
        'eu',
        'com',
        'asia'
    ];

    /**
     * Проверка региона регистрации
     *
     * @param string $region
     * @return bool
     */
    public function checkingRegion(string $region)
    {
        if ( in_array($region, $this->arrRegions) ) {

            return true;
        }

        return false;
    }

    private function redirect(string $url)
    {
        header('HTTP/1.1 301 Moved Permanently');
        header("Location:".$url);
        die();
    }

    private function call(array $params = [])
    {
        if ( empty($params) ) {

            die('Неправильные параметры.');
        }

        return stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params)
                ]
            ]);
    }

    public function get_token(string $region)
    {
        if ( !$this->checkingRegion($region) ) {

            return abort(404);
        }

        $this->auth_url = 'https://api.worldoftanks.'.$region.'/wot/auth/login/';

        $json = file_get_contents($this->auth_url, false, $this->call([
            'nofollow' => self::$nofollow,
            'expires_at' => 300,
            'redirect_uri' => config('app.url') . '/wot/' . $region. '/auth/login/',
            'application_id' => self::$application_id,
            'display' => 'popup'
        ]));

        $json = json_decode($json, true);

        if ( $json['status'] === 'ok' ) {

            $this->redirect($json['data']['location']);
        }

        return die('Не была получена ссылка для перенаправления.');
    }

    public function get_auth_data(string $region)
    {
        if ( $_GET['status'] !== 'ok' ) {

            $error_code = 500;

            if ( preg_match('/^[0-9]+$/u', $_GET['code']) ) {

                $error_code = $_GET['code'];
            }

            die("Ошибка авторизации. Код ошибки: $error_code");
        } elseif ( $_GET['expires_at'] < time() ) {
            // Ошибка авторизации
            die("Срок действия access_token истек. <a href='/'>Назад</a>");
        } else {
            // Подтверждаем правдивость полученных параметров
            $this->prolongate = 'https://api.worldoftanks.'.$region.'/wot/auth/prolongate/';

            $json = file_get_contents($this->prolongate, false, $this->call([
                'expires_at' => time() + self::$expires,
                'access_token' => $_GET['access_token'],
                'application_id' => self::$application_id
            ]));

            return json_decode($json, true);
        }
    }
}