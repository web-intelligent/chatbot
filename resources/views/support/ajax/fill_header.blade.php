@if($chat)

    @if(!is_null($chat->user_id))
        <div class="user-info" data-user-id="{{ $chat->user_id }}">
            <div class="chat-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <h4>{{ (!$chat->user->name) ? 'Гость' : $chat->user->name }} <span class="head_online_indikator"></span></h4>
                <p><i class="fas fa-id-badge"></i> ID чата: {{ $chat->id }}</p>
                <p>
                    @if($chat->user->phone)
                        <i class="fa-solid fa-square-phone"></i> {{ $chat->user->phone }}
                    @endif

                    @if($chat->user->email)
                        <i class="fa-solid fa-square-envelope"></i> {{ $chat->user->email }}
                    @endif
                </p>
            </div>
        </div>
{{--        <div class="action-icons">--}}
{{--            <i class="fas fa-phone-alt" title="Звонок"></i>--}}
{{--            <i class="fas fa-ellipsis-v" title="Ещё"></i>--}}
{{--            <i class="fas fa-archive" title="Архивировать чат"></i>--}}
{{--        </div>--}}
    @endif


@endif
