$('input[name="phone"]').mask("+7 (999) 999 99 99");


// Helper для форматирования времени
function formatMessageTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString); // JS сам переведёт UTC → локальное
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}


 // Формат времени
function formatTime(dateString) {
    const date = new Date(dateString);

    return date.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Прокрутка вниз до последнего сообщения
function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Вспомогательная функция: экранирование HTML
function escapeHtml(str) {
    // return str.replace(/[&<>]/g, function(m) {
    //     if(m === '&') return '&amp;';
    //     if(m === '<') return '&lt;';
    //     if(m === '>') return '&gt;';
    //     return m;
    // }).replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, function(c) {
    //     return c;
    // });

    return str;
}


// При загрузке страницы форматируй все времена:
$(document).ready(function () {
    $('.message-time').each(function () {
        const isoString = $(this).attr('data-time');
        if (isoString) {
            $(this).html(formatMessageTime(isoString));
        }
    });
});

// Добавление сообщения в контейнер
function addMessage(response, messagesContainer = null, recipient = 'user') {
    console.log(response)
    // контейнер по умолчанию
    const $container = messagesContainer
        ? $(messagesContainer)
        : $('#messagesContainer');

    const senderType = response.message.sender_type;
    const check = (recipient === 'user' && senderType === 'user') ? '<i style="font-size:8px" class="fa-solid fa-check"></i>' : ''
    const checkSupport = (recipient === 'support' && senderType === 'support') ? '<i style="font-size:8px" class="fa-solid fa-check"></i>' : ''


    const avatarIcon =
        senderType === 'user'
            ? '<i class="fas fa-user"></i>'
            : senderType === 'bot'
                ? '<i class="fas fa-robot"></i>'
                : '<i class="fas fa-headset"></i>';

    const time = formatMessageTime(response.message.created_at);

    const messageHtml = `
        <div class="message ${senderType}">
            <div class="avatar">${avatarIcon}</div>
            <div class="bubble">
                <div class="message-text" data-message-id="${response.message.id}">
                    ${escapeHtml(response.message.message)}
                    ${check}${checkSupport}
                </div>
                <div class="message-time">${time}</div>
            </div>
        </div>
    `;

    // добавляем через jQuery
    $container.append(messageHtml);

    scrollToBottom();

    // возвращаем последний добавленный элемент
    return $container.find('.message').last();
}

/*
    * Помечаем сообщение прочитанным
    * */
function ReadMessages(messageId, route, messagesIDS = null, chatId) {

    const ids = Array.isArray(messagesIDS)
        ? messagesIDS
        : messageId;

    $.ajax({
        url: route,
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            'message_id' : ids,
            'chat_id' : chatId
        },
        success: function (response) {
            if (response.success) {
                console.log('is_read updated')
            }
        }
    });
}
