# Trading Platform Assessment

## Overview
This project is a full-stack trading platform simulation with:

- **Laravel API backend**  
- **Vue.js frontend (Composition API + Tailwind CSS)**  
- Real-time updates via **Pusher / Laravel Echo**  
- Limit order creation, cancellation, and order matching  
- USD and crypto asset management with proper balance locking  
- Commission applied on matched trades (1.5%)  
- Dynamic **Orders page** with filters, orderbook, and wallet overview

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

> **Note:** Using `http` and `sync` queue for local development to bypass SSL/cURL issues. For production, use `https` and a proper queue driver (database or Redis).

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

- **Order matching** is handled by `MatchOrderJob` (full match only, no partial matches)  
- **OrderMatched** event broadcasts to **private channels**: `private-user.{id}`  
- **Commission:** 1.5% of matched USD value is deducted from buyer/seller consistently  
- Locked balances/assets ensure **atomic execution** and race-condition safety

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

## Frontend (Vue.js + Tailwind)

### Features Implemented
- **Orders & Wallet Overview**  
  - USD & crypto balances  
  - All past orders (open, filled, cancelled)  
  - Dynamic orderbook for selected symbol (buy/sell orders)  
- **Limit Order Form**  
  - Inputs: Symbol, Side (Buy/Sell), Price, Amount  
  - Submit button creates order via API  
- **Filters**  
  - Filter orders by symbol, side, and status  
- **Real-Time Updates**  
  - New trades instantly update balances, order list, and orderbook  
  - Listens to `OrderMatched` events via **Laravel Echo + Pusher**

### Frontend Setup
```bash
cd tradingtask
npm install
npm run dev
```

- Open in browser (usually `http://localhost:5173`)  
- Ensure Laravel backend is running (`php artisan serve`)

---

## Notes
- Use `QUEUE_CONNECTION=sync` for **local testing**  
- Private channels ensure **only the buyer and seller** receive order events  
- All balances and asset movements are **atomic and concurrency-safe**  
- Commission is applied **consistently** on matched trades

---

## Bonus / Optional Features
- Trades table to store executed matches (not required, optional for analytics)  
- Toast/alert notifications on order execution  
- Volume preview on Limit Order