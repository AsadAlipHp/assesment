// src/echo.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { PUSHER_KEY, PUSHER_CLUSTER, PUSHER_ENCRYPTED, BACKEND } from './config'

window.Pusher = Pusher

export const createEcho = () => {
  const token = localStorage.getItem('token')

  return new Echo({
    broadcaster: 'pusher',
    key: PUSHER_KEY,
    cluster: PUSHER_CLUSTER,
    encrypted: PUSHER_ENCRYPTED,
    authEndpoint: `${BACKEND}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`
      }
    }
  })
}
