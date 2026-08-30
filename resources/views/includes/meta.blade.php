<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
    <title>Официальный чат-бот и инфо-центр | {{ $meta['title'] }}</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/style.css') }}">
    <meta name="robots" content="noindex, nofollow">

    <script src="{{ asset('public/js/jquery.min.js') }}"></script>

    <script src="{{ asset('public/js/chat-realtime.js') }}"></script>
</head>
<body>
<div id="pwa-install-banner">

    <div class="pwa-content">

        <div class="pwa-icon">
            <i class="fa-solid fa-mobile-screen"></i>
        </div>

        <div class="pwa-text">
            <div class="pwa-title">
                Установите ЧАТ-БОТ ФФАР
            </div>

            <div class="pwa-description" id="pwa-description">
                Быстрый доступ к чат-боту с рабочего стола Вашего устройства.
            </div>
            <button id="pwa-install-btn" class="pwa-btn" style="margin-top: 10px">
                Установить
            </button>
        </div>

        <div class="pwa-actions">
            <button id="pwa-close-btn" class="pwa-close"><i class="fa-solid fa-circle-xmark"></i></button>
        </div>
    </div>
</div>

