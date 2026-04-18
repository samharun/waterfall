# Waterfall Version 1 — QA Checklist

## Admin Panel Access
- [ ] `/admin` loads for `admin@waterfall.com` (role: admin)
- [ ] `/admin` returns 403 for customer, dealer, delivery_staff
- [ ] Filament login page shows at `/admin/login`
- [ ] Back-office middleware blocks non-back-office users

## Role Menu Visibility
- [ ] super_admin/admin sees all navigation groups
- [ ] delivery_manager sees: Dashboard, Customers (view), Zones, Orders, Deliveries, Delivery Report
- [ ] billing_officer sees: Dashboard, Customers/Dealers (view), Invoices, Payments, Reports
- [ ] stock_manager sees: Dashboard, Products, Stock Transactions, Jar Deposits, Stock Report
- [ ] Settings pages hidden from delivery_manager/billing_officer/stock_manager

## Customer Registration & OTP
- [ ] `/customer/register` loads
- [ ] Invalid BD mobile (e.g. 01234567890) is rejected
- [ ] Existing approved mobile shows "already registered" message
- [ ] Existing pending mobile shows "pending approval" message
- [ ] Valid registration sends OTP (check `storage/logs/laravel.log` in local)
- [ ] OTP is hashed in `customer_otps` table (not plain text)
- [ ] OTP NOT logged in production (only in local/testing env)
- [ ] Wrong OTP increments attempts counter
- [ ] After 5 wrong attempts, OTP is locked
- [ ] OTP expires after configured minutes (default 5)
- [ ] Resend OTP works with 60s cooldown
- [ ] Max 3 resends enforced
- [ ] Correct OTP creates User + Customer with `approval_status = pending`
- [ ] Pending customer cannot login (shows pending message)
- [ ] Admin approves customer in Filament
- [ ] Approved customer can login at `/customer/login`

## Customer Web Panel
- [ ] Dashboard shows: current_due, jar_deposit_qty, pending orders, subscription summary
- [ ] Order create: product list loads with customer-specific price
- [ ] Order create: fallback to default price when no custom price
- [ ] Order submitted: Order + OrderItem created correctly
- [ ] Order list: shows only own orders
- [ ] Order detail: shows correct items and totals
- [ ] Cannot view another customer's order (403)
- [ ] Invoice list: own invoices only
- [ ] Invoice print: own invoice only (403 for others)
- [ ] Payment list: own payments only
- [ ] Payment receipt print: own payment only
- [ ] Jar deposit list: own records only
- [ ] Subscription: create/edit/pause/resume/cancel works
- [ ] Profile edit: name, email, address, zone, slot updates correctly

## Dealer Web Panel
- [ ] `/dealer/login` works with mobile + password
- [ ] Pending/rejected dealer cannot login
- [ ] Dashboard shows: current_due, pending orders, month payments
- [ ] Products page shows dealer-specific price (or default)
- [ ] Order create: dealer price applies
- [ ] Order list/detail: own orders only
- [ ] Invoice/payment: own records only, print own only
- [ ] Dealer registration OTP flow works

## Delivery Staff Panel
- [ ] `/delivery/login` works (email + password)
- [ ] Non-delivery_staff role rejected
- [ ] Today's deliveries: only assigned to logged-in staff
- [ ] Cannot view another staff's delivery (403)
- [ ] Mark in-progress: delivery_status → in_progress, order_status → assigned
- [ ] Mark delivered: delivery_status → delivered, delivered_at set, order_status → delivered
- [ ] Mark failed: failure_reason required, order_status → assigned
- [ ] Bulk mark delivered: all selected become delivered
- [ ] Collect payment: creates Payment with collection_source = delivery_staff
- [ ] Collect payment: invoice due updates if invoice selected
- [ ] Collect payment: customer/dealer current_due updates
- [ ] Overpayment against invoice is blocked
- [ ] Delivered with payment: payment + delivery status in one action
- [ ] Cannot collect payment for another staff's delivery

## Order & Delivery (Admin)
- [ ] Create customer order with custom pricing
- [ ] Create dealer order with dealer pricing
- [ ] Order totals calculate correctly (subtotal - discount + delivery_charge)
- [ ] Confirm order action works
- [ ] Cancel order action works
- [ ] Assign delivery from OrderResource
- [ ] Duplicate active delivery prevented
- [ ] Delivery status changes update order_status correctly

## Invoice & Payment (Admin)
- [ ] Create customer invoice
- [ ] Create dealer invoice
- [ ] total_amount = subtotal + previous_due
- [ ] Mark issued: status → issued, customer_due updates
- [ ] Record payment: paid_amount updates, due_amount updates
- [ ] Invoice status: issued → partial → paid correctly
- [ ] Cancel invoice: customer_due recalculates
- [ ] Delete/restore payment: invoice and due recalculate
- [ ] Print invoice: admin, customer, dealer all work
- [ ] Print receipt: admin, customer, dealer all work
- [ ] Unauthorized print blocked (403)

## Stock & Jar Deposit
- [ ] stock_in increases current_stock
- [ ] stock_out decreases current_stock
- [ ] damaged decreases current_stock
- [ ] returned increases current_stock
- [ ] adjustment increase/decrease works
- [ ] Edit/delete/restore recalculates current_stock
- [ ] Low stock filter shows products at/below alert qty
- [ ] Customer jar deposit: deposit_received increases jar_deposit_qty
- [ ] Customer jar deposit: jar_returned decreases jar_deposit_qty
- [ ] Edit/delete/restore recalculates jar_deposit_qty

## Subscriptions & Recurring Orders
- [ ] Create subscription with daily/weekly/custom_days/monthly frequency
- [ ] Next delivery date calculates correctly
- [ ] Pause subscription: status → paused
- [ ] Resume subscription: status → active, next date recalculates
- [ ] Cancel subscription: status → cancelled
- [ ] `php artisan waterfall:generate-recurring-orders --dry-run` shows preview
- [ ] `php artisan waterfall:generate-recurring-orders` creates pending orders
- [ ] Duplicate order for same subscription + date is prevented
- [ ] next_delivery_date advances after order generation

## Settings & Print
- [ ] Company Settings: save name, address, mobile, email
- [ ] Company Settings: logo upload works (requires `php artisan storage:link`)
- [ ] Company Settings: colors and footer notes save
- [ ] SMS/OTP Settings: save provider, OTP length/expiry/attempts
- [ ] SMS/OTP Settings: encrypted secrets not overwritten when blank
- [ ] SMS/OTP Settings: test SMS logs to `storage/logs/laravel.log`
- [ ] Invoice print layout: company header, invoice details, payment history
- [ ] Receipt print layout: company header, payment details, amount highlighted

## Dashboard & Reports
- [ ] Dashboard widgets load without errors (empty DB safe)
- [ ] Sales Report: filters work, totals correct
- [ ] Delivery Report: filters work, status counts correct
- [ ] Due Report: customer/dealer dues correct
- [ ] Stock Report: low stock highlighted
- [ ] Customer Ledger: debit/credit/balance correct

## Security
- [ ] CSRF tokens in all POST forms
- [ ] OTP not exposed in production logs
- [ ] SMS API secrets not logged
- [ ] Admin print routes require back-office role
- [ ] Customer/dealer/delivery_staff cannot access /admin
- [ ] Ownership checks prevent cross-customer/dealer data access
- [ ] Delivery staff cannot update another staff's delivery

## Production Readiness Notes
- Run `php artisan storage:link` for logo uploads
- Set `APP_ENV=production` and `APP_DEBUG=false`
- Set `SMS_DRIVER=ssl_wireless` or `twilio` with real credentials
- Set `MAIL_MAILER` to real SMTP for approval notifications
- Run `php artisan optimize` before deployment
- Do NOT run `WaterfallDemoSeeder` in production
- Change all demo passwords before going live
