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
                /* ═══════════════════════════════════════════════════════════
                   WATERFALL ADMIN — GLOBAL UI POLISH
                   Primary: #0077B6  |  Accent: #f59e0b  |  BG: #f8fafc
                ═══════════════════════════════════════════════════════════ */

                /* ── Base & body ─────────────────────────────────────────── */
                body { background: #f8fafc !important; }

                /* ── Top navigation bar ──────────────────────────────────── */
                .fi-topbar {
                    background: #0077B6 !important;
                    border-bottom: none !important;
                    box-shadow: 0 2px 8px rgba(0,119,182,.25) !important;
                    height: 56px !important;
                }
                .fi-topbar-nav { gap: 2px !important; }

                /* Brand / logo area */
                .fi-logo { color: #fff !important; font-weight: 800 !important; font-size: 16px !important; }
                .fi-logo svg, .fi-logo img { filter: brightness(0) invert(1) !important; }

                /* Top nav group buttons */
                .fi-topbar-nav-item-button {
                    font-size: 13px !important;
                    font-weight: 500 !important;
                    color: rgba(255,255,255,.85) !important;
                    padding: 6px 12px !important;
                    border-radius: 8px !important;
                    transition: background .15s, color .15s !important;
                    white-space: nowrap !important;
                }
                .fi-topbar-nav-item-button:hover {
                    background: rgba(255,255,255,.15) !important;
                    color: #fff !important;
                }
                .fi-topbar-nav-item-button.fi-active,
                .fi-topbar-nav-item-button[aria-current="page"] {
                    background: rgba(255,255,255,.2) !important;
                    color: #fff !important;
                    font-weight: 700 !important;
                }
                /* Chevron icon in nav buttons */
                .fi-topbar-nav-item-button svg { color: rgba(255,255,255,.7) !important; }

                /* Top-right icons (notifications, user menu) */
                .fi-topbar-item-button { color: rgba(255,255,255,.85) !important; }
                .fi-topbar-item-button:hover { color: #fff !important; background: rgba(255,255,255,.15) !important; border-radius: 8px !important; }

                /* ── Dropdown menus ──────────────────────────────────────── */
                .fi-dropdown-panel {
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
                    overflow: hidden !important;
                    min-width: 200px !important;
                }
                .fi-dropdown-list { padding: 6px !important; }
                .fi-dropdown-list-item {
                    border-radius: 8px !important;
                    margin: 1px 0 !important;
                }
                .fi-dropdown-list-item-button {
                    padding: 8px 12px !important;
                    border-radius: 8px !important;
                    font-size: 13px !important;
                    color: #374151 !important;
                    transition: background .12s !important;
                }
                .fi-dropdown-list-item-button:hover {
                    background: #f0f9ff !important;
                    color: #0077B6 !important;
                }
                .fi-dropdown-list-item-label { font-size: 13px !important; font-weight: 500 !important; }
                .fi-dropdown-list-item-icon { color: #9ca3af !important; }
                .fi-dropdown-list-item-button:hover .fi-dropdown-list-item-icon { color: #0077B6 !important; }

                /* ── Page layout ─────────────────────────────────────────── */
                .fi-main { padding: 20px 24px !important; }
                .fi-page-header { margin-bottom: 20px !important; }
                .fi-page-header-heading {
                    font-size: 22px !important;
                    font-weight: 800 !important;
                    color: #0f172a !important;
                    letter-spacing: -.02em !important;
                }
                .fi-page-header-subheading { font-size: 13px !important; color: #64748b !important; margin-top: 2px !important; }

                /* ── Filament sections ───────────────────────────────────── */
                .fi-section {
                    background: #fff !important;
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    box-shadow: 0 1px 3px rgba(0,0,0,.05) !important;
                    overflow: hidden !important;
                }
                .fi-section-header {
                    padding: 14px 20px !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                    background: #f8fafc !important;
                }
                .fi-section-header-heading {
                    font-size: 14px !important;
                    font-weight: 700 !important;
                    color: #0f172a !important;
                }
                .fi-section-content { padding: 20px !important; }
                .fi-section-content-ctn { padding: 20px !important; }

                /* ── Tables ──────────────────────────────────────────────── */
                .fi-ta-table { border-collapse: collapse !important; }
                .fi-ta-header-cell {
                    background: #f8fafc !important;
                    font-size: 11px !important;
                    font-weight: 700 !important;
                    color: #64748b !important;
                    text-transform: uppercase !important;
                    letter-spacing: .06em !important;
                    padding: 10px 16px !important;
                    border-bottom: 1px solid #e5e7eb !important;
                    white-space: nowrap !important;
                }
                .fi-ta-row {
                    border-bottom: 1px solid #f1f5f9 !important;
                    transition: background .1s !important;
                }
                .fi-ta-row:hover { background: #f0f9ff !important; }
                .fi-ta-cell {
                    padding: 10px 16px !important;
                    font-size: 13px !important;
                    color: #374151 !important;
                    vertical-align: middle !important;
                }
                /* Table wrapper */
                .fi-ta-wrp {
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    overflow: hidden !important;
                    box-shadow: 0 1px 3px rgba(0,0,0,.05) !important;
                    background: #fff !important;
                }
                /* Table header bar */
                .fi-ta-header {
                    padding: 14px 16px !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                    background: #fff !important;
                }
                /* Table footer / pagination */
                .fi-ta-footer {
                    padding: 12px 16px !important;
                    border-top: 1px solid #f1f5f9 !important;
                    background: #f8fafc !important;
                }

                /* ── Badges ──────────────────────────────────────────────── */
                .fi-badge {
                    font-size: 11px !important;
                    font-weight: 600 !important;
                    padding: 2px 8px !important;
                    border-radius: 9999px !important;
                    letter-spacing: .01em !important;
                }

                /* ── Buttons ─────────────────────────────────────────────── */
                .fi-btn {
                    border-radius: 8px !important;
                    font-size: 13px !important;
                    font-weight: 600 !important;
                    padding: 8px 14px !important;
                    transition: all .15s !important;
                }
                .fi-btn-color-primary {
                    background: #0077B6 !important;
                    border-color: #0077B6 !important;
                    color: #fff !important;
                }
                .fi-btn-color-primary:hover {
                    background: #005f92 !important;
                    border-color: #005f92 !important;
                }
                .fi-btn-color-danger { border-radius: 8px !important; }
                .fi-btn-color-success { border-radius: 8px !important; }
                .fi-btn-color-warning { border-radius: 8px !important; }
                .fi-btn-color-info { border-radius: 8px !important; }
                .fi-btn-color-gray { border-radius: 8px !important; }

                /* ── Form inputs ─────────────────────────────────────────── */
                .fi-input {
                    border-radius: 8px !important;
                    border-color: #d1d5db !important;
                    font-size: 13px !important;
                    background: #f9fafb !important;
                    transition: border-color .15s, box-shadow .15s !important;
                }
                .fi-input:focus {
                    border-color: #0077B6 !important;
                    box-shadow: 0 0 0 3px rgba(0,119,182,.12) !important;
                    background: #fff !important;
                }
                .fi-select-input {
                    border-radius: 8px !important;
                    font-size: 13px !important;
                    background: #f9fafb !important;
                }
                .fi-fo-field-wrp-label {
                    font-size: 12px !important;
                    font-weight: 600 !important;
                    color: #374151 !important;
                }
                .fi-fo-field-wrp-helper-text { font-size: 11px !important; color: #9ca3af !important; }

                /* ── Modals ──────────────────────────────────────────────── */
                .fi-modal-window {
                    border-radius: 16px !important;
                    box-shadow: 0 20px 60px rgba(0,0,0,.18) !important;
                    overflow: hidden !important;
                }
                .fi-modal-header {
                    padding: 18px 24px !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                    background: #f8fafc !important;
                }
                .fi-modal-heading {
                    font-size: 16px !important;
                    font-weight: 700 !important;
                    color: #0f172a !important;
                }
                .fi-modal-content { padding: 20px 24px !important; }
                .fi-modal-footer {
                    padding: 14px 24px !important;
                    border-top: 1px solid #f1f5f9 !important;
                    background: #f8fafc !important;
                    display: flex !important;
                    gap: 8px !important;
                    justify-content: flex-end !important;
                }

                /* ── Stats overview widget ───────────────────────────────── */
                .fi-wi-stats-overview-stat {
                    border-radius: 12px !important;
                    border: 1px solid #e5e7eb !important;
                    background: #fff !important;
                    box-shadow: 0 1px 3px rgba(0,0,0,.05) !important;
                    padding: 16px !important;
                }
                .fi-wi-stats-overview-stat-value {
                    font-size: 26px !important;
                    font-weight: 800 !important;
                    color: #0f172a !important;
                }
                .fi-wi-stats-overview-stat-label {
                    font-size: 12px !important;
                    font-weight: 600 !important;
                    color: #64748b !important;
                    text-transform: uppercase !important;
                    letter-spacing: .05em !important;
                }

                /* ── Notifications ───────────────────────────────────────── */
                .fi-no-notification {
                    border-radius: 12px !important;
                    box-shadow: 0 4px 16px rgba(0,0,0,.12) !important;
                    border: 1px solid #e5e7eb !important;
                }

                /* ── Pagination ──────────────────────────────────────────── */
                .fi-pagination-item-btn {
                    border-radius: 6px !important;
                    font-size: 13px !important;
                    font-weight: 500 !important;
                }
                .fi-pagination-item-btn[aria-current="page"] {
                    background: #0077B6 !important;
                    color: #fff !important;
                    border-color: #0077B6 !important;
                }

                /* ── Action group button ─────────────────────────────────── */
                .fi-ac-action-group-trigger-btn {
                    border-radius: 8px !important;
                    font-size: 12px !important;
                }

                /* ── Tabs ────────────────────────────────────────────────── */
                .fi-tabs-tab {
                    font-size: 13px !important;
                    font-weight: 500 !important;
                    border-radius: 8px 8px 0 0 !important;
                }
                .fi-tabs-tab[aria-selected="true"] {
                    color: #0077B6 !important;
                    border-bottom-color: #0077B6 !important;
                    font-weight: 700 !important;
                }

                /* ── Filters ─────────────────────────────────────────────── */
                .fi-ta-filters-form {
                    background: #fff !important;
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    padding: 16px !important;
                    box-shadow: 0 2px 8px rgba(0,0,0,.06) !important;
                }

                /* ── Dashboard widget grid ───────────────────────────────── */
                .fi-wi-widget { border-radius: 12px !important; }

                /* ── Breadcrumbs ─────────────────────────────────────────── */
                .fi-breadcrumbs-item-label { font-size: 12px !important; color: #64748b !important; }
                .fi-breadcrumbs-item-label:hover { color: #0077B6 !important; }

                /* ── Empty state ─────────────────────────────────────────── */
                .fi-ta-empty-state-heading { font-size: 15px !important; font-weight: 700 !important; color: #374151 !important; }
                .fi-ta-empty-state-description { font-size: 13px !important; color: #9ca3af !important; }

                /* ── Search input ────────────────────────────────────────── */
                .fi-ta-search-field input {
                    border-radius: 8px !important;
                    font-size: 13px !important;
                    border-color: #d1d5db !important;
                    background: #f9fafb !important;
                }
                .fi-ta-search-field input:focus {
                    border-color: #0077B6 !important;
                    box-shadow: 0 0 0 3px rgba(0,119,182,.1) !important;
                }

                /* ── Scrollbar ───────────────────────────────────────────── */
                ::-webkit-scrollbar { width: 6px; height: 6px; }
                ::-webkit-scrollbar-track { background: #f1f5f9; }
                ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
                ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
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
