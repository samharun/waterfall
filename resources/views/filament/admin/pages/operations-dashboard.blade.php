<x-filament-panels::page>
    <style>{!! file_get_contents(resource_path('css/operations-dashboard.css')) !!}</style>
    @php
        $d = $this->getDashboardData();
        $can = fn ($permission) => auth()->user()?->can($permission);
        $dayLabel = $d['date']->isToday() ? 'Today’s' : $d['date']->format('d M').' ·';
        $groups = [
            'ORDER & DELIVERY' => [['Orders Overview','shopping-cart','orders-overview','orders.view'],['Deliveries Overview','truck','deliveries-overview','deliveries.view'],['Delivery Staff Map','map','delivery-staff-map','deliveries.view']],
            'BUSINESS' => [['Customers','users','customers','customers.view'],['Dealers','building-storefront','dealers','dealers.view']],
            'FINANCE' => [['Accounts','wallet','accounts-dashboard','accounts.dashboard.view'],['Collections','banknotes','due-collections','due_collections.manage'],['Reports','chart-bar','accounts-report','accounts.reports.view']],
        ];
    @endphp
    <div class="wf-ops" x-data="{ menuOpen: false }" wire:poll.60s="refreshDashboard">
        <button x-show="menuOpen" x-cloak class="wf-menu-shade" x-on:click="menuOpen = false" aria-label="Close navigation"></button>
        <aside class="wf-rail" :class="{ 'is-open': menuOpen }" aria-label="Main navigation">
            <a class="wf-brand" href="{{ url('/admin') }}">
                <svg width="36" height="48" viewBox="0 0 36 48" aria-hidden="true"><defs><linearGradient id="wf-drop" x2="1" y2="1"><stop stop-color="#00b9ff"/><stop offset="1" stop-color="#0067ef"/></linearGradient></defs><path d="M18 2C14 10 3 23 3 31a15 15 0 0030 0C33 23 22 10 18 2Z" fill="url(#wf-drop)"/><path d="M8 32c1 9 12 12 19 5" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                <span><strong>WaterFall</strong><small>MANAGEMENT SYSTEM</small></span>
            </a>
            <nav>
                <a class="wf-nav-link active" href="{{ url('/admin') }}" aria-current="page"><x-filament::icon icon="heroicon-o-home"/>Operations Dashboard</a>
                @foreach($groups as $label => $links)
                    @if(collect($links)->contains(fn ($link) => $can($link[3])))
                    <div class="wf-nav-group"><h2>{{ $label }}</h2>
                        @foreach($links as [$text,$icon,$path,$permission])
                            @if($can($permission))<a class="wf-nav-link" href="{{ url('/admin/'.$path) }}"><x-filament::icon :icon="'heroicon-o-'.$icon"/>{{ $text }}</a>@endif
                        @endforeach
                    </div>
                    @endif
                @endforeach
                <details class="wf-more"><summary>More administration <x-filament::icon icon="heroicon-o-chevron-down"/></summary>
                    @foreach(filament()->getNavigation() as $group)
                        @foreach($group->getItems() as $item)
                            <a class="wf-nav-link" href="{{ $item->getUrl() }}">{{ $item->getLabel() }}</a>
                        @endforeach
                    @endforeach
                </details>
            </nav>
            <div class="wf-rail-footer">
                @if($can('settings.company.manage'))<a class="wf-nav-link" href="{{ url('/admin/company-settings') }}"><x-filament::icon icon="heroicon-o-cog-6-tooth"/>Settings</a>@endif
                <div class="wf-profile"><span class="wf-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ \App\Models\User::ROLES[auth()->user()->role] ?? 'Team member' }}</small></span><x-filament::icon icon="heroicon-o-chevron-right"/></div>
            </div>
        </aside>
        <div class="wf-topbar-content">
            <button class="wf-menu-button" x-on:click="menuOpen = ! menuOpen" :aria-expanded="menuOpen" aria-label="Open navigation"><x-filament::icon icon="heroicon-o-bars-3"/></button>
            <div class="wf-crumb"><a href="{{ url('/admin') }}">Overview</a><span>/</span>Operations</div>
            <label class="wf-search"><x-filament::icon icon="heroicon-o-magnifying-glass"/><input wire:model.live.debounce.300ms="search" maxlength="100" type="search" placeholder="Search orders, customers…" aria-label="Search recent orders by number or customer"/></label>
        </div>
        <header class="wf-heading">
            <div><h1>Operations Dashboard</h1><p>Your daily orders, deliveries and collections at a glance.</p></div>
            <div class="wf-header-actions"><label class="wf-date"><x-filament::icon icon="heroicon-o-calendar-days"/><input type="date" wire:model.live="selectedDate" aria-label="Dashboard date"/></label><button class="wf-button" wire:click="refreshDashboard" wire:loading.attr="disabled"><x-filament::icon icon="heroicon-o-arrow-path"/>Refresh</button>@if($can('orders.create'))<a class="wf-button primary" href="{{ url('/admin/orders/create') }}"><x-filament::icon icon="heroicon-o-plus"/>New order</a>@endif</div>
        </header>
        @error('selectedDate')<p role="alert">{{ $message }}</p>@enderror
        <section class="wf-metrics" aria-label="Business snapshot">
            @foreach([
                [$d['date']->isToday() ? 'Collected today' : 'Collected on '.$d['date']->format('d M'),'৳ '.number_format($d['collections']['today'],2),'Accepted payments','wallet'],
                ['Collected this month','৳ '.number_format($d['collections']['month'],2),$d['date']->format('F Y').' · through '.$d['date']->format('d M'),'calendar-days'],
                ['Total collected','৳ '.number_format($d['collections']['all'],2),'All time · through selected date','banknotes'],
                [$dayLabel.' orders',number_format($d['orders']),$d['awaiting'].' awaiting confirmation','shopping-cart'],
            ] as [$label,$value,$note,$icon])
                <article class="wf-metric"><div class="wf-metric-label"><x-filament::icon :icon="'heroicon-o-'.$icon"/>{{ $label }}<span class="wf-metric-decor"><x-filament::icon :icon="'heroicon-o-'.$icon"/></span></div><strong>{{ $value }}</strong><p>{{ $note }}</p></article>
            @endforeach
        </section>
        <section class="wf-business wf-card" aria-label="Customer and dealer counts">
            @foreach([[$d['customers'],'Active customers','users','green'],[$d['dealers'],'Approved dealers','building-storefront','blue'],[$d['approvals'],'Pending approvals','user-plus','amber']] as [$count,$label,$icon,$color])
                <div class="wf-business-item"><span class="wf-icon-circle {{ $color }}"><x-filament::icon :icon="'heroicon-o-'.$icon"/></span><div><strong>{{ number_format($count) }}</strong><p>{{ $label }}</p></div>@if($loop->last && $can('customers.view'))<a class="wf-review" href="{{ url('/admin/customers').'?'.http_build_query(['filters'=>['approval_status'=>['value'=>'pending']]]) }}">Review <span>→</span></a>@endif</div>
            @endforeach
        </section>
        <div class="wf-grid">
            <section class="wf-card wf-delivery"><div class="wf-card-heading"><h2><x-filament::icon icon="heroicon-o-truck"/>{{ $dayLabel }} delivery status</h2>@if($can('deliveries.view'))<a href="{{ url('/admin/deliveries-overview') }}">View deliveries <span>→</span></a>@endif</div>
                <div class="wf-progress-title"><p><strong>{{ $d['delivered'] }} of {{ $d['total'] }}</strong> completed</p><b>{{ $d['percent'] }}%</b></div>
                <div class="wf-progress-track" role="progressbar" aria-label="Delivery completion" aria-valuenow="{{ $d['percent'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $d['percent'] }}%"></span></div>
                <div class="wf-statuses">@foreach([['Pending',$d['pending'],'amber'],['In progress',$d['inProgress'],'blue'],['Delivered',$d['delivered'],'green'],['Failed',$d['failed'],'red']] as [$label,$count,$color])<div><span class="wf-dot {{ $color }}"></span><strong>{{ $count }}</strong><p>{{ $label }}</p></div>@endforeach</div>
                @if($d['partial'])<p class="wf-partial">{{ $d['partial'] }} partially delivered · included in total, awaiting completion</p>@endif
                <div class="wf-chart-heading"><span>Deliveries over the last {{ $chartDays }} days</span><select wire:model.live="chartDays" aria-label="Chart period"><option value="7">Last 7 days</option><option value="14">Last 14 days</option></select></div>
                <div class="wf-chart" aria-label="Completed deliveries by completion date"><div class="wf-axis">@foreach([4,3,2,1,0] as $tick)<span>{{ $d['chartMax']*$tick/4 }}</span>@endforeach</div><div class="wf-bars">@foreach($d['chart'] as $bar)<div class="wf-bar-column" title="{{ $bar['date'] }}: {{ $bar['count'] }} delivered"><div class="wf-bar" style="height:{{ $bar['count']/$d['chartMax']*100 }}%"><strong>{{ $bar['count'] }}</strong></div><span>{{ $chartDays === 7 ? $bar['label'] : $bar['date'] }}</span></div>@endforeach</div></div>
            </section>
            <section class="wf-card wf-attention"><div class="wf-card-heading"><h2><x-filament::icon icon="heroicon-o-exclamation-triangle"/>Needs attention</h2><span class="wf-count">{{ (int)($d['awaiting']>0)+(int)($d['unassigned']>0)+(int)($d['approvals']>0) }}</span></div>
                @foreach([
                    [$d['awaiting'].' orders to confirm','New orders waiting for confirmation','document-text','blue','Review orders','orders-overview','orders.view'],
                    [$d['unassigned'].' deliveries unassigned','Orders ready for delivery','truck','green','Assign staff','deliveries-overview','deliveries.assign'],
                    [$d['approvals'].' customer approvals','New customer registrations','user','violet','Review','customers?'.http_build_query(['filters'=>['approval_status'=>['value'=>'pending']]]),'customers.view'],
                ] as [$title,$note,$icon,$color,$action,$path,$permission])<div class="wf-attention-row"><span class="wf-icon-circle {{ $color }}"><x-filament::icon :icon="'heroicon-o-'.$icon"/></span><div><h3>{{ $title }}</h3><p>{{ $note }}</p></div>@if($can($permission))<a class="wf-button small" href="{{ url('/admin/'.$path) }}">{{ $action }}</a>@endif</div>@endforeach
            </section>
            <section class="wf-card wf-recent"><div class="wf-card-heading"><h2><x-filament::icon icon="heroicon-o-shopping-cart"/>Recent orders</h2>@if($can('orders.view'))<a href="{{ url('/admin/orders-overview') }}">View all orders <span>→</span></a>@endif</div>
                <div class="wf-table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Zone</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @forelse($d['recent'] as $order)<tr><td>{{ $order->order_no }}</td><td>{{ $order->customer?->name ?? $order->dealer?->name ?? '—' }}</td><td>{{ $order->zone?->name ?? '—' }}</td><td class="wf-nowrap">৳ {{ number_format($order->total_amount,2) }}</td><td><span class="wf-badge {{ match($order->order_status){'pending'=>'amber','confirmed','delivered'=>'green','assigned'=>'blue',default=>'gray'} }}"><i></i>{{ ucfirst($order->order_status) }}</span></td><td>@if($can('orders.update'))<a class="wf-table-action" href="{{ url('/admin/orders/'.$order->id.'/edit') }}">View</a>@else<span>—</span>@endif</td></tr>
                @empty<tr><td colspan="6" class="wf-empty">{{ $search ? 'No orders match your search.' : 'No orders yet. New orders will appear here.' }}</td></tr>@endforelse
                </tbody></table></div>
            </section>
            <section class="wf-card wf-staff"><div class="wf-card-heading"><h2><x-filament::icon icon="heroicon-o-users"/>Staff activity</h2>@if($can('deliveries.view'))<a href="{{ url('/admin/delivery-staff-map') }}">View staff map <span>→</span></a>@endif</div>
                @forelse($d['staff'] as $staff)@php($percent = (int)round($staff['completed']/$staff['total']*100))<div class="wf-staff-row"><span class="wf-icon-circle {{ ['blue','green','violet'][$loop->index%3] }}">{{ $staff['initials'] }}</span><div class="wf-staff-name"><h3>{{ $staff['name'] }}</h3><p>{{ $staff['completed'] }} / {{ $staff['total'] }} delivered</p></div><div class="wf-staff-progress"><span>{{ $percent }}%</span><div class="wf-progress-track"><span style="width:{{ $percent }}%"></span></div></div></div>@empty<div class="wf-empty-staff"><span class="wf-icon-circle blue"><x-filament::icon icon="heroicon-o-users"/></span><h3>No staff activity yet</h3><p>Assigned deliveries will appear here.</p></div>@endforelse
            </section>
        </div>
        <footer class="wf-live-note"><span class="wf-dot green"></span>Live data · Updated {{ $lastUpdatedAt }}<span wire:loading>Refreshing…</span></footer>
    </div>
</x-filament-panels::page>
