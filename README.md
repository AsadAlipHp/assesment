# Trading Platform Assessment

## Overview
This project is a full-stack trading platform simulation with:

- Laravel API backend
- Vue.js frontend (Composition API + Tailwind, coming soon)
- Real-time updates via Pusher
- Limit order creation, cancellation, and order matching
- USD and crypto asset management with proper balance locking
- Commission applied on matched trades

---

## Backend Setup (Laravel)

### Requirements
- PHP 8.x
- Composer
- MySQL
- WAMP / MAMP / Local server

### Installation
```bash
git clone https://github.com/AsadAlipHp/assesment.git
cd assesment
composer install
cp .env.example .env
php artisan key:generate
```

### Database Configuration
In your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trading_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:
```bash
php artisan migrate
```

### Pusher Configuration (Local Development)
In your `.env`:
```env
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=sync

PUSHER_APP_ID=2108051
PUSHER_APP_KEY=e0187631da2c6885a14a
PUSHER_APP_SECRET=a0728c12402ebf21ff58
PUSHER_APP_CLUSTER=ap2
PUSHER_SCHEME=http
PUSHER_PORT=80
```

> **Note:** Using `http` and `sync` queue for local development to bypass SSL/cURL issues.  For production, use `https` and proper queue driver (database or redis).

---

## API Endpoints

| Method | Endpoint | Description |
|--------|---------|-------------|
| POST   | `/api/register` | Register a new user |
| POST   | `/api/login`    | Login user |
| GET    | `/api/profile`  | Get authenticated user's USD balance and assets |
| GET    | `/api/orders?symbol=BTC` | Get all open orders for a symbol |
| POST   | `/api/orders`   | Create a limit order |
| POST   | `/api/orders/{id}/cancel` | Cancel an open order |

---

## Order Matching & Real-Time

- `MatchOrderJob` handles matching logic (full match only, no partial matches)
- `OrderMatched` event broadcasts to **private channels**: `private-user.{id}`
- Commission = **1.5%** of matched USD value
- Locked balances/assets ensure atomic execution

---

### Testing Events (Tinker)

```bash
php artisan tinker
```

```php
$buy = App\Models\Order::where('side', 'buy')->first();
$sell = App\Models\Order::where('side', 'sell')->first();

event(new App\Events\OrderMatched($buy, $sell, 0.01, 95000, 14.25));
```

- Open Pusher debug console → Event should appear with payload:

```json
{
  "buy_order_id": 1,
  "sell_order_id": 2,
  "amount": 0.01,
  "price": 95000,
  "fee": 14.25
}
```

---

### Notes
- Use `QUEUE_CONNECTION=sync` for **local testing**
- Switch to proper queue driver (`database`/`redis`) and HTTPS in **production**
- Private channels ensure only the buyer and seller receive order events

---

## Frontend (Vue.js)

- **Coming next:**  
  - Limit Order Form (Buy/Sell)
  - Orders & Wallet Overview
  - Real-time updates via Pusher/Laravel Echo

---

## License
This project is for assessment purposes only.

