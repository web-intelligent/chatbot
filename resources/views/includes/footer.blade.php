<script src="{{ asset('public/js/popper.min.js') }}"></script>
<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('public/js/jquery.maskedinput.js') }}"></script>
<script src="{{ asset('public/js/common.js') }}"></script>

<script>
    $(function () {

        const hideUntil = localStorage.getItem('pwa_hide_until');

        if (
            hideUntil &&
            parseInt(hideUntil) > Date.now()
        ) {
            return;
        }

        const isStandalone =
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true;

        if (isStandalone) {
            return;
        }

        const isIOS =
            /iphone|ipad|ipod/i.test(navigator.userAgent);

        let deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', function (e) {

            e.preventDefault();

            deferredPrompt = e;

            $('#pwa-install-banner').fadeIn();

        });

        if (isIOS) {

            $('#pwa-install-banner').fadeIn();

            $('#pwa-install-btn').text('Как установить');

            $('#pwa-description').html(
                'Нажмите <b>Поделиться</b> → <b>На экран Домой</b> → <b>Добавить</b>'
            );
        }

        $('#pwa-install-btn').on('click', async function () {

            if (isIOS) {

                alert(
                    'Для установки приложения:\n\n' +
                    '1. Нажмите кнопку "Поделиться"\n' +
                    '2. Выберите "На экран Домой"\n' +
                    '3. Нажмите "Добавить"'
                );

                return;
            }

            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();

            const result =
                await deferredPrompt.userChoice;

            if (result.outcome === 'accepted') {

                $('#pwa-install-banner').fadeOut();

            }

            deferredPrompt = null;
        });

        $('#pwa-close-btn').on('click', function () {

            localStorage.setItem(
                'pwa_hide_until',
                Date.now() + (7 * 24 * 60 * 60 * 1000)
            );

            $('#pwa-install-banner').fadeOut();
        });

    });
</script>

</body>
</html>
