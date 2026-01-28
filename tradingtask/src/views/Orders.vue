<template>
  <div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">TradingTask</div>
      <nav>
        <ul>
          <li><router-link to="/dashboard">Dashboard</router-link></li>
          <li><router-link to="/orders" class="active">Orders</router-link></li>
          <li><button @click="logout">Logout</button></li>
        </ul>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="main">

      <!-- Top Navbar -->
      <header class="topbar">
        <h1>Orders</h1>
        <div class="user-info">Hello, <strong>{{ userName }}</strong></div>
      </header>

      <!-- Scrollable content -->
      <div class="content">

        <!-- Orders Filter Bar as Table -->
        <div class="orders-filter-table">
          <table>
            <tr>
              <td>
                <label for="symbol">Symbol</label>
                <select id="symbol" v-model="selectedSymbol">
                  <option value="">All</option>
                  <option value="BTC">BTC</option>
                  <option value="ETH">ETH</option>
                </select>
              </td>
              <td>
                <label for="status">Status</label>
                <select id="status" v-model="selectedStatus">
                  <option value="">All</option>
                  <option value="1">Open</option>
                  <option value="2">Filled</option>
                  <option value="3">Cancelled</option>
                </select>
              </td>
              <td>
                <label for="side">Side</label>
                <select id="side" v-model="selectedSide">
                  <option value="">All</option>
                  <option value="buy">Buy</option>
                  <option value="sell">Sell</option>
                </select>
              </td>
              <td>
                <button class="filter-btn" @click="applyFilters">Apply</button>
              </td>
            </tr>
          </table>
        </div>

        <!-- Orders Table -->
        <section class="orders-table">
          <h2>My Orders</h2>
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
              <tr v-if="filteredOrders.length === 0">
                <td colspan="5" style="text-align:center">No orders</td>
              </tr>
              <tr v-for="order in filteredOrders" :key="order.id">
                <td>{{ order.symbol }}</td>
                <td>{{ order.side }}</td>
                <td>${{ order.price }}</td>
                <td>{{ order.amount }}</td>
                <td>{{ statusText(order.status) }}</td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- Orderbook Section -->
        <section class="orderbook">
          <h2>Orderbook ({{ selectedSymbol || 'All' }})</h2>
          <div class="orderbook-lists">
            <div class="buy-orders">
              <h3>Buy Orders</h3>
              <ul>
                <li v-for="order in buyOrders" :key="order.id">
                  {{ order.amount }} @ ${{ order.price }}
                </li>
              </ul>
            </div>
            <div class="sell-orders">
              <h3>Sell Orders</h3>
              <ul>
                <li v-for="order in sellOrders" :key="order.id">
                  {{ order.amount }} @ ${{ order.price }}
                </li>
              </ul>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import { API_BASE_URL } from '../config'
import { createEcho } from '../echo'
import '../assets/css/orders.css'

const userName = ref('User')
const selectedSymbol = ref('')
const selectedStatus = ref('')
const selectedSide = ref('')

const orders = ref([])
const buyOrders = ref([])
const sellOrders = ref([])

// Logout
const logout = () => {
  localStorage.removeItem('token')
  window.location.href = '/'
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

// Fetch all orders
const fetchOrders = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`${API_BASE_URL}/orders`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = res.data

    updateOrderbook()
  } catch(err) {
    console.error('Failed to fetch orders:', err)
  }
}

// Update Buy/Sell orderbook
const updateOrderbook = () => {
  const symbol = selectedSymbol.value
  buyOrders.value = orders.value
    .filter(o => (symbol ? o.symbol === symbol : true) && o.side === 'buy' && o.status === 1)
    .sort((a,b) => b.price - a.price)

  sellOrders.value = orders.value
    .filter(o => (symbol ? o.symbol === symbol : true) && o.side === 'sell' && o.status === 1)
    .sort((a,b) => a.price - b.price)
}

// Apply filter button
const applyFilters = () => {
  updateOrderbook()
}

// Filtered orders for table
const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    return (
      (!selectedSymbol.value || o.symbol === selectedSymbol.value) &&
      (!selectedStatus.value || o.status == selectedStatus.value) &&
      (!selectedSide.value || o.side === selectedSide.value)
    )
  })
})

// Real-time setup
let echo
onMounted(async () => {
  const token = localStorage.getItem('token')
  if (token) {
    const profile = await axios.get(`${API_BASE_URL}/profile`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    userName.value = profile.data.user.name

    // Initialize echo
    echo = createEcho()
    echo.private(`user.${profile.data.user.id}`)
        .listen('OrderMatched', (e) => {
          console.log('OrderMatched event:', e)
          fetchOrders()
        })
  }

  fetchOrders()
})

// Watch symbol changes
watch(selectedSymbol, updateOrderbook)
</script>


