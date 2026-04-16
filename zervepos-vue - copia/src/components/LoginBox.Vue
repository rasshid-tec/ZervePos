<template>
  <div class="login-box">
    <h2>Iniciar Sesión</h2>
    <p>Bienvenido de nuevo</p>

    <div class="input-box">
      <label>Usuario</label>
      <input v-model="nombreUsuario" type="text" placeholder="Ingresa tu usuario" />
    </div>

    <div class="input-box">
      <label>Contraseña</label>
      <input v-model="contrasena" type="password" placeholder="Ingresa tu contraseña" />
    </div>

    <button class="btn-login" @click="handleLogin" :disabled="cargando">
      <span v-if="!cargando">Entrar</span>
      <span v-else class="spinner"></span>
    </button>
  </div>

  <AlertaToast ref="alerta" />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AlertaToast from './AlertaToast.vue'

const router = useRouter()
const auth = useAuthStore()
const alerta = ref(null)
const nombreUsuario = ref('')
const contrasena = ref('')
const cargando = ref(false)

async function handleLogin() {
  if (!nombreUsuario.value || !contrasena.value) {
    alerta.value.mostrar('Por favor ingresa tu usuario y contraseña.', 'advertencia')
    return
  }

  cargando.value = true
  const resultado = await auth.iniciarSesion(nombreUsuario.value, contrasena.value)
  cargando.value = false

  if (resultado?.error) {
    alerta.value.mostrar(resultado.error, 'error')
  }

  if (resultado?.ok) {
    alerta.value.mostrar(`¡Bienvenido, ${resultado.nombre}!`, 'exito')
    setTimeout(() => router.push(resultado.destino), 1500)
  }
}
</script>

<style scoped>
.login-box {
  position: relative;
  z-index: 1;
  width: 380px;
  padding: 44px 36px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: white;
}

h2 {
  text-align: center;
  font-size: 30px;
  font-weight: 700;
  margin-bottom: 8px;
  letter-spacing: 0.3px;
}

p {
  text-align: center;
  color: rgba(255,255,255,0.55);
  font-size: 14px;
  margin-bottom: 32px;
}

.input-box {
  margin-bottom: 20px;
}

.input-box label {
  display: block;
  font-size: 13px;
  color: rgba(255,255,255,0.75);
  margin-bottom: 8px;
}

.input-box input {
  width: 100%;
  padding: 13px 16px;
  border: 1px solid rgba(255,255,255,0.08);
  outline: none;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.06);
  color: white;
  font-size: 15px;
  transition: all 0.25s;
  box-sizing: border-box;
}

.input-box input::placeholder { color: rgba(255,255,255,0.3); }

.input-box input:focus {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(55, 138, 221, 0.6);
  box-shadow: 0 0 0 3px rgba(55, 138, 221, 0.15);
}

.btn-login {
  width: 100%;
  padding: 14px;
  margin-top: 8px;
  border: none;
  border-radius: 10px;
  background: #2479d4;
  color: white;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
}

.btn-login:hover:not(:disabled) {
  background: #1a6bc4;
  transform: translateY(-1px);
}

.btn-login:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>