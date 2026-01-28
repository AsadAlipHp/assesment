<template>
  <div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">TradingTask</div>
      <nav>
        <ul>
          <li><router-link to="/dashboard" class="active">Dashboard</router-link></li>
          <li><router-link to="/orders">Orders</router-link></li>
          <li><button @click="logout">Logout</button></li>
        </ul>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="main">

      <!-- Top Navbar -->
      <header class="topbar">
        <h1>Dashboard</h1>
        <div class="user-info">Hello, <strong>{{ userName }}</strong></div>
      </header>

      <!-- Scrollable content -->
      <div class="content">

        <!-- Limit Order Form -->
        <section class="order-form">
          <h2>Place Limit Order</h2>

          <div v-if="message.text" :class="['msg', message.type]">
            {{ message.text }}
          </div>

          <form @submit.prevent="placeOrder">

            <div class="form-group">
              <label>Symbol</label>
              <select v-model="orderForm.symbol">
                <option value="BTC">BTC</option>
                <option value="ETH">ETH</option>
              </select>
            </div>

            <div class="form-group">
              <label>Side</label>
              <select v-model="orderForm.side">
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
              </select>
            </div>

            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.01" v-model="orderForm.price" required />
            </div>

            <div class="form-group">
              <label>Amount</label>
              <input type="number" step="0.0001" v-model="orderForm.amount" required />
            </div>

            <button type="submit">Place Order</button>
          </form>
        </section>

        <!-- Cards -->
        <section class="cards">
          <div class="card">
            <h3>USD Balance</h3>
            <p>${{ usdBalance }}</p>
          </div>
          <div class="card">
            <h3>BTC Balance</h3>
            <p>{{ assets.BTC || 0 }} BTC</p>
          </div>
          <div class="card">
            <h3>ETH Balance</h3>
            <p>{{ assets.ETH || 0 }} ETH</p>
          </div>
        </section>

        <!-- Orders Table -->
        <section class="orders">
          <h2>Recent Orders</h2>
          <table>
            <thead>
              <tr>
                <th>Symbol</th>
                <th>Side</th>
                <th>Price</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="orders.length === 0">
                <td colspan="5" style="text-align:center">No orders yet</td>
              </tr>
              <tr v-for="order in orders" :key="order.id">
                <td>{{ order.symbol }}</td>
                <td>{{ order.side }}</td>
                <td>${{ order.price }}</td>
                <td>{{ order.amount }}</td>
                <td>{{ statusText(order.status) }}</td>
              </tr>
            </tbody>
          </table>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { API_BASE_URL } from '../config'
import '../assets/css/dashboard.css'

const router = useRouter()

// User & balances
const userName = ref('User')
const usdBalance = ref(0)
const assets = ref({}) // BTC, ETH

// Orders
const orders = ref([])

// Limit Order form
const orderForm = ref({
  symbol: 'BTC',
  side: 'buy',
  price: '',
  amount: ''
})

// Messages
const message = ref({ text:'', type:'' }) // success | error

// Logout function
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user_id')
  router.push('/')
}

// Convert status code to text
const statusText = (status) => {
  switch(status){
    case 1: return 'Open'
    case 2: return 'Filled'
    case 3: return 'Cancelled'
    default: return 'Unknown'
  }
}

// Fetch profile (user + USD balance + assets)
const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`${API_BASE_URL}/profile`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    userName.value = res.data.user.name
    usdBalance.value = res.data.balance
    assets.value = res.data.assets || {}
  } catch(err) {
    console.error('Failed to fetch profile:', err)
  }
}

// Fetch orders
const fetchOrders = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return
    const res = await axios.get(`${API_BASE_URL}/orders`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = res.data
  } catch(err) {
    console.error("Failed to fetch orders:", err)
  }
}

// Place a limit order
const placeOrder = async () => {
  try {
    const token = localStorage.getItem('token')
    await axios.post(`${API_BASE_URL}/orders`, orderForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })

    message.value = { text: 'Order placed successfully!', type: 'success' }
    orderForm.value.price = ''
    orderForm.value.amount = ''

    // Refresh orders + balances instantly
    await fetchOrders()
    await fetchProfile()
  } catch(err){
    console.error(err)
    message.value = { text: err.response?.data?.message || 'Failed to place order', type: 'error' }
  }
}

// Initialize Laravel Echo for real-time
const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY || 'e0187631da2c6885a14a',
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap2',
  forceTLS: true,
  wsHost: import.meta.env.VITE_PUSHER_HOST || 'ws.pusherapp.com',
  wsPort: import.meta.env.VITE_PUSHER_PORT || 80,
  disableStats: true,
  encrypted: true,
  authEndpoint: `${API_BASE_URL}/broadcasting/auth`,
  auth: {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token')}`
    }
  }
})

// Real-time listener
onMounted(async () => {
  await fetchProfile()
  await fetchOrders()

  const userId = localStorage.getItem('user_id')
  if (userId) {
    echo.private(`user.${userId}`)
      .listen('OrderMatched', async (event) => {
        console.log('OrderMatched event received:', event)

        // Refresh balances & orders
        await fetchProfile()
        await fetchOrders()

        // Show success message
        message.value = { 
          text: `Order matched: ${event.amount} @ $${event.price}, Fee: $${event.fee}`, 
          type: 'success' 
        }
      })
  }
})

// Watch symbol change to fetch orders dynamically
watch(() => orderForm.value.symbol, fetchOrders)
</script>



