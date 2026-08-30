<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <style>
        #response-message {
            position: fixed;
            top: 120px;
            right: 25px;
            color: white;
            border-radius: 7px;
            padding: 10px 25px;
            background: #333;
            display: none;
            z-index: 100;
        }
    </style>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #9aa9bf;">
<div id="response-message"></div>
<div id="user" style="width: 40px; height: 40px; border-radius: 100%; background: #ff8a5c; cursor: pointer; display: flex; justify-content: center; align-items: center">
    🙂
</div>

<script type="module">
    // Настройка CSRF для fetch (если axios не работает)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showMessage(buttonId, message) {
        const msg = document.getElementById('response-message');
        msg.style.display = 'block';
        msg.innerHTML = message + '<br>От: ' + buttonId;
        setTimeout(() => msg.style.display = 'none', 3000);
    }

    // Подписка на канал
    window.Echo.channel('reaction')
        .listen('ReactedEvent', (e) => {
            console.log('Получено:', e);
            showMessage(e.buttonId, e.message);
        });

    // Отправка через fetch вместо axios (проще)
    document.getElementById('user').addEventListener('click', function () {
        fetch('/reaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                buttonId: 'user',
                message: 'Тестовое сообщение!'
            })
        });
    });
</script>
</body>
</html>