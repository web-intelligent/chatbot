@include('includes.meta')

<div class="gradient-bg"></div>

<div class="chat-app active" id="{{ $chat->id }}">
    <div class="chat-header">
        <div class="logo-area">
            <i class="fas fa-headset" style="color: #ff3b6f; font-size: 1.6rem;"></i>
            <div>
                <div class="title-chat">Чат-бот и инфо-центр ФФАР</div>
            </div>
        </div>
        <div class="status">
            <div class="online-dot"></div>
            <span>Операторы онлайн · 24/7</span>
            <i class="fas fa-shield-alt" style="margin-left: 6px; opacity: 0.7;"></i>
        </div>
        <div>
            @if(\Illuminate\Support\Facades\Auth::check())
                <a style="text-decoration: none; background: #ff6b4a; color: white; padding: 3px 10px; border-radius: 15px; font-size: 14px" href="{{ route('logout') }}">Выход</a>
            @else
                <a style="text-decoration: none; background: #ff6b4a; color: white; padding: 3px 10px; border-radius: 15px; font-size: 14px" href="{{ route('login') }}">Вход</a>
            @endif
        </div>
    </div>

    <!-- Окно сообщений -->
    <div class="messages-container" id="messagesContainer">
        <!-- Приветственное сообщение от поддержки (динамика через js, но продублируем для красоты) -->
        <div class="message support" id="welcomeMsg">
            <div class="avatar"><i class="fas fa-robot"></i></div>
            <div class="bubble">
                <div class="message-text">👋 Здравствуйте! Я — технический ассистент федерации. Чем могу помочь? Расскажите о проблеме или задайте вопрос по боту, регистрации, соревнованиям.</div>
                <div class="quick-replies">
                    <span class="quick-chip" data-msg="Не могу зарегистрироваться на соревнования через бота">📋 Регистрация</span>
                    <span class="quick-chip" data-msg="Бот не отвечает, пишет ошибку">⚠️ Бот не работает</span>
                    <span class="quick-chip" data-msg="Где посмотреть расписание вебинаров?">📅 Расписание</span>
                    <span class="quick-chip" data-msg="Как получить сертификат тренера?">🎓 Сертификаты</span>
                </div>
            </div>
        </div>

        @if(!$user)
            <div class="message support">
                <div class="avatar"><i class="fas fa-headset"></i></div>
                <div class="bubble">
                    <div class="message-text"><a href="{{ route('login') }}">Войдите</a> или <a href="{{ route('login') }}">создайте аккаунт</a> — так мы сможем помочь вам быстрее и эффективнее</div>
                </div>
            </div>
        @endif

        @foreach($messages as $message)
            <div class="message {{ $message->sender_type }}">
                <div class="avatar">
                    @if($message->sender_type == 'user')
                        <i class="fas fa-user"></i>
                    @elseif($message->sender_type == 'bot')
                        <i class="fas fa-robot"></i>
                    @else
                        <i class="fas fa-headset"></i>
                    @endif
                </div>
                <div class="bubble">
                    <div class="message-text" data-message-id="{{ $message->id }}">
                        {!! $message->message !!}
                        @if($message->sender_type == 'user')
                            @if($message->is_read == 0)
                                <i style="font-size: 8px" class="fa-solid fa-check"></i>
                            @else
                                <i style="font-size: 8px; color: limegreen" class="fa-solid fa-check"></i>
                            @endif
                        @endif
                    </div>
                    <div class="message-time" data-time="{{ $message->created_at->toIso8601String() }}"></div>
                </div>
            </div>
        @endforeach


    </div>

    <!-- Индикатор печати (скрыт по умолчанию) -->
    <div style="padding: 0 1.8rem 0.5rem 1.8rem; display: none" id="typing-status">
        <div class="typing-status" style="background: rgba(255,59,111,0.1); border-color: rgba(255,59,111,0.3);">
            <i class="fas fa-user"></i>
            <span>Оператор печатает...</span>
            <div class="dots"><span></span><span></span><span></span></div>
        </div>
    </div>
    <form id="user-send-message">
        <div class="input-area">
            <div class="input-wrapper">
                <input type="hidden" name="chat_id" value="{{ $chat->id }}">
                <input type="text" name="message" id="messageInput" placeholder="Напишите сообщение..." autocomplete="off">
                {{--            <button class="attach-btn" id="attachBtn"><i class="fas fa-paperclip"></i></button>--}}
                <button class="send-btn" id="sendBtn"><i class="fas fa-paper-plane" style="font-size: 16px"></i></button>
            </div>
        </div>
    </form>
    <div class="footer-chat">
        <i class="fas fa-lock" style="font-size: 0.6rem;"></i> Конфиденциально. Ваши данные защищены.
    </div>
</div>

<script type="module">

    const chatId = {{ $chat->id ?? 'null' }};

    function checkReadMessages() {
        if (
            document.visibilityState === 'visible' &&
            document.hasFocus()
        ) {
            console.log('Чат реально виден пользователю');
            // readAllVisibleMessages();
            const IDS = [];
            $('.message .message-text[data-message-id]').each(function () {
                IDS.push($(this).data('message-id'));
            });

            ReadMessages(1, "{{ route('chat.read.message') }}", IDS, chatId)
        }
    } checkReadMessages()

    document.addEventListener('visibilitychange', checkReadMessages);
    window.addEventListener('focus', checkReadMessages);

    /*
    * Помечаем сообщение прочитанным
    * */

    {{--ReadMessages(1, "{{ route('chat.read.message') }}", IDS);--}}

    /*
    * ПОДКЛЮЧЕНИЕ К ПРОСЛУШИВАНИЮ ПРОЧИТАННЫХ СООБЩЕНИЙ
    * */

    ChatRealtime.connectToChat(chatId);

    window.Echo.private('read_message.' + chatId)
        .listen('.read_message', function (e) {
            if(e.messages) {
                e.messages.forEach(function (msg) {
                    if (msg.is_read === 1) {
                        // Находим сообщение по data-message-id
                        const $messageText = $(`.message-text[data-message-id="${msg.id}"]`);

                        if ($messageText.length) {
                            // Меняем цвет иконки внутри этого сообщения
                            $messageText.find('i.fa-check').css('color', 'limegreen');
                        }
                    }
                })
            }
        })



    /*
    * Прослушивание канала общения с техподдержкой
    * */
    window.Echo.private('support_send_message.' + chatId).listen('.support_actions', function (e) {
        if (e.status && e.type == 'message') {
            addMessage(e)
        }
    })

    /*
    * Прослушивание канала на прочитанное сообщение
    * */
    window.Echo.private('support_read_message.' + chatId).listen('.support_actions', function (e) {
        console.log(e)
        if (e.status && e.message?.id) {
            // Находим иконку внутри конкретного сообщения и красим её
            setTimeout(function () {
                $('#messagesContainer')
                    .find('.message-text[data-message-id="' + e.message.id + '"] i')
                    .css('color', 'limegreen');
            }, 3000)
        }
    })


    /*
    * Отображение кто онлайн (НАДО ДОРАБОТАТЬ)
    * */
    window.Echo.join('online-users')
        .here(users => console.log('HERE-2', users))
        .joining(user => console.log('JOINING-2', user))
        .error(err => console.log('ERR-2', err));


    // ---------- ЛОГИКА ЧАТА: пользователь + имитация ответа поддержки ----------
    const messagesContainer = $('#messagesContainer');
    const messageInput = $('#messageInput');
    const sendBtn = $('#sendBtn');
    const attachBtn = $('#attachBtn');
    const typingDiv = $('#typingArea');
    const user = @json($user ?? null);
    {{--const chatId = {{ $chat->id ?? 'null' }};--}}


    /*
    * Пользователь печатает
    * */
    let typingResetTimeout = null;
    let typingSent = false;

    $('input[name="message"]').on('input', function () {
        const chatId = $(this).parent().find('input[name="chat_id"]').val();

        if (!typingSent) {
            ChatRealtime.sendTyping(chatId, user.id);
            typingSent = true;
        }

        clearTimeout(typingResetTimeout);

        typingResetTimeout = setTimeout(() => {
            typingSent = false;
        }, 1000);
    });

    /*
    * Отправка сообщения пользователем
    * */
    $('#user-send-message').off('submit').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData($('#user-send-message')[0]);

        $.ajax({
            url: "{{ route('chat.send.message') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('body').append('<div id="load_wrap" style="position: fixed; width: 100%; height: 100%; z-index: 10000000; top: 0;' +
                    'left: 0; "></div>')
            },
            success: function (response) {
                if (response.status) {

                    $('#user-send-message').trigger('reset');

                    addMessage({
                        message: response.message
                    });

                    addMessage({
                        message: response.ai_message
                    });

                    $('#load_wrap').hide()
                }
            }
        });
    })

</script>

@include('includes.footer')
