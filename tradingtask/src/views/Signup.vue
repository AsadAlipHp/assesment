<template>
  <div class="signup-page">
    <!-- Left side with gradient and illustration -->
    <div class="signup-left">
      <div class="signup-text">
        <h1>Join Us!</h1>
        <p>Create your account and start trading securely.</p>
      </div>
      <img src="/assets/signup-illustration.svg" alt="Signup Illustration" class="illustration" />
    </div>

    <!-- Right side signup card -->
    <div class="signup-right">
      <div class="signup-card">
        <h2>Sign Up</h2>

        <!-- Error message -->
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <form @submit.prevent="handleSignup">
          <input type="text" v-model="name" placeholder="Full Name" required />
          <input type="email" v-model="email" placeholder="Email" required />
          <input type="password" v-model="password" placeholder="Password" required />
          <input type="password" v-model="password_confirmation" placeholder="Confirm Password" required />
          <button type="submit">Create Account</button>
        </form>

        <p class="login-link">
          Already have an account? <a href="/">Login</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { API_BASE_URL } from '../config'

const router = useRouter()

// Form data
const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const errorMessage = ref('')

// Signup function
const handleSignup = async () => {
  errorMessage.value = ''
  try {
    const response = await axios.post(`${API_BASE_URL}/register`, {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    })

    // Save token (optional if your API returns token)
    localStorage.setItem('token', response.data.token)

    // Redirect to Dashboard
    router.push('/dashboard')
  } catch (error) {
    // Show backend validation errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      errorMessage.value = Object.values(errors).flat().join(', ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Signup failed'
    }
  }
}
</script>

<style scoped>
/* Full page layout */
.signup-page {
  display: flex;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

/* Left side with gradient and illustration */
.signup-left {
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

.signup-left h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.signup-left p {
  font-size: 1.2rem;
  max-width: 400px;
}

.signup-left .illustration {
  margin-top: 2rem;
  max-width: 80%;
}

/* Right side signup card */
.signup-right {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
}

.signup-card {
  background: white;
  padding: 3rem 2.5rem;
  border-radius: 15px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.2);
  width: 350px;
  text-align: center;
}

.signup-card h2 {
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

.signup-card input {
  width: 100%;
  padding: 0.8rem;
  margin-bottom: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1rem;
  outline: none;
}

.signup-card input:focus {
  border-color: #42b883;
  box-shadow: 0 0 5px #42b88344;
}

.signup-card button {
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

.signup-card button:hover {
  background: #36966e;
  transform: translateY(-2px);
}

.login-link {
  margin-top: 1rem;
  font-size: 0.9rem;
}

.login-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: bold;
}

.login-link a:hover {
  text-decoration: underline;
}

.error {
  color: red;
  margin-bottom: 1rem;
  font-weight: bold;
}

/* Responsive */
@media (max-width: 900px) {
  .signup-page {
    flex-direction: column;
  }
  .signup-left {
    padding: 2rem;
  }
  .signup-left .illustration {
    max-width: 60%;
    margin-top: 1rem;
  }
  .signup-right {
    padding: 2rem;
  }
}
</style>
