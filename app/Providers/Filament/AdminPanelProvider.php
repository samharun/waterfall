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
            ->topNavigation()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): HtmlString => new HtmlString(<<<'HTML'
                <style>
                /* ── Top navigation bar ──────────────────────────────────── */
                .fi-topbar {
                    border-bottom: 1px solid #e5e7eb !important;
                    background: #fff !important;
                    box-shadow: 0 1px 4px rgba(0,0,0,.06) !important;
                }

                /* ── Top nav items ───────────────────────────────────────── */
                .fi-topbar-nav-item-button {
                    font-size: 13px !important;
                    font-weight: 500 !important;
                    color: #374151 !important;
                    padding: 6px 10px !important;
                    border-radius: 8px !important;
                    transition: background .15s, color .15s !important;
                }

                .fi-topbar-nav-item-button:hover {
                    background: #f3f4f6 !important;
                    color: #111827 !important;
                }

                /* Active top nav item */
                .fi-topbar-nav-item-button.fi-active,
                .fi-topbar-nav-item-button[aria-current="page"] {
                    background: #fef3c7 !important;
                    color: #92400e !important;
                    font-weight: 600 !important;
                }

                /* ── Dropdown groups ─────────────────────────────────────── */
                .fi-dropdown-list-item-label {
                    font-size: 13px !important;
                    color: #374151 !important;
                }

                .fi-dropdown-list-item:hover .fi-dropdown-list-item-label {
                    color: #111827 !important;
                }

                /* ── Page heading ────────────────────────────────────────── */
                .fi-page-header-heading {
                    font-size: 20px !important;
                    font-weight: 700 !important;
                    color: #111827 !important;
                }

                /* ── Navigation badge ────────────────────────────────────── */
                .fi-badge {
                    font-size: 10px !important;
                    font-weight: 700 !important;
                }
                </style>
            HTML))
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
                NavigationGroup::make('Customers'),
                NavigationGroup::make('Dealers'),
                NavigationGroup::make('Reports'),
                NavigationGroup::make('Administration'),
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
