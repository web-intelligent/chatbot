@include('includes.meta')

<div class="gradient-bg"></div>
<div class="main_auth_container">
    <div class="auth-container">
        <div class="glass-card">
            <div class="auth-header">
                <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
                <h1>{{ $meta['title'] }}</h1>
                <div class="sub">Федерация фитнес-аэробики России</div>
            </div>

            <!-- Вкладки: вход / регистрация -->
            <div class="tabs">
                <button class="tab-btn active" id="loginTabBtn">Вход</button>
                <button class="tab-btn" id="registerTabBtn">Регистрация</button>
            </div>

            <!-- Форма Входа -->
            <div id="loginForm" class="form-wrapper">
                <form id="loginFormElement">
                    @csrf
                    <div class="form-group">
                        <label>Адрес электронной почты</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="text" name="email" id="loginEmail" placeholder="example@fitness.ru" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="loginPassword" placeholder="••••••••" autocomplete="current-password">
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1" id="rememberCheckbox"> Запомнить меня
                        </label>
                        <a href="#" class="forgot-link" id="forgotPasswordLink">Забыли пароль?</a>
                    </div>
                    <button type="submit" class="btn-auth"><i class="fas fa-arrow-right-to-bracket"></i> Войти в кабинет</button>
                </form>
{{--                <div class="divider"><span>или через соцсети</span></div>--}}
{{--                <div class="social-buttons">--}}
{{--                    <a href="#" class="social-btn" id="socialVk"><i class="fab fa-vk"></i> VK</a>--}}
{{--                    <a href="#" class="social-btn" id="socialTelegram"><i class="fab fa-telegram"></i> Telegram</a>--}}
{{--                    <a href="#" class="social-btn" id="socialGoogle"><i class="fab fa-google"></i> Google</a>--}}
{{--                </div>--}}
                <div id="loginMessage" class="message-box"></div>
            </div>

            <!-- Форма Регистрации (скрыта по умолчанию) -->
            <div id="registerForm" class="form-wrapper hidden-form">
                <form id="registerFormElement">
                    @csrf
                    <div class="form-group">
                        <label>Полное имя</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="regFullname" placeholder="Иванов Иван Иванович" autocomplete="name" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ник</label>
                        <div class="input-icon">
                            <i class="fa-regular fa-id-badge"></i>
                            <input type="text" id="regFullname" placeholder="fitness_bot" autocomplete="name" name="nick">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="regEmail" placeholder="ivanov@fitness-russia.ru" autocomplete="email" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <div class="input-icon">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" id="regPhone" placeholder="+7 (900) 123-45-67" name="phone">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <div class="input-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="regPassword" placeholder="Минимум 6 символов" autocomplete="new-password" name="password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Подтверждение пароля</label>
                        <div class="input-icon">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" id="regPasswordConfirm" placeholder="Повторите пароль" name="password_confirmation">
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="checkbox-label" style="display: inline-block">
                            <input type="checkbox" name="accept_terms" id="agreeTerms"> Я принимаю <a href="#" style="color:#ff8aae;">условия использования</a> и даю согласие на обработку данных
                        </label>
                    </div>
                    <button type="submit" class="btn-auth"><i class="fas fa-user-plus"></i> Создать аккаунт</button>
                </form>
{{--                <div class="divider"><span>Регистрация через соцсети</span></div>--}}
{{--                <div class="social-buttons">--}}
{{--                    <a href="#" class="social-btn" id="regSocialVk"><i class="fab fa-vk"></i> VK</a>--}}
{{--                    <a href="#" class="social-btn" id="regSocialTelegram"><i class="fab fa-telegram"></i> Telegram</a>--}}
{{--                </div>--}}
                <div id="registerMessage" class="message-box"></div>
            </div>
            <div class="auth-footer">
                <i class="fas fa-shield-alt"></i> Безопасный вход • Данные защищены • Федерация фитнес-аэробики России
            </div>
        </div>
    </div>
</div>

<script>
    // Переключение между формами
    const loginTab = document.getElementById('loginTabBtn');
    const registerTab = document.getElementById('registerTabBtn');
    const loginFormDiv = document.getElementById('loginForm');
    const registerFormDiv = document.getElementById('registerForm');

    function setActiveTab(active) {
        if (active === 'login') {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginFormDiv.classList.remove('hidden-form');
            registerFormDiv.classList.add('hidden-form');
            // очищаем сообщения
            hideAllMessages();
        } else {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerFormDiv.classList.remove('hidden-form');
            loginFormDiv.classList.add('hidden-form');
            hideAllMessages();
        }
    }

    function hideAllMessages() {
        const loginMsg = document.getElementById('loginMessage');
        const regMsg = document.getElementById('registerMessage');
        if(loginMsg) loginMsg.classList.remove('show');
        if(regMsg) regMsg.classList.remove('show');
    }

    loginTab.addEventListener('click', () => setActiveTab('login'));
    registerTab.addEventListener('click', () => setActiveTab('register'));

    // Вспомогательная функция показа сообщений
    function showMessage(element, text, type = 'error') {
        element.textContent = text;
        element.className = `message-box show ${type}`;
        setTimeout(() => {
            if(element.classList.contains('show')) {
                element.classList.remove('show');
            }
        }, 6000);
    }

    // ВАЛИДАЦИЯ И ЭМУЛЯЦИЯ АВТОРИЗАЦИИ/РЕГИСТРАЦИИ (демо-логика, стиль федерации)
    const loginForm = $('#loginFormElement');
    const registerFormElem = $('#registerFormElement');

    // Функция эмуляции "входа"
    // loginForm.addEventListener('submit', (e) => {
    //     e.preventDefault();
    //     const emailOrPhone = document.getElementById('loginEmail').value.trim();
    //     const password = document.getElementById('loginPassword').value.trim();
    //     const remember = document.getElementById('rememberCheckbox').checked;
    //     const msgBox = document.getElementById('loginMessage');
    //
    //     if (!emailOrPhone || !password) {
    //         showMessage(msgBox, '❌ Пожалуйста, заполните email/телефон и пароль.', 'error');
    //         return;
    //     }
    //     // простая эмуляция: проверка, что пароль не менее 3 символов для демо
    //     if (password.length < 3) {
    //         showMessage(msgBox, '⚠️ Неверный пароль. Попробуйте ещё раз.', 'error');
    //         return;
    //     }
    //     // имитация успешного входа
    //     showMessage(msgBox, '✅ Добро пожаловать! Выполняется вход в личный кабинет...', 'success');
    //     // здесь можно было бы перенаправить, но оставим информативно
    //     setTimeout(() => {
    //         alert(`🎉 Добро пожаловать в экосистему федерации!\nСтатус: ${remember ? 'сессия запомнена' : 'гостевая сессия'}\n(Демо-режим, полная авторизация будет доступна после интеграции с бэкендом)`);
    //         // сброс формы опционально
    //         // document.getElementById('loginEmail').value = '';
    //         // document.getElementById('loginPassword').value = '';
    //     }, 800);
    // });


    // Авторизация
    loginForm.off('submit').on('submit', function (e) {
        e.preventDefault();

        const msgBox = document.getElementById('loginMessage');
        let formData = new FormData(loginForm[0]);

        $.ajax({
            url: "{{ route('auth') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success && response.redirect) {
                    showMessage(msgBox, response.message, 'success');
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 800)

                } else {
                    showMessage(msgBox, response.message, 'error');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseJSON)
                let msg = '⚠️ Ошибка авторизации';
                if (xhr.status === 401 || xhr.status === 422) {
                    msg = xhr.responseJSON?.message || msg;
                }
                showMessage(msgBox, msg, 'error');
            }
        });
    });


    // Регистрация
    registerFormElem.off('submit').on('submit', function (e) {
        e.preventDefault();

        const msgBox = document.getElementById('registerMessage');
        let formData = new FormData(registerFormElem[0]);

        $.ajax({
            url: "{{ route('registration') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (!response.success) {
                    showMessage(msgBox, response.message, 'error');
                } else {
                    setTimeout(() => {
                        setActiveTab('login');
                        showMessage(document.getElementById('loginMessage'), 'Аккаунт создан! Теперь войдите, используя email и пароль.', 'success');
                        // очистим поля регистрации
                        registerFormElem.trigger('reset');
                    }, 1800);
                }
            },
            error: function (xhr) {
                // Сработает при ошибках валидации (422) или ошибках сервера (500)
                let errorMessage = 'Произошла ошибка при регистрации';

                if (xhr.status === 422 && xhr.responseJSON) {
                    // Берем первую ошибку валидации Laravel
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat()[0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showMessage(msgBox, errorMessage, 'error');
            }
        });
    });


    // registerFormElem.addEventListener('submit', (e) => {
    //     e.preventDefault();
    //     const fullname = document.getElementById('regFullname').value.trim();
    //     const email = document.getElementById('regEmail').value.trim();
    //     const phone = document.getElementById('regPhone').value.trim();
    //     const password = document.getElementById('regPassword').value.trim();
    //     const confirm = document.getElementById('regPasswordConfirm').value.trim();
    //     const agree = document.getElementById('agreeTerms').checked;
    //     const msgBox = document.getElementById('registerMessage');
    //
    //     if (!fullname || !email || !phone || !password || !confirm) {
    //         showMessage(msgBox, '❗ Все поля обязательны для заполнения.', 'error');
    //         return;
    //     }
    //     if (!email.includes('@') || !email.includes('.')) {
    //         showMessage(msgBox, '📧 Укажите корректный email адрес.', 'error');
    //         return;
    //     }
    //     if (phone.length < 10) {
    //         showMessage(msgBox, '📱 Введите корректный номер телефона.', 'error');
    //         return;
    //     }
    //     if (password.length < 6) {
    //         showMessage(msgBox, '🔐 Пароль должен содержать минимум 6 символов.', 'error');
    //         return;
    //     }
    //     if (password !== confirm) {
    //         showMessage(msgBox, '❌ Пароли не совпадают.', 'error');
    //         return;
    //     }
    //     if (!agree) {
    //         showMessage(msgBox, '📜 Необходимо принять условия использования и согласие на обработку данных.', 'error');
    //         return;
    //     }
    //
    //     // успешная регистрация
    //     showMessage(msgBox, '🎉 Регистрация прошла успешно! Перенаправление на вход...', 'success');
    //     setTimeout(() => {
    //         // имитируем переключение на форму входа и частичное заполнение
    //
    //         ТУТ AJAX
    //
    //         setActiveTab('login');
    //         document.getElementById('loginEmail').value = email;
    //         showMessage(document.getElementById('loginMessage'), 'Аккаунт создан! Теперь войдите, используя email и пароль.', 'success');
    //         // очистим поля регистрации
    //         registerFormElem.reset();
    //     }, 1800);
    // });

    // Социальные кнопки (демо-уведомления)
    const socials = document.querySelectorAll('.social-btn');
    socials.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            alert('🌐 Интеграция с соцсетями будет доступна в ближайшее время. Войдите через email сейчас.');
        });
    });

    // "Забыли пароль" — информационный попап
    const forgotLink = document.getElementById('forgotPasswordLink');
    if(forgotLink) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            alert('📩 Для восстановления пароля обратитесь в техническую поддержку федерации: support@fitness-aerobics.ru или через бота @FitnessAerobicsRussiaBot');
        });
    }

    // дополнительные эффекты: при наведении на табы
    const style = document.createElement('style');
    style.textContent = `
    .input-icon input:-webkit-autofill,
    .input-icon input:-webkit-autofill:focus {
      transition: background-color 600000s 0s, color 600000s 0s;
    }
    .input-icon input[data-autocompleted] {
      background: rgba(20, 25, 40, 0.9);
    }
  `;
    document.head.appendChild(style);
</script>

@include('includes.footer')
