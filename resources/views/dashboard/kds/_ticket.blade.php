@php
    $ageMinutes = $order->placed_at ? max(0, (int) $order->placed_at->diffInMinutes(now())) : 0;
    $kitchenItems = $order->kitchenItems();
    $orderKey = (string) $order->_id;
@endphp
<article class="pf-kds-ticket {{ $ticketClass ?? '' }}">
    <div class="pf-kds-ticket-top">
        <div>
            <strong>{{ $order->order_number }}</strong>
            <div class="text-muted small">{{ $order->customer_name ?: 'Customer' }}</div>
        </div>
        <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
    </div>

    <div class="pf-kds-meta">
        <span class="pf-kds-chip {{ $order->fulfillmentType() === 'pickup' ? 'tone-pickup' : 'tone-delivery' }}">
            <i class="bi {{ $order->fulfillmentType() === 'pickup' ? 'bi-bag' : 'bi-truck' }}"></i>
            {{ $order->fulfillmentLabel() }}
        </span>
        <span class="pf-kds-chip tone-age">
            <i class="bi bi-clock"></i> {{ $ageMinutes }}m
        </span>
        @if ($order->notes || $order->delivery_instructions)
            <span class="pf-kds-chip" title="{{ $order->instructionsText() }}">
                <i class="bi bi-chat-left-text"></i> Notes
            </span>
        @endif
    </div>

    @if ($order->notes || $order->delivery_instructions)
        <div class="small text-muted mb-2">{{ $order->instructionsText() }}</div>
    @endif

    @foreach ($kitchenItems as $line)
        <div class="pf-kds-item {{ $line['kds_status'] === 'completed' ? 'is-done' : '' }}">
            <div class="pf-kds-item-head">
                <div class="pf-kds-item-name">{{ $line['qty'] }}× {{ $line['base_name'] }}</div>
                @if ($line['kds_status'] === 'baking')
                    <span class="badge text-bg-warning">Baking</span>
                @elseif ($line['kds_status'] === 'completed')
                    <span class="badge text-bg-success">Done</span>
                @endif
            </div>

            @if ($line['has_mods'])
                <div class="pf-kds-mods">
                    @foreach ($line['mods'] as $mod)
                        <span class="pf-kds-mod {{ str_starts_with($mod, '+ ') ? 'is-topping' : '' }}">{{ $mod }}</span>
                    @endforeach
                </div>
            @endif

            @if ($line['kds_status'] !== 'completed')
                <div class="pf-kds-item-actions">
                    @if ($line['kds_status'] !== 'baking')
                        <form method="POST" action="{{ url('/dashboard/kds/'.$orderKey.'/item') }}">
                            @csrf
                            <input type="hidden" name="item" value="{{ $line['index'] }}">
                            <input type="hidden" name="status" value="baking">
                            <button type="submit" class="btn btn-sm btn-pf-outline">Baking</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ url('/dashboard/kds/'.$orderKey.'/item') }}">
                        @csrf
                        <input type="hidden" name="item" value="{{ $line['index'] }}">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-sm btn-pf-primary">Completed</button>
                    </form>
                </div>
            @endif
        </div>
    @endforeach

    <div class="pf-kds-ticket-actions">
        @if ($order->normalizedStatus() === 'received')
            <form method="POST" action="{{ url('/dashboard/kds/'.$orderKey.'/start') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-pf-outline">
                    <i class="bi bi-play-fill me-1"></i>Start prep
                </button>
            </form>
        @endif
        @if (in_array($order->normalizedStatus(), ['received', 'preparing'], true))
            <form method="POST" action="{{ url('/dashboard/kds/'.$orderKey.'/baking') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-pf-outline">
                    <i class="bi bi-fire me-1"></i>All Baking
                </button>
            </form>
        @endif
        <form method="POST" action="{{ url('/dashboard/kds/'.$orderKey.'/complete') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-pf-primary">
                <i class="bi bi-check2-circle me-1"></i>Complete ticket
            </button>
        </form>
    </div>
</article>
