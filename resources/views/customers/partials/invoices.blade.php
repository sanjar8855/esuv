<div id="pjax-invoices">
    <h3>Invoyslar tarixi</h3>
    <ul class="list-group">
        @foreach($invoices as $invoice)
            <li class="list-group-item">
                <strong>Invoys #{{ $invoice->invoice_number }}</strong><br>
                <small>Oy: {{ $invoice->billing_period }}</small><br>
                <small>Holat:
                    @if($invoice->status == 'pending')
                        <span class="badge bg-yellow text-yellow-fg">To'liq to‘lanmagan</span>
                    @elseif($invoice->status == 'paid')
                        <span class="badge bg-green text-green-fg">To‘langan</span>
                    @elseif($invoice->status == 'overdue')
                        <span class="badge bg-red text-red-fg">Muddati o‘tgan</span>
                    @endif
                </small><br>
                <small>Summa: {{ number_format($invoice->amount_due, 0, '.', ' ') }} UZS</small>
            </li>
        @endforeach
    </ul>
    <div class="mt-3">
        {{ $invoices->appends(['invoice_page' => request('invoice_page')])->links() }}
    </div>
</div>
