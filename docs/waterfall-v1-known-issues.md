# Waterfall V1 — Known Issues

| # | Issue | Impact | Suggested Fix | Priority |
|---|-------|--------|---------------|----------|
| 1 | No queue/job system | SMS and email notifications are synchronous — slow on high load | Add Laravel Queue with database driver | Medium |
| 2 | Dealer `jar_deposit_qty` column missing | Dealer jar deposit qty calculated from records each time (no cached column) | Add `jar_deposit_qty` column to dealers table via migration | Low |
| 3 | No API routes | No REST API for mobile app integration | Create API routes with Sanctum auth in future version | Low |
| 4 | Bulk SMS BD provider is placeholder | `sendViaBulkSmsBd()` falls back to generic HTTP | Implement proper Bulk SMS BD API | Low |
| 5 | No email verification for registration | Users can register with fake emails | Add email verification step after OTP | Low |
| 6 | Recurring order review workflow | Generated orders go to `pending` status — no dedicated "pending_review" status | Add `pending_review` to order_status enum | Low |
| 7 | No pagination on reports | Reports load all records — may be slow with large data | Add pagination or date-range limits | Medium |
| 8 | No audit log | No record of who changed what | Add activity log package (spatie/laravel-activitylog) | Low |
| 9 | Customer profile edit doesn't sync User.name | Name change in profile doesn't update linked User record | Already fixed in ProfileController.update() | Fixed |
| 10 | SmsOtpSettings had InteractsWithForms | Page crashed with TypeError | Removed InteractsWithForms — fixed | Fixed |
