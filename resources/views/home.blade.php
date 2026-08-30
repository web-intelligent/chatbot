@php use App\Models\Chat;use Illuminate\Support\Facades\Auth; @endphp
@include('includes.meta')
<div class="gradient-orb"></div>

<div class="container">
    <!-- герой с инфо о боте -->
    <div class="hero">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; margin-bottom: 15px">
            <div class="badge-bot">
                <i class="fas fa-robot"></i> ЧАТ-БОТ
            </div>
            <div>
                @if(\Illuminate\Support\Facades\Auth::check())
                    <a class="btn-auth" style="text-align: right; text-decoration: none" href="{{ route('logout') }}">Выход</a>
                @else
                    <a class="btn-auth" style="text-align: right; text-decoration: none" href="{{ route('login') }}">Вход</a>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center">
            <img style="width: 80px; height: 80px; border-radius: 100%; margin-right: 15px" src="{{ asset('public/images/chat_bot_red.jpg') }}" alt="Федерация фитнес-аэробики России">
            <h1> Официальный чат-бот и инфо-центр ФФАР</h1>
        </div>

        <form id="send_message" method="POST" action="{{ route('chat.user.send') }}" style="position: relative">
            @csrf

            @if(Auth::check())

                @php $chats = Chat::where('user_id', Auth::id())->get();@endphp
                @if($chats)
                    @foreach($chats as $chat)
                        <a class="btn-primary-main" href="{{ route('chat.index', $chat->id) }}">{{ (!is_null($chat->title)) ? $chat->title : 'Перейти в чат' }}</a>
                    @endforeach
                @endif

                <div class="input-area">
                    <div class="input-wrapper">
                        <input type="text" id="messageInput" name="user_message" placeholder="Напишите сообщение..."
                               autocomplete="off">
                        <button class="send-btn" id="sendBtn"><i class="fas fa-paper-plane" style="font-size: 16px"></i>
                        </button>
                    </div>
                </div>
            @else
                <a style="background: #ff3b6f; padding: 15px 25px; border-radius: 25px; color: #fff; text-decoration: none; font-weight: bold"
                   href="{{ route('login') }}">Авторизуйтесь, чтобы начать общение</a>
            @endif


        </form>
    </div>

    <!-- Карточки возможностей / преимущества -->
    <div class="grid-cols">
        <div class="info-card">
            <div class="card-icon"><i class="fas fa-comment-dots"></i></div>
            <h3>ИИ-консультант 24/7</h3>
            <p>Ответы на часто задаваемые вопросы, вопросы о правилах, судействе, экипировке, музыкальном сопровождении, обучение — моментально в чат-боте.</p>
            <ul class="feature-list">
                <li><i class="fas fa-microphone-alt"></i> Голосовые подсказки</li>
                <li><i class="fas fa-database"></i> База знаний ФФАР</li>
            </ul>
        </div>
        <div class="info-card">
            <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>Календарь стартов</h3>
            <p>Чемпионаты, кубки, всероссийские соревнования. Бот всегда подскажет даты, регламенты и
                результаты.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Уведомления о регистрации</li>
                <li><i class="fas fa-chart-line"></i> Прямые эфиры и протоколы</li>
            </ul>
        </div>
        <div class="info-card">
            <div class="card-icon"><i class="fas fa-graduation-cap"></i></div>
            <h3>Обучение и вебинары</h3>
            <p>Профессиональная переподготовка, повышение квалификации. Семинары для судей, мастер-классы от элит-тренеров, онлайн-курсы по фитнес-аэробике. Доступ через
                бота.</p>
            <ul class="feature-list">
                <li><i class="fas fa-video"></i> Обучение. Методическая база</li>
                <li><i class="fas fa-id-card"></i> Аккредитация судей</li>
            </ul>
        </div>
        <div class="info-card">
            <div class="card-icon"><i class="fa-solid fa-file"></i></div>
            <h3>Аккредитация</h3>
            <p>Порядок проведения государственной аккредитации региональных общественных организаций или структурных подразделений (региональных отделений) общероссийской спортивной федерации для наделения их статусом региональных спортивных федераций</p>
            <ul class="feature-list">
                <li><i class="fa-solid fa-stamp"></i> Первичная аккредитация</li>
                <li><i class="fa-solid fa-stapler"></i> Повторная аккредитация</li>
            </ul>
        </div>
        <div class="info-card">
            <div class="card-icon"><i class="fa-solid fa-chart-line"></i></div>
            <h3>Региональное развитие</h3>
            <p>Создание региональной федерации позволяет системно развивать фитнес-аэробику, проводить официальные соревнования, выстраивать взаимодействие с органами власти и включаться в деятельность Общероссийской спортивной федерации.</p>
            <ul class="feature-list">
                <li><i class="fa-solid fa-chart-diagram"></i> Региональное отделение ФФАР</li>
                <li><i class="fa-solid fa-users-gear"></i> Региональная общественная организация</li>
            </ul>
        </div>
        <div class="info-card">
            <div class="card-icon"><i class="fas fa-trophy"></i></div>
            <h3>Документы и рейтинги</h3>
            <p>Актуальные документы, реестры тренеров, судей, спортсменов. Рейтинг спортсменов.</p>
            <ul class="feature-list">
                <li><i class="fas fa-file-alt"></i> Актуальная нормативная база</li>
                <li><i class="fas fa-chart-simple"></i> Мониторинги и рейтинги</li>
            </ul>
        </div>
    </div>

    <!-- актуальные новости / лента -->
    <div style="margin: 2rem 0 1rem;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.2rem;">
            <i class="fas fa-newspaper" style="color: var(--accent); font-size: 1.5rem;"></i>
            <h2 style="font-size: 1.7rem; font-weight: 600;">Новости федерации</h2>
        </div>
        <div id="newsFeed">
            <!-- динамически загрузим примеры новостей через JS, но можно и статично -->
            <div class="news-item"><strong>🏆 2 июня 2026</strong> — Открыта предварительная регистрация на учебно-тренировочные сборы в Анапе!
            </div>
            <div class="news-item"><strong>📢 1 июня 2026</strong> — С днём Защиты детей.
            </div>
            <div class="news-item"><strong>🔥 31 мая 2026</strong> — Фитнес-аэробика в Лужниках: праздник, который запомнится надолго!
            </div>
            <div class="news-item"><strong>🤖 25 мая 2026</strong> — Федерация фитнес-аэробики в МАХ!
            </div>
        </div>
    </div>

    <!-- таблица-график соревнований стильная -->
    <div style="margin: 3rem 0 2rem;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem;">
            <i class="fas fa-clock" style="color: var(--accent);"></i>
            <h2 style="font-size: 1.6rem;">Ближайшие старты 2026</h2>
        </div>
        <div class="glass-card" style="overflow-x: auto; padding: 0.2rem;">
            <table style="width: 100%; border-collapse: collapse; min-width: 550px;">
                <thead>
                <tr>
                    <th style="text-align: left; padding: 1.2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        Событие
                    </th>
                    <th style="text-align: left; padding: 1.2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        Дата
                    </th>
                    <th style="text-align: left; padding: 1.2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        Город
                    </th>
                    <th style="text-align: left; padding: 1.2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        Бот-регистрация
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong>Чемпионат
                            России</strong></td>
                    <td style="padding: 1rem;">28-31 мая 2026</td>
                    <td style="padding: 1rem;">Москва</td>
                    <td style="padding: 1rem;"><span class="highlight"><i class="fas fa-check-circle"></i> открыт</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong>Первенство России</strong></td>
                    <td style="padding: 1rem;">12-14 июня 2026</td>
                    <td style="padding: 1rem;">Санкт-Петербург</td>
                    <td style="padding: 1rem;"><span class="highlight"><i class="fas fa-calendar-week"></i> скоро</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong>Финал кубка России</strong></td>
                    <td style="padding: 1rem;">5-7 июля 2026</td>
                    <td style="padding: 1rem;">Анапа</td>
                    <td style="padding: 1rem;"><span class="highlight"><i class="fas fa-robot"></i> в боте</span></td>
                </tr>
                <tr>
                    <td style="padding: 1rem;"><strong>Всероссийский фестиваль «Я выбираю спорт»</strong></td>
                    <td style="padding: 1rem;">20-23 августа 2026</td>
                    <td style="padding: 1rem;">Казань</td>
                    <td style="padding: 1rem;"><span class="highlight">регистрация с июня</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- партнёры / цитата -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1.5rem; margin: 3rem 0 0.5rem;">
        <div
            style="background: rgba(255,255,255,0.02); border-radius: 2rem; padding: 1.2rem 1.8rem; text-align: center;">
            <i class="fas fa-heart" style="color: #ff3b6f;"></i>
            <span style="margin-left: 8px;">Официальный партнёр: Минспорт РФ</span>
        </div>
        <div
            style="background: rgba(255,255,255,0.02); border-radius: 2rem; padding: 1.2rem 1.8rem; text-align: center;">
            <i class="fas fa-users"></i> <span>Более 3500 участников в системе</span>
        </div>
        <div style="background: rgba(255,255,255,0.02); border-radius: 2rem; padding: 1.2rem 1.8rem;">
            <i class="fas fa-globe"></i> <span>Цифровая корпоративная платформа ФФАР</span>
        </div>
    </div>

    <div class="footer">
        <div>© 2026 Федерация фитнес-аэробики России. Все права защищены.</div>
        <div class="social-links">
{{--            <a href="#"><i class="fab fa-telegram"></i></a>--}}
{{--            <a href="#"><i class="fab fa-vk"></i></a>--}}
            {{--            <a href="#"><i class="fab fa-youtube"></i></a>--}}
            {{--            <a href="#"><i class="fab fa-instagram"></i></a>--}}
        </div>
    </div>
</div>


<script type="module">
    // window.Echo.channel('typing')
    //     .listen('UserTypeEvent', (e) => {
    //         console.log('Получено:', e);
    //     });
    //
    // $('input[name="ask"]').on('change keyup', function () {
    //
    //     $.ajax({
    //         url: '/is-typing',
    //         method: "POST",
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: {
    //             'message' : $(this).val()
    //         },
    //         success: function (response) {
    //             console.log(response)
    //         }
    //     });
    // })

    $('#send_message').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData($('form')[0]);

        $.ajax({
            url: "{{ route('chat.user.send') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status === true) {
                    window.location.href = '/chat/' + response.chat_id
                }
            }
        });

    })
</script>

@include('includes.footer')
