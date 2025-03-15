@if($reading->confirmed)
    <span class="badge bg-green text-green-fg">Tasdiqlangan</span>
@else
    <span class="badge bg-red text-red-fg">Tasdiqlanmagan</span>
    <form action="{{ route('meter_readings.confirm', $reading->id) }}" method="POST"
          class="d-inline confirm-form" data-reading-id="{{ $reading->id }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-primary confirm-btn">
            Tasdiqlash
        </button>
    </form>
@endif
