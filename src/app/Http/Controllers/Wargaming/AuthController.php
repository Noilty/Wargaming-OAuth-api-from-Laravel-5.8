<?php

namespace App\Http\Controllers\Wargaming;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Вход с идентификатором Wargaming ID
     *
     * @param string $region
     * @param User $user
     * @return mixed
     */
    public function login(string $region, User $user)
    {
        $wargaming = new ApiController();

        if ( empty($_GET['status']) ) {

            $wargaming->get_token($region);
        }

        if (isset($_GET['status']) &&
            isset($_GET['access_token']) &&
            isset($_GET['nickname']) &&
            isset($_GET['account_id']) &&
            isset($_GET['expires_at'])) {

            $data = $wargaming->get_auth_data($region);

            if ($data['status'] === 'ok') {
                $access_token = $data['data']['access_token'];
                $expires_at = $data['data']['expires_at'];
                $account_id = $data['data']['account_id'];
                $nickname = $_GET['nickname'];

                $searchUser = $user->query()
                    ->where('wg_account_id','=',$account_id)
                    ->limit(1)
                    ->get();

                # Слово добавлено 31.03.2021
                $secret_word = 'Секретное слово'; // Не менять!
                $password = $account_id.$secret_word.$region;

                if ( sizeof($searchUser) ) {
                    // Если пользователь найден обновляем данные либо авторизуем его
                    $data = [
                        'wg_nickname' => $nickname,
                        'wg_access_token' => $access_token,
                        'wg_expires_at' => date('Y-m-d H:i:s', $expires_at),
                    ];

                    if ( !$user::findOrFail($searchUser[0]->id)->update($data) ) {
                        // Не удалось обновить данные, авторизация невозможна!
                        return redirect()
                            ->route('index')
                            ->with('danger','Не удалось обновить данные, авторизация невозможна!');
                    }

                    if ( \Auth::attempt(['account_id' => $account_id, 'password' => $password], false) ) {
                        // Аутентификация успешна...
                        return redirect()
                            ->route('index')
                            ->with('success','С возвращением, '.$nickname.'!');
                    }
                }

                $user->wg_region = $region;
                $user->wg_nickname = $nickname;
                $user->wg_account_id = $account_id;
                $user->wg_access_token = $access_token;
                $user->wg_expires_at = date('Y-m-d H:i:s', $expires_at);
                $user->password = Hash::make($password);

                if ( $user->save() ) {
                    // Сохранение успешно, можно проходить Аутентификацию
                    if ( \Auth::attempt(['wg_account_id' => $account_id, 'password' => $password], false) ) {
                        // Аутентификация успешна...
                        return redirect()
                            ->route('index')
                            ->with('info','Пользователь, '.$nickname.' успешно добавлен. Вход выполнен!');
                    }
                    // Аутентификация провалилась...
                    return redirect()
                        ->route('index')
                        ->with('danger','Аутентификация провалилась!');
                }
                // Не удалось сохранить пользователя!
                return redirect()
                    ->route('index')
                    ->with('danger','Не удалось сохранить пользователя!');
            } else {
                // Токен от ВГ протух (Нужно почистить Кэш в браузере)
                return redirect()
                    ->route('index')
                    ->with('danger','INVALID_ACCESS_TOKEN');
            }
        } else {
            $error_code = 500;

            if ( preg_match('/^[0-9]+$/u', $_GET['code']) ) {

                $error_code = $_GET['code'];
            }

            return redirect()
                ->route('index')
                ->with('danger','Ошибка ' . $error_code);
        }
    }
}