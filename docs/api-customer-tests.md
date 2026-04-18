# Waterfall Customer API — Test Reference

Base URL: `http://localhost:8000/api/customer`

---

## 1. Register — invalid mobile
```bash
curl -X POST http://localhost:8000/api/customer/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","mobile":"0123456789","address":"Dhaka","zone_id":1}'
# Expected: 422 Validation failed
```

## 2. Register — valid mobile
```bash
curl -X POST http://localhost:8000/api/customer/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Rahim Uddin","mobile":"01712345678","address":"Mirpur, Dhaka","zone_id":1}'
# Expected: 201 Registration successful. OTP verification required.
```

## 3. Login — unregistered mobile
```bash
curl -X POST http://localhost:8000/api/customer/login \
  -H "Content-Type: application/json" \
  -d '{"mobile":"01999999999"}'
# Expected: 404 No account found
```

## 4. Login — registered mobile
```bash
curl -X POST http://localhost:8000/api/customer/login \
  -H "Content-Type: application/json" \
  -d '{"mobile":"01712345678"}'
# Expected: 200 OTP sent
```

## 5. Verify OTP — invalid OTP
```bash
curl -X POST http://localhost:8000/api/customer/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"mobile":"01712345678","otp":"000000","type":"login"}'
# Expected: 400 Invalid OTP
```

## 6. Verify OTP — valid OTP, pending customer
```bash
curl -X POST http://localhost:8000/api/customer/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"mobile":"01712345678","otp":"123456","type":"registration"}'
# Expected: 200 OTP verified. Account waiting for admin approval.
```

## 7. Verify OTP — valid OTP, approved customer (returns token)
```bash
# First approve the customer in admin panel, then:
curl -X POST http://localhost:8000/api/customer/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"mobile":"01712345678","otp":"123456","type":"login"}'
# Expected: 200 Login successful + token
```

## 8. Profile — no token (401)
```bash
curl -X GET http://localhost:8000/api/customer/profile
# Expected: 401 Unauthenticated
```

## 9. Profile — with token
```bash
curl -X GET http://localhost:8000/api/customer/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
# Expected: 200 customer profile data
```

## 10. Place order — active customer
```bash
curl -X POST http://localhost:8000/api/customer/orders \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2,"delivery_slot":"morning"}'
# Expected: 201 Order placed
```

## 11. Place order — pending customer (403)
```bash
# Use token from a pending customer account
curl -X POST http://localhost:8000/api/customer/orders \
  -H "Authorization: Bearer PENDING_CUSTOMER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":1,"delivery_slot":"now"}'
# Expected: 403 Account not active
```

## 12. List own orders
```bash
curl -X GET http://localhost:8000/api/customer/orders \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
# Expected: 200 paginated order list (only this customer's orders)
```

## 13. View another customer's order (404)
```bash
# Use an order ID that belongs to a different customer
curl -X GET http://localhost:8000/api/customer/orders/999 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
# Expected: 404 Order not found
```

## 14. Dashboard
```bash
curl -X GET http://localhost:8000/api/customer/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 15. Products
```bash
curl -X GET http://localhost:8000/api/customer/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 16. Bills
```bash
curl -X GET http://localhost:8000/api/customer/bills \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 17. Due Balance
```bash
curl -X GET http://localhost:8000/api/customer/due-balance \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 18. Deposits
```bash
curl -X GET http://localhost:8000/api/customer/deposits \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 19. Logout
```bash
curl -X POST http://localhost:8000/api/customer/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```
