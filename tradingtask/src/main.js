import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'; // <-- import router

createApp(App)
  .use(router) // <-- tell Vue to use router
  .mount('#app');
