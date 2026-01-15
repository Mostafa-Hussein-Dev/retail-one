@props(['amount'])

@php
    $rate = \App\Models\Setting::get('exchange_rate_usd_lbp', 89500);
    $lbp = $amount * $rate;
@endphp

<span class="currency-toggle">
    <span class="usd">${{ number_format($amount, 2) }}</span>
    <span class="lbp" style="display:none">LL {{ number_format($lbp, 0) }}</span>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCurrency(this)">
        <i class="fas fa-exchange-alt"></i>
    </button>
</span>

@once
@push('scripts')
<script>
function toggleCurrency(btn) {
    const container = btn.closest('.currency-toggle');
    const usd = container.querySelector('.usd');
    const lbp = container.querySelector('.lbp');

    if (usd.style.display === 'none') {
        usd.style.display = 'inline';
        lbp.style.display = 'none';
    } else {
        usd.style.display = 'none';
        lbp.style.display = 'inline';
    }
}
</script>
@endpush
@endonce
