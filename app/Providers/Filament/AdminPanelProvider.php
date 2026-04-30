<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\DeliveryCompletionChartWidget;
use App\Filament\Admin\Widgets\DeliveryStaffActivityWidget;
use App\Filament\Admin\Widgets\JarDepositTrackerWidget;
use App\Filament\Admin\Widgets\PendingApprovalsWidget;
use App\Filament\Admin\Widgets\RecentActivityWidget;
use App\Filament\Admin\Widgets\TodayCollectionSummaryWidget;
use App\Filament\Admin\Widgets\TodayDeliveryStatusWidget;
use App\Filament\Admin\Widgets\UnassignedDeliveriesWidget;
use App\Filament\Admin\Widgets\ZoneDeliverySummaryWidget;use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use App\Filament\Admin\Pages\Auth\Login;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->default()
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(PanelsRenderHook::SCRIPTS_BEFORE, fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                    (() => {
                        const installPersistFallback = () => {
                            if (! window.Alpine || typeof window.Alpine.$persist === 'function') {
                                return;
                            }

                            const persist = (value) => ({
                                as() {
                                    return value;
                                },
                            });

                            window.Alpine.$persist = persist;

                            if (typeof window.Alpine.magic === 'function') {
                                window.Alpine.magic('persist', () => persist);
                            }

                            if (typeof window.Alpine.directive === 'function') {
                                window.Alpine.directive('persist', () => {});
                            }
                        };

                        if (window.Alpine) {
                            installPersistFallback();
                        }

                        document.addEventListener('alpine:init', installPersistFallback, { once: true });
                    })();
                </script>
            HTML))
            ->databaseNotifications(fn (): bool => Schema::hasTable('notifications'))
            ->databaseNotificationsPolling('5s')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->navigationGroups([
                NavigationGroup::make('Order & Delivery'),
                NavigationGroup::make('Customer Management'),
                NavigationGroup::make('Dealer / Distributor'),
                NavigationGroup::make('Billing & Payment'),
                NavigationGroup::make('Inventory'),
                NavigationGroup::make('Reports'),
                NavigationGroup::make('Settings'),
            ])
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                TodayDeliveryStatusWidget::class,
                DeliveryStaffActivityWidget::class,
                ZoneDeliverySummaryWidget::class,
                PendingApprovalsWidget::class,
                TodayCollectionSummaryWidget::class,
                UnassignedDeliveriesWidget::class,
                JarDepositTrackerWidget::class,
                RecentActivityWidget::class,
                DeliveryCompletionChartWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureBackOfficeAccess::class,
            ]);
    }
}
