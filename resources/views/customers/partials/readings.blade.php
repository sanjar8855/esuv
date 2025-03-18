<div id="pjax-readings">
    <ul class="list-group">
        @foreach($readings as $reading)
            <li class="list-group-item">
                <small>Sana: {{ $reading->reading_date }}</small><br>
                <small>Ko'rsatgich: {{ number_format($reading->reading, 0, '.', ' ') }}</small><br>
                <small>Holat: {!! $reading->confirmed
                        ? '<span class="badge bg-green text-green-fg">Tasdiqlangan</span>'
                        : '<span class="badge bg-red text-red-fg">Tasdiqlanmagan</span>'
                    !!}</small><br>
                @if($reading->photo)
                    <a href="{{ asset('storage/' . $reading->photo) }}" target="_blank">
                        <img src="{{ asset('storage/' . $reading->photo) }}" alt="Ko'rsatkich rasmi"
                             width="50">
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
    <div class="mt-3">
        {{ $readings->appends(['reading_page' => request('reading_page')])->links() }}
    </div>
</div>
