<template>
  <div class="login-page">
    <!-- Left side with gradient and illustration -->
    <div class="login-left">
      <div class="login-text">
        <h1>Welcome Back!</h1>
        <p>Login to access your dashboard and manage your tasks seamlessly.</p>
      </div>
      <img src="/assets/login-illustration.svg" alt="Login Illustration" class="illustration" />
    </div>

    <!-- Right side login card -->
    <div class="login-right">
      <div class="login-card">
        <h2>Login</h2>
        
        <!-- Error message -->
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        
        <form @submit.prevent="handleLogin">
          <input type="email" v-model="email" placeholder="Email" required />
          <input type="password" v-model="password" placeholder="Password" required />
          <button type="submit">Login</button>
        </form>
        <p class="signup-link">
          Don't have an account? <a href="/signup">Sign Up</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { API_BASE_URL } from '../config'
import axios from 'axios'
import { useRouter } from 'vue-router'

// Form state
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const router = useRouter()

// Login function
const handleLogin = async () => {
  errorMessage.value = '' // clear previous errors
  try {
    const response = await axios.post(`${API_BASE_URL}/login`, {
      email: email.value,
      password: password.value
    })

    // Save token or user info in localStorage
    localStorage.setItem('token', response.data.token)
    
    // Redirect to dashboard
    router.push('/dashboard')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed'
  }
}
</script>

<style scoped>
/* Full page layout */
.login-page {
  display: flex;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

/* Left side with gradient and text */
.login-left {
  flex: 1;
  background: linear-gradient(to bottom right, #42b883, #2c3e50);
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 3rem;
  text-align: center;
}

.login-left h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.login-left p {
  font-size: 1.2rem;
  max-width: 400px;
}

.login-left .illustration {
  margin-top: 2rem;
  max-width: 80%;
}

/* Right side login card */
.login-right {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
}

.login-card {
  background: white;
  padding: 3rem 2.5rem;
  border-radius: 15px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.2);
  width: 350px;
  text-align: center;
}

.login-card h2 {
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

.login-card input {
  width: 100%;
  padding: 0.8rem;
  margin-bottom: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1rem;
  outline: none;
}

.login-card input:focus {
  border-color: #42b883;
  box-shadow: 0 0 5px #42b88344;
}

.login-card button {
  width: 100%;
  padding: 0.9rem;
  background: #42b883;
  color: white;
  font-size: 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.login-card button:hover {
  background: #36966e;
  transform: translateY(-2px);
}

.signup-link {
  margin-top: 1rem;
  font-size: 0.9rem;
}

.signup-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: bold;
}

.signup-link a:hover {
  text-decoration: underline;
}

.error {
  color: red;
  margin-bottom: 1rem;
  font-weight: bold;
}

/* Responsive */
@media (max-width: 900px) {
  .login-page {
    flex-direction: column;
  }
  .login-left {
    padding: 2rem;
  }
  .login-left .illustration {
    max-width: 60%;
    margin-top: 1rem;
  }
  .login-right {
    padding: 2rem;
  }
}
</style>
