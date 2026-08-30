@include('includes.meta')

    <div class="gradient-bg"></div>

    <div class="support-dashboard">
        <!-- ЛЕВАЯ ПАНЕЛЬ: СПИСОК ЧАТОВ (виджет, где появляются чаты от пользователей) -->
        <div class="chats-sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-comments"></i> Чаты поддержки</h3>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="badge-online">
                        <i class="fas fa-circle"></i> 5 активных диалогов
                    </div>
                    <div>
                        @if(\Illuminate\Support\Facades\Auth::check())
                            <a style="text-decoration: none; background: #ff6b4a; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px" href="{{ route('logout') }}">Выход</a>
                        @else
                            <a style="text-decoration: none; background: #ff6b4a; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px" href="{{ route('login') }}">Вход</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="search-chats">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Поиск по пользователям...">
                </div>
            </div>
            <div class="chat-list">
                <!-- карточка чата с новым сообщением (активный пользователь) -->
                @foreach($chats as $chat)
                    <div class="chat-item" id="{{ $chat->id }}" data-user-id="{{ $chat->user_id }}">
                        <div class="chat-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="chat-info">
                            <div class="chat-name">
                                <span>{{ $chat->user?->name ?? 'Гость' }} <span class="status_elem"></span></span>
                                <span class="time-badge">{{ date('d.m.Y', strtotime($chat->created_at)) }}</span>
                            </div>
                            <div class="last-message">
                                <span class="last_message_text">{{ $chat->lastMessage?->message ?? 'Нет сообщений' }}</span>
                                @if($chat->unread_messages_count > 0)
                                    <span class="unread-badge">{{ $chat->unread_messages_count }}</span>
                                @endif
                                <span class="is_typing_text"></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- лёгкий разделитель с количеством операторов -->
            <div class="separator" style="margin: 0 1rem;"></div>
            <div style="padding: 0.9rem 1.2rem; font-size: 0.7rem; color: #7a85a0; border-top: 1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-headset"></i> 3 оператора в сети · SLA 2 мин
            </div>
        </div>

        <!-- ПРАВАЯ ЧАСТЬ: ОКНО ПЕРЕПИСКИ С ВЫБРАННЫМ ПОЛЬЗОВАТЕЛЕМ -->
        <div class="chat-window">
            <!-- шапка активного диалога -->
            <div class="chat-window-header"></div>
            <!-- область сообщений (история переписки) -->
            <div id="messagesContainer" class="messages-area"></div>

            <!-- индикатор "оператор набирает сообщение" (демонстрация виджета) -->
            <div style="padding: 0 1.8rem 0.5rem 1.8rem; display: none" id="typing-status">
                <div class="typing-status" style="background: rgba(255,59,111,0.1); border-color: rgba(255,59,111,0.3);">
                    <i class="fas fa-user"></i>
                    <span>{{ $chat->user?->name ?? 'Гость' }} печатает...</span>
                    <div class="dots"><span></span><span></span><span></span></div>
                </div>
            </div>

            <!-- поле ввода нового сообщения (для ответа от поддержки) -->
            <form id="support_send_form" method="POST">
                <div class="message-input-area">
                    @csrf
                    <i class="fas fa-paperclip attach-icon"></i>
                    <div class="input-container">
                        <input type="hidden" name="chat_id">
                        <input type="text" name="message" placeholder="Написать ответ в этот чат..." autocomplete="off">
                        <button type="submit" class="send-icon">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
    {{--                <i class="fas fa-smile-wink attach-icon" style="font-size: 1.2rem;"></i>--}}
                </div>
            </form>
        </div>
    </div>


<script type="module">

    /*
    * ============================================== START SUPPORT
    * */

    // Техподдержка отправляет сообщение
    $('#support_send_form').off('submit').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData($('#support_send_form')[0]);

        $.ajax({
            url: "{{ route('support.send.message') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log(response)
                if (response.status) {
                    $('#support_send_form').trigger('reset');
                    addMessage(response, null, 'support')
                }
            },
            error: function (xhr) {
                // Сработает при ошибках валидации (422) или ошибках сервера (500)
                let errorMessage = 'Произошла ошибка при отправки сообщения';

                if (xhr.status === 422 && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message
                }

                $('.typing-status').html(errorMessage)
                $('#typing-status').show(300);
                setTimeout( (e) => {
                    $('#typing-status').hide(300);
                }, 3000)
            }
        });
    })

    /*
    * ============================================== END SUPPORT
    * */


    /*
    * Пользователь печатает
    * */
    let supportTypingTimeout = null;
    let supportTypingSent = false;

    $('input[name="message"]').on('input', function () {

        // ID активного чата
        const chatId = $('.chat-item.active').attr('id');
        const userId = $('.chat-item.active').attr('data-user-id');


        if (!chatId) {
            return;
        }

        // Отправляем whisper только 1 раз
        if (!supportTypingSent) {

            ChatRealtime.sendTyping(chatId, userId);

            supportTypingSent = true;
        }

        clearTimeout(supportTypingTimeout);

        supportTypingTimeout = setTimeout(() => {
            supportTypingSent = false;
        }, 1000);

    });


    /*
    * Получение статусов (онлайн/офлайн)
    * */

    let onlineUsers = [];

    // общий presence канал
    window.Echo.join('online-users')

        // кто уже онлайн
        .here(function (users) {
            onlineUsers = users;
            console.log(onlineUsers)
            renderSidebarStatuses();
        })

        // кто подключился
        .joining(function (user) {
            console.log('JOINING:', user);
            if (!onlineUsers.find(function (u) {
                return u.id === user.id;
            })) {
                onlineUsers.push(user);
            }
            renderSidebarStatuses();
        })

        // кто отключился
        .leaving(function (user) {
            onlineUsers = onlineUsers.filter(function (u) {
                return u.id !== user.id;
            });
            renderSidebarStatuses();
        })

        .error(function (e) {
            console.log(e);
        });

    // Отображение статусов чатов
    function renderSidebarStatuses() {

        const isOnlineHead = onlineUsers.some(function (u) {
            return parseInt(u.id) === $('.user-info').attr('data-user-id');
        });
        if (isOnlineHead) {
            $('.user-info').find('.head_online_indikator').addClass('status-online');
        } else {
            $('.user-info').find('.head_online_indikator').removeClass('status-online')
        }


        $('.chat-item').each(function () {

            const userId = parseInt(
                $(this).data('user-id')
            );

            const isOnline = onlineUsers.some(function (u) {
                return parseInt(u.id) === userId;
            });

            const badge = $(this).find('.status_elem');

            if (isOnline) {
                badge
                    // .removeClass('offline')
                    .addClass('status-online');
            } else {
                badge
                    .removeClass('status-online')
                    // .addClass('offline');
            }
        });
    }


    /*
    * Если было отправлено сообщение
    * */
    Echo.channel('message_sent')
        .listen('.new-message', (e) => {

            if (e.data && e.data.chat_id) {
                const $chatItem = $('#' + e.data.chat_id); // ID уникален, .find() не нужен

                $chatItem.find('.last_message_text').html(e.data.message)

                let $badge = $chatItem.find('.unread-badge');

                if ($badge.length) {
                    if (!$chatItem.hasClass('active')) $badge.show(300).html(e.unread)
                } else {
                    $chatItem.find('.last-message').append('<span class="unread-badge">'+ e.unread +'</span>');
                }

            }

        });


    /*
    * Заполнение шапки при выборе чата
    * */
    function fillWindowHeader(chatId) {
        $.ajax({
            url: "{{ route('support.fill_header') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'chat_id' : chatId
            },
            success: function (response) {
                if (response.length > 0) {
                    $('.chat-window-header').html(response)
                }
            }
        });
    }

    $('.chat-item').each(function () {
        const chatId = $(this).attr('id');
        ChatRealtime.connectToChat(chatId);
    });

    /*
    * Активация чата
    * */
    let currentChannel = null;
    let currentChatId = null;
    function activateChat() {

        $('.chat-item').off('click').on('click', function (e) {

            e.preventDefault();

            const chatId = $(this).attr('id');
            $('#support_send_form').find('input[name="chat_id"]').val(chatId)


            /*
            * Если кто-то печатает
            * */
            ChatRealtime.connectToChat(chatId);

            // ✅ удаляем unread только у текущего чата
            $(this).find('.unread-badge').hide(300);

            $('.chat-item').removeClass('active');
            $(this).addClass('active');

            // ✅ ОТПИСКА ОТ ПРЕДЫДУЩЕГО КАНАЛА
            if (currentChannel) {
                window.Echo.leave(currentChannel);
            }

            currentChannel = 'chat.' + chatId;
            currentChatId = chatId;

            $.ajax({
                url: "{{ route('support.get_messages') }}",
                method: "POST",

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                data: {
                    'chat_id': chatId
                },

                success: function (response) {

                    $('#messagesContainer').empty().append(response);

                    fillWindowHeader(chatId);

                    $('#messagesContainer').scrollTop($('#messagesContainer')[0].scrollHeight);

                    // ✅ НОВАЯ ПОДПИСКА
                    window.Echo.private(currentChannel)
                        .listen('.new-message', (e) => {

                            console.log('EVENT CHAT:', e.data.chat_id);
                            console.log('OPEN CHAT:', currentChatId);

                            // ✅ защита
                            if (parseInt(e.data.chat_id) !== parseInt(currentChatId)) {
                                return;
                            }

                            const senderType = e.data.sender_type;

                            const icon = senderType === 'user'
                                ? '<i class="fas fa-user"></i>'
                                : '<i class="fas fa-headset"></i>';

                            $('#typing-status').hide(300);

                            $('#messagesContainer').append(`
                            <div class="message-bubble ${senderType}">
                                <div class="msg-avatar">
                                    ${icon}
                                </div>

                                <div class="bubble-content">
                                    <div class="msg-text">
                                        ${e.data.message}
                                    </div>

                                    <div class="msg-time">
                                        ${formatTime(e.data.created_at)}
                                    </div>
                                </div>
                            </div>
                        `);

                            $('#messagesContainer')
                                .scrollTop($('#messagesContainer')[0].scrollHeight);

                            ReadMessages(e.data.id, "{{ route('support.read_message') }}", null, chatId);
                        });

                    window.Echo.private('user_read_message.' + chatId)
                        .listen('.read_message', (e) => {
                            e.message_ids.forEach(id => {

                                $(`.message-text[data-message-id="${id}"] .fa-check`)
                                    .css('color', 'limegreen');

                            });
                        });
                }
            });
        });
    } activateChat()

    /*
    * Создание нового чата
    * */
    window.Echo.private('support-dashboard')
        .listen('.new-chat', (e) => {
            if (e.chat) {
                console.log(e)
                // Подготавливаем данные (аналог PHP логики на JS)
                const userName = (e.user.name) ? e.user.name : 'Гость';
                const lastMsg = e.last_message ? e.last_message : 'Нет сообщений';

                // Форматируем дату (простой вариант DD.MM.YYYY)
                const date = new Date(e.chat.created_at);
                const formattedDate = date.toLocaleDateString('ru-RU');

                const unreadMessages = e.unread ? e.unread : 1

                $('.chat-list').append(`
                <div class="chat-item" id="${e.chat.id}">
                    <div class="chat-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="chat-info">
                        <div class="chat-name">
                            <span>${userName} <span class="status-online"></span></span>
                            <span class="time-badge">${formattedDate}</span>
                        </div>
                        <div class="last-message">
                            <span class="last_message_text">${lastMsg}</span>
                            <span class="unread-badge">${unreadMessages}</span>
                        </div>
                    </div>
                </div>
            `);

            activateChat()

            }
        });

</script>


@include('includes.footer')
