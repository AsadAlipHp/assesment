import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Signup from '../views/Signup.vue'
import Dashboard from '../views/Dashboard.vue'
import Orders from '../views/Orders.vue'

const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/signup', name: 'Signup', component: Signup },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/orders', name: 'Orders', component: Orders },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route guard
router.beforeEach((to, from, next) => {
  const publicPages = ['Login', 'Signup']
  const authRequired = !publicPages.includes(to.name)
  const loggedIn = localStorage.getItem('token')

  if (authRequired && !loggedIn) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

export default router
