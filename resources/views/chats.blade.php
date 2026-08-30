@include('includes.meta')

<div class="gradient-bg"></div>

<div class="support-dashboard">
    <!-- ЛЕВАЯ ПАНЕЛЬ: СПИСОК ЧАТОВ (виджет, где появляются чаты от пользователей) -->
    <div class="chats-sidebar">
        <div class="sidebar-header">
            <h1><i class="fas fa-comments"></i> {{ $meta['title'] }}</h1>
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
        <div class="messages-area"></div>

        <!-- индикатор "оператор набирает сообщение" (демонстрация виджета) -->
        <div style="padding: 0 1.8rem 0.5rem 1.8rem; display: none" id="typing-status">
            <div class="typing-status" style="background: rgba(255,59,111,0.1); border-color: rgba(255,59,111,0.3);">
                <i class="fas fa-user"></i>
                <span>{{ $chat->user?->name ?? 'Гость' }} печатает...</span>
                <div class="dots"><span></span><span></span><span></span></div>
            </div>
        </div>

        <!-- поле ввода нового сообщения (для ответа от поддержки) -->
        <div class="message-input-area">
            <i class="fas fa-paperclip attach-icon"></i>
            <div class="input-container">
                <input type="text" placeholder="Написать ответ в этот чат...">
                <div class="send-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
            <i class="fas fa-smile-wink attach-icon" style="font-size: 1.2rem;"></i>
        </div>
    </div>
</div>


<script type="module">

    /*
    * Получение статусов (онлайн/офлайн)
    * */

    let onlineUsers = [];

    // общий presence канал
    window.Echo.join('online-users')

        // кто уже онлайн
        .here(function (users) {
            console.log(users)
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

    function renderSidebarStatuses() {

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
    * Если кто-то печатает
    * */
    let typingTimeouts = {};   // Таймеры для разных чатов
    let typingMessages = {};   // Храним оригинальный текст для каждого чата

    window.Echo.channel('user-typing')
        .listen('.typing', (e) => {
            let chatId = $('#' + e.chat).attr('id');
            if (!chatId) return;

            const $chatElement = $('#' + chatId);
            const $lastMessageText = $chatElement.find('.last_message_text');

            // 🔹 Сохраняем оригинальный текст, если ещё не сохранён для этого чата
            if (!typingMessages[chatId]) {
                typingMessages[chatId] = $lastMessageText.html(); // сохраняем с учётом возможного HTML
            }

            // 🔹 Сбрасываем старый таймер для этого чата
            if (typingTimeouts[chatId]) {
                clearTimeout(typingTimeouts[chatId]);
            }

            // 🔹 Показываем индикатор, если чат активен
            if ($chatElement.hasClass('active')) {
                $('#typing-status').show(300);
            }

            // 🔹 Меняем текст на "Печатает..."
            $lastMessageText.html(`
            <span>Печатает...</span>
            <div class="dots"><span></span><span></span><span></span></div>
        `);

            // 🔹 Ставим новый таймер
            typingTimeouts[chatId] = setTimeout(() => {
                // Восстанавливаем оригинальный текст из хранилища
                if (typingMessages[chatId]) {
                    $lastMessageText.html(typingMessages[chatId]);
                    delete typingMessages[chatId]; // очищаем память
                }

                $('#typing-status').hide(300);
                delete typingTimeouts[chatId];
            }, 2500);
        });

    /*
    * Если было отправлено сообщение
    * */
    Echo.channel('message_sent')
        .listen('.new-message', (e) => {

            if (e.data && e.data.chat_id) {
                const $chatItem = $('#' + e.data.chat_id); // ID уникален, .find() не нужен

                $chatItem.find('.last_message_text').html(e.data.message)
                // $chatItem.find('.last-message').append('<span class="unread-badge">'+ e.unread +'</span>');

                let $badge = $chatItem.find('.unread-badge');

                if ($badge.length) {
                    if (!$chatItem.hasClass('active')) $badge.show(300).html(e.unread)
                } else {
                    $chatItem.find('.last-message').append('<span class="unread-badge">'+ e.unread +'</span>');
                }
            }

        });

    /*
    * Формат времени
    * */
    function formatTime(dateString) {
        const date = new Date(dateString);

        return date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /*
    * Помечаем сообщение прочитанным
    * */
    function ReadMessages(messageId) {
        $.ajax({
            url: "{{ route('support.read_message') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'message_id' : messageId
            },
            success: function (response) {
                if (response.success) {
                    console.log('is_read updated')
                }
            }
        });
    }

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

    /*
    * Активация чата
    * */
    let currentChannel = null;
    let currentChatId = null;
    function activateChat() {

        $('.chat-item').off('click').on('click', function (e) {

            e.preventDefault();

            const chatId = $(this).attr('id');

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

                    $('.messages-area').empty().append(response);

                    fillWindowHeader(chatId);

                    $('.messages-area').scrollTop($('.messages-area')[0].scrollHeight);

                    // ✅ НОВАЯ ПОДПИСКА
                    window.Echo.channel(currentChannel)
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

                            $('.messages-area').append(`
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

                            $('.messages-area')
                                .scrollTop($('.messages-area')[0].scrollHeight);

                            ReadMessages(e.data.id);
                        });
                }
            });
        });
    } activateChat()

    window.Echo.channel('support-dashboard')
        .listen('.new-chat', (e) => {
            if (e.chat) {
                // Подготавливаем данные (аналог PHP логики на JS)
                const userName = (e.chat.user && e.chat.user.name) ? e.chat.user.name : 'Гость';
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

            }
        });

</script>


@include('includes.footer')
