<?php

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\JarDepositController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\RegisterController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\Admin\CustomerQrController;
use App\Http\Controllers\Admin\DeliveryStaffLocationController;
use App\Http\Controllers\Admin\InvoicePrintController;
use App\Http\Controllers\Admin\PaymentReceiptPrintController;
use App\Http\Controllers\Dealer\AuthController as DealerAuthController;
use App\Http\Controllers\Dealer\DashboardController as DealerDashboardController;
use App\Http\Controllers\Dealer\InvoiceController as DealerInvoiceController;
use App\Http\Controllers\Dealer\JarDepositController as DealerJarDepositController;
use App\Http\Controllers\Dealer\OrderController as DealerOrderController;
use App\Http\Controllers\Dealer\PaymentController as DealerPaymentController;
use App\Http\Controllers\Dealer\ProductController as DealerProductController;
use App\Http\Controllers\Dealer\ProfileController as DealerProfileController;
use App\Http\Controllers\Dealer\RegisterController as DealerRegisterController;
use App\Http\Controllers\Delivery\AuthController as DeliveryAuthController;
use App\Http\Controllers\Delivery\DashboardController as DeliveryDashboardController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Delivery\PaymentCollectionController;
use Illuminate\Support\Facades\Route;

//Route::get('/', fn () => redirect()->route('customer.login'));

Route::get('/', function () { return view('landing');})->name('landing');

// ── Locale toggle ──────────────────────────────────────────────────
Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['bn', 'en'])) {
        session(['locale' => $lang]);
    }
    return back();
})->name('locale.switch');

// ── Admin Print Routes ─────────────────────────────────────────────
Route::middleware(['auth', 'back.office'])->group(function () {
    Route::get('/admin/invoices/{invoice}/print', [InvoicePrintController::class, 'print'])
        ->name('admin.invoices.print');
    Route::get('/admin/payments/{payment}/print', [PaymentReceiptPrintController::class, 'print'])
        ->name('admin.payments.print');
    Route::get('/admin/delivery-staff-locations', DeliveryStaffLocationController::class)
        ->name('admin.delivery-staff-locations.index');

    Route::prefix('/admin/customers')->name('admin.customers.')->group(function () {
        Route::get('/qr/bulk-print', [CustomerQrController::class, 'bulkPrint'])
            ->name('qr.bulk-print');
        Route::get('/{customer}/qr', [CustomerQrController::class, 'show'])
            ->name('qr.show');
        Route::get('/{customer}/qr/download', [CustomerQrController::class, 'download'])
            ->name('qr.download');
        Route::get('/{customer}/qr/print', [CustomerQrController::class, 'print'])
            ->name('qr.print');
    });
});

// ── Customer Auth (guest) ──────────────────────────────────────────
Route::prefix('customer')->name('customer.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

        // Registration & OTP
        Route::get('/register',              [RegisterController::class, 'showRegister'])->name('register');
        Route::post('/register',             [RegisterController::class, 'register'])->name('register.submit')->middleware('throttle:10,1');
        Route::get('/verify-otp',            [RegisterController::class, 'showVerifyOtp'])->name('otp.verify');
        Route::post('/verify-otp',           [RegisterController::class, 'verifyOtp'])->name('otp.verify.submit')->middleware('throttle:5,1');
        Route::post('/resend-otp',           [RegisterController::class, 'resendOtp'])->name('otp.resend')->middleware('throttle:3,1');
        Route::get('/registration-pending',  [RegisterController::class, 'registrationPending'])->name('registration.pending');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Customer Protected Routes ──────────────────────────────────
    Route::middleware(['auth', 'customer.access'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile',   [ProfileController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile',   [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/orders',         [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create',  [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders',        [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('/invoices',           [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', function (\App\Models\Invoice $invoice) {
            abort_if($invoice->customer_id !== auth()->user()->customer?->id, 403);
            $invoice->load(['customer.zone', 'payments']);
            $settings = app(\App\Services\SettingsService::class);
            return view('prints.invoice', ['invoice' => $invoice, 'company' => $settings->company(), 'branding' => $settings->branding(), 'billing' => $settings->billing(), 'logoUrl' => $settings->logoUrl(), 'context' => 'customer']);
        })->name('invoices.print');

        Route::get('/payments',    [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}/print', function (\App\Models\Payment $payment) {
            abort_if($payment->customer_id !== auth()->user()->customer?->id, 403);
            $payment->load(['customer.zone', 'invoice', 'receivedBy']);
            $settings = app(\App\Services\SettingsService::class);
            return view('prints.payment-receipt', ['payment' => $payment, 'company' => $settings->company(), 'branding' => $settings->branding(), 'billing' => $settings->billing(), 'logoUrl' => $settings->logoUrl(), 'context' => 'customer']);
        })->name('payments.print');
        Route::get('/jar-deposits',[JarDepositController::class, 'index'])->name('jar-deposits.index');

        // Subscription
        Route::get('/subscription',        [SubscriptionController::class, 'show'])->name('subscription.show');
        Route::get('/subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
        Route::post('/subscription',       [SubscriptionController::class, 'store'])->name('subscription.store');
        Route::get('/subscription/edit',   [SubscriptionController::class, 'edit'])->name('subscription.edit');
        Route::put('/subscription',        [SubscriptionController::class, 'update'])->name('subscription.update');
        Route::post('/subscription/pause', [SubscriptionController::class, 'pause'])->name('subscription.pause');
        Route::post('/subscription/resume',[SubscriptionController::class, 'resume'])->name('subscription.resume');
        Route::post('/subscription/cancel',[SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    });
});

// ── Delivery Staff Panel ───────────────────────────────────────────
Route::prefix('delivery')->name('delivery.')->group(function () {

    // Guest-only routes (no auth)
    Route::get('/login',  [DeliveryAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [DeliveryAuthController::class, 'login'])->name('login.submit');

    Route::post('/logout', [DeliveryAuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(['auth', 'delivery.access'])->group(function () {

        Route::get('/dashboard', [DeliveryDashboardController::class, 'index'])->name('dashboard');
        Route::get('/today',     [DeliveryController::class, 'today'])->name('today');

        Route::get('/deliveries',                                [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}',                     [DeliveryController::class, 'show'])->name('deliveries.show');
        Route::post('/deliveries/{delivery}/mark-in-progress',   [DeliveryController::class, 'markInProgress'])->name('deliveries.mark-in-progress');
        Route::post('/deliveries/{delivery}/mark-delivered',     [DeliveryController::class, 'markDelivered'])->name('deliveries.mark-delivered');
        Route::post('/deliveries/{delivery}/mark-failed',        [DeliveryController::class, 'markFailed'])->name('deliveries.mark-failed');
        Route::post('/deliveries/bulk-mark-delivered',           [DeliveryController::class, 'bulkMarkDelivered'])->name('deliveries.bulk-mark-delivered');

        // Payment collection
        Route::get('/deliveries/{delivery}/collect-payment',              [PaymentCollectionController::class, 'create'])->name('deliveries.collect-payment');
        Route::post('/deliveries/{delivery}/collect-payment',             [PaymentCollectionController::class, 'store'])->name('deliveries.collect-payment.store');
        Route::post('/deliveries/{delivery}/mark-delivered-with-payment', [PaymentCollectionController::class, 'markDeliveredWithPayment'])->name('deliveries.mark-delivered-with-payment');
    });
});


// ── Dealer Panel ───────────────────────────────────────────────────
Route::prefix('dealer')->name('dealer.')->group(function () {

    Route::get('/login',  [DealerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [DealerAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout',[DealerAuthController::class, 'logout'])->name('logout');

    // Dealer self-registration
    Route::get('/register',             [DealerRegisterController::class, 'showRegister'])->name('register');
    Route::post('/register',            [DealerRegisterController::class, 'register'])->name('register.submit')->middleware('throttle:10,1');
    Route::get('/verify-otp',           [DealerRegisterController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp',          [DealerRegisterController::class, 'verifyOtp'])->name('otp.verify.submit')->middleware('throttle:5,1');
    Route::post('/resend-otp',          [DealerRegisterController::class, 'resendOtp'])->name('otp.resend')->middleware('throttle:3,1');
    Route::get('/registration-pending', [DealerRegisterController::class, 'registrationPending'])->name('registration.pending');

    Route::middleware(['auth', 'dealer.access'])->group(function () {

        Route::get('/dashboard', [DealerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile',   [DealerProfileController::class, 'show'])->name('profile');
        Route::get('/products',  [DealerProductController::class, 'index'])->name('products.index');

        Route::get('/orders',         [DealerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create',  [DealerOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders',        [DealerOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [DealerOrderController::class, 'show'])->name('orders.show');

        Route::get('/invoices',           [DealerInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [DealerInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', function (\App\Models\Invoice $invoice) {
            abort_if($invoice->dealer_id !== auth()->user()->dealer?->id, 403);
            $invoice->load(['dealer.zone', 'payments']);
            $settings = app(\App\Services\SettingsService::class);
            return view('prints.invoice', ['invoice' => $invoice, 'company' => $settings->company(), 'branding' => $settings->branding(), 'billing' => $settings->billing(), 'logoUrl' => $settings->logoUrl(), 'context' => 'dealer']);
        })->name('invoices.print');

        Route::get('/payments',    [DealerPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}/print', function (\App\Models\Payment $payment) {
            abort_if($payment->dealer_id !== auth()->user()->dealer?->id, 403);
            $payment->load(['dealer.zone', 'invoice', 'receivedBy']);
            $settings = app(\App\Services\SettingsService::class);
            return view('prints.payment-receipt', ['payment' => $payment, 'company' => $settings->company(), 'branding' => $settings->branding(), 'billing' => $settings->billing(), 'logoUrl' => $settings->logoUrl(), 'context' => 'dealer']);
        })->name('payments.print');
        Route::get('/jar-deposits',[DealerJarDepositController::class, 'index'])->name('jar-deposits.index');
    });
});
