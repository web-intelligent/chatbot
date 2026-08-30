<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    //
    public function login() {
        $meta = [
            'title' => 'Регистрация/авторизация пользователя'
        ];
        return view('login', compact('meta'));
    }

    /*
     * Регистрация пользователя
     * */
    public function registration(Request $request) {
        if ($request->isMethod('post') && $request->ajax()) {
            try {
                $validated = $request->validate([
                    'accept_terms'     => 'accepted|required',
                    'name'             => 'required|string|max:255',
                    'nick'             => 'required|string|max:55',
                    'email'            => 'required|email|unique:users,email',
                    'phone'            => 'required|string|regex:/^\+7\s\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}$/',
                    'password' => [
                        'required',
                        'confirmed',
                        Password::min(8)  // Минимальная длина пароля
                        ->letters()  // Должен содержать хотя бы одну букву
                        ->numbers()  // Должен содержать хотя бы одну цифру
                        ->uncompromised(),  // Проверка на утечку пароля
                    ],
                ], [
                    'accept_terms.required'     => 'Вы должны принять условия использования и дать согласие на обработку персональных данных',
                    'accept_terms.accepted'     => 'Вы должны принять условия использования и дать согласие на обработку персональных данных',
                    'name.required'             => 'Укажите ваше имя.',
                    'nick.required'             => 'Укажите никнейм.',
                    'email.required'            => 'Укажите email.',
                    'email.email'               => 'Некорректный email.',
                    'email.unique'              => 'Этот email уже зарегистрирован.',
                    'phone.required'            => 'Укажите телефон.',
                    'phone.regex'               => 'Телефон должен быть в формате +7 (XXX) XXX XX XX.',
                    'password.required'         => 'Поле "Пароль" обязательно',
                    'password.confirmed'         => 'Поле "Пароль" и "Повторите пароль" должны совпадать',
                ]);

                $user = User::create([
                    'email' => strip_tags(trim($validated['email'])),
                    'name' => strip_tags(trim($validated['name'])),
                    'nick' => strip_tags(trim($validated['nick'])),
                    'phone' => strip_tags(trim($validated['phone'])),
                    'password' => Hash::make(trim($validated['password'])),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Регистрация прошла успешно!',
                    'user'    => $user
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Первая ошибка для показа в msgBox
                $firstError = collect($e->errors())->first()[0];

                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors'  => $e->errors()  // все ошибки по полям
                ], 422);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Доступ запрещён'
        ], 403);
    }

    /*
     * Авторизация порльзователя
     * */
    public function auth(Request $request)
    {
        if (!$request->isMethod('post') || !$request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещён'
            ], 403);
        }

        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'remember' => 'integer|max:1',
        ], [
            'email.required'    => 'Необходимо указать адрес электронной почты',
            'email.email'       => 'Указан неверный формат адреса электронной почты',
            'password.required' => 'Необходимо указать пароль',
            'remember.boolean'  => 'Неверное значение в поле "Запомнить меня"'
        ]);

        if (!Auth::attempt([
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {

            return response()->json([
                'success' => false,
                'message' => 'Неверный email или пароль'
            ], 401);
        }

        // Успешный вход — регенерируем сессию
        $request->session()->regenerate();

        // Получаем роль (только что залогиненного пользователя)
        $user = Auth::user();

        // Определяем куда редиректить
        $redirectUrl = match ($user->role) {
//            'admin'  => route('admin.dashboard'),
//            'seller' => route('seller.panel'),
//            'user', 'customer', null => route('profile'),
            1, 2 => route('support.index'),
            default  => route('home'),
        };

        if ($user->role == 0) {
            $chat= Chat::where('user_id', $user->id)->where('status', 'open')->first();
            if ($chat) {
                $redirectUrl = route('chat.index', $chat->id);
            }
        }


        return response()->json([
            'success'     => true,
            'message'     => 'Вы авторизовались успешно',
            'redirect'    => $redirectUrl,
            'role'        => $user->role
        ]);
    }

    /*
     * Выход пользователя
     * */

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }

}
