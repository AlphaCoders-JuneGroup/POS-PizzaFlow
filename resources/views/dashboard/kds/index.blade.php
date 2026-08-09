@extends('layouts.dashboard')

@section('title', 'Kitchen Display System')
@section('page_title', 'Kitchen KDS')

@push('styles')
<style>
.pf-kds-board {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    align-items: start;
}
.pf-kds-col {
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid var(--dash-border);
    border-radius: var(--dash-radius);
    min-height: 18rem;
    overflow: hidden;
}
.pf-kds-col-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.9rem 1.1rem;
    border-bottom: 1px solid var(--dash-border);
    font-weight: 700;
    letter-spacing: 0.02em;
}
.pf-kds-col-head .count {
    background: rgba(31, 41, 55, 0.08);
    border-radius: 999px;
    padding: 0.15rem 0.65rem;
    font-size: 0.85rem;
}
.pf-kds-col.is-new .pf-kds-col-head { background: rgba(230, 57, 70, 0.1); color: #c1121f; }
.pf-kds-col.is-prep .pf-kds-col-head { background: rgba(244, 162, 97, 0.18); color: #b86a2b; }
.pf-kds-col.is-bake .pf-kds-col-head { background: rgba(255, 209, 102, 0.35); color: #9a6b00; }
.pf-kds-tickets {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 0.85rem;
}
.pf-kds-ticket {
    background: #fff;
    border: 1px solid var(--dash-border);
    border-radius: 0.95rem;
    box-shadow: 0 8px 18px rgba(31, 41, 55, 0.06);
    padding: 0.95rem 1rem;
    border-left: 5px solid var(--dash-primary);
    animation: pfKdsIn 0.35s ease;
}
.pf-kds-ticket.is-prep { border-left-color: var(--dash-secondary); }
.pf-kds-ticket.is-bake { border-left-color: #e9a825; }
.pf-kds-ticket-top {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    align-items: flex-start;
    margin-bottom: 0.55rem;
}
.pf-kds-ticket-top strong { font-size: 1.15rem; }
.pf-kds-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 0.7rem;
}
.pf-kds-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 0.5rem;
    padding: 0.2rem 0.55rem;
    font-size: 0.78rem;
    font-weight: 600;
    background: rgba(31, 41, 55, 0.06);
    color: var(--dash-text);
}
.pf-kds-chip.tone-pickup { background: rgba(34, 197, 94, 0.12); color: #15803d; }
.pf-kds-chip.tone-delivery { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
.pf-kds-chip.tone-age { background: rgba(230, 57, 70, 0.1); color: #c1121f; }
.pf-kds-item {
    border-top: 1px dashed rgba(31, 41, 55, 0.12);
    padding: 0.65rem 0 0.2rem;
}
.pf-kds-item:first-of-type { border-top: 0; padding-top: 0; }
.pf-kds-item.is-done { opacity: 0.55; }
.pf-kds-item-head {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    align-items: center;
}
.pf-kds-item-name {
    font-weight: 700;
    font-size: 1.02rem;
}
.pf-kds-mods {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.4rem;
}
.pf-kds-mod {
    background: linear-gradient(135deg, rgba(230, 57, 70, 0.12), rgba(255, 209, 102, 0.28));
    color: #9a1b24;
    border: 1px solid rgba(230, 57, 70, 0.22);
    border-radius: 0.45rem;
    padding: 0.18rem 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}
.pf-kds-mod.is-topping {
    background: rgba(244, 162, 97, 0.2);
    border-color: rgba(244, 162, 97, 0.45);
    color: #9a4e12;
}
.pf-kds-item-actions,
.pf-kds-ticket-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.55rem;
}
.pf-kds-empty {
    color: var(--dash-muted);
    text-align: center;
    padding: 2rem 1rem;
    font-size: 0.92rem;
}
.pf-kds-ready {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.pf-kds-ready-pill {
    background: #fff;
    border: 1px solid var(--dash-border);
    border-radius: 999px;
    padding: 0.35rem 0.8rem;
    font-size: 0.85rem;
    font-weight: 600;
}
@keyframes pfKdsIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 1199.98px) {
    .pf-kds-board { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Kitchen Display System</h2>
        <p class="pf-dash-sub mb-0">
            Live tickets with custom pizza modifications highlighted. Mark items Baking or Completed.
        </p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-broadcast me-1"></i>Auto-refresh · Queue <strong id="kdsQueueCount">{{ $counts['queue'] }}</strong>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif
@if (session('success'))
    <div class="alert alert-success pf-alert">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-inbox"></i></div>
            <div><span>NEW</span><strong id="kdsReceivedCount">{{ $counts['received'] }}</strong></div>
        </div>
    </div>
    <div class="col-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-egg-fried"></i></div>
            <div><span>PREPARING</span><strong id="kdsPreparingCount">{{ $counts['preparing'] }}</strong></div>
        </div>
    </div>
    <div class="col-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi bi-fire"></i></div>
            <div><span>BAKING</span><strong id="kdsBakingCount">{{ $counts['baking'] }}</strong></div>
        </div>
    </div>
</div>

<div class="pf-kds-board mb-4">
    @foreach ([
        'received' => ['label' => 'New / Received', 'class' => 'is-new', 'ticket' => ''],
        'preparing' => ['label' => 'Preparing', 'class' => 'is-prep', 'ticket' => 'is-prep'],
        'baking' => ['label' => 'Baking', 'class' => 'is-bake', 'ticket' => 'is-bake'],
    ] as $key => $meta)
        <section class="pf-kds-col {{ $meta['class'] }}">
            <div class="pf-kds-col-head">
                <span>{{ $meta['label'] }}</span>
                <span class="count">{{ $columns[$key]->count() }}</span>
            </div>
            <div class="pf-kds-tickets">
                @forelse ($columns[$key] as $order)
                    @include('dashboard.kds._ticket', ['order' => $order, 'ticketClass' => $meta['ticket']])
                @empty
                    <div class="pf-kds-empty">No tickets</div>
                @endforelse
            </div>
        </section>
    @endforeach
</div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>Recently completed</h3>
        <span class="text-muted small">Ready for pickup / dispatch</span>
    </div>
    <div class="p-3 p-md-4">
        @if ($recentReady->isEmpty())
            <div class="text-muted">No recently completed tickets.</div>
        @else
            <div class="pf-kds-ready">
                @foreach ($recentReady as $ready)
                    <span class="pf-kds-ready-pill">
                        <i class="bi bi-check2-circle text-success me-1"></i>
                        {{ $ready->order_number }}
                        <span class="text-muted fw-normal ms-1">· {{ $ready->fulfillmentLabel() }}</span>
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pollUrl = @json($pollUrl);
    let stamp = null;

    async function poll() {
        try {
            const res = await fetch(pollUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            set('kdsQueueCount', data.queue);
            set('kdsReceivedCount', data.received);
            set('kdsPreparingCount', data.preparing);
            set('kdsBakingCount', data.baking);
            if (stamp !== null && data.stamp !== stamp) {
                window.location.reload();
                return;
            }
            stamp = data.stamp;
        } catch (e) {}
    }

    poll();
    setInterval(poll, 6000);
});
</script>
@endpush
