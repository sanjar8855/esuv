<div id="pjax-payments">
    <h3>To‘lovlar Tarixi</h3>
    <ul class="list-group">
        @foreach($payments as $payment)
            <li class="list-group-item">
                <strong>To‘lov: {{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong><br>
                <small>Usul: {{ ucfirst($payment->payment_method) }}</small><br>
                <small>Sana: {{ $payment->payment_date }}</small><br>
                <small>Status: {{ $payment->status }}</small>
            </li>
        @endforeach
    </ul>
    <div class="mt-3">
        {{ $payments->appends(['payment_page' => request('payment_page')])->links() }}
    </div>
</div>
