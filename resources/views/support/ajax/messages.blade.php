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
                @if($message->sender_type == 'support')
                    @if($message->is_read == 0)
                        <i style="font-size: 8px" class="fa-solid fa-check"></i>
                    @else
                        <i style="font-size: 8px; color: limegreen" class="fa-solid fa-check"></i>
                    @endif
                @endif
            </div>
            <div class="message-time">{{ date('H:i', strtotime($message->created_at)) }}</div>
        </div>
    </div>
@endforeach
