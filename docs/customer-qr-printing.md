# Customer QR Printing

## QR value format

Customer QR codes encode the plain Waterfall customer ID only:

```text
WF-CUS-000123
```

The value comes from `customers.customer_id`. If a legacy customer record is missing that field, the QR display falls back to `WF-CUS-` plus the numeric database ID padded to six digits, for example `WF-CUS-000123`.

No address, phone number, payment data, due amount, or profile data is encoded in the QR code.

## Individual QR printing

In the Filament admin customer table, open a customer action menu and use:

- `View QR` to preview the QR card.
- `Print QR` to open the print-friendly 85mm x 54mm card.
- `Download QR` to download a crisp SVG QR image.

The protected route format is:

```text
/admin/customers/{customer}/qr
/admin/customers/{customer}/qr/print
/admin/customers/{customer}/qr/download
```

## Bulk QR printing

From the Filament admin customer table, select customers and use the `Print QR Cards` bulk action. It opens a print-friendly A4 page containing multiple QR cards.

The protected bulk route also accepts query filters:

```text
/admin/customers/qr/bulk-print?customer_ids[]=1&customer_ids[]=2
/admin/customers/qr/bulk-print?zone_id=1
/admin/customers/qr/bulk-print?approval_status=approved
```

If `customer_ids` are present, they take priority over filter fields. Bulk output is capped at 300 customers per request to keep print pages manageable.

## Flutter scanner alignment

The delivery staff Flutter app supports plain IDs, JSON containing `customer_id`, URLs containing a Waterfall customer ID, and text such as `Customer ID: WF-CUS-000123`.

This first Laravel admin version intentionally encodes only:

```text
WF-CUS-000123
```

That keeps scanning offline-friendly and lets the mobile app match against assigned delivery data securely.

## Security

All QR preview, print, download, and bulk print routes require authenticated back-office access and the `customers.view` permission. No public customer profile page or unauthenticated customer lookup route is created.

Printed QR cards still display basic delivery-use information beside the QR code, such as customer name and mobile number, so printed materials should be handled as operational documents.

## Future improvements

- Future QR format may be a simple URL such as `https://bdwaterfall.com/customer/WF-CUS-000123`.
- Add zone-aware print presets if operations need different sticker sheet sizes.
- Add a dedicated filtered-print action for the current Filament table filters if Filament exposes the active filter state cleanly.
