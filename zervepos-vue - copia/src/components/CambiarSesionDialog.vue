<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="cerrar">
      <div class="modal-card">
        <div class="modal-header">
          <h2>Cambiar sesión</h2>
          <button class="btn-cerrar" @click="cerrar">✕</button>
        </div>

        <div class="modal-body">
          <div class="campo">
            <label for="cs-usuario">Usuario</label>
            <input
              id="cs-usuario"
              v-model="nombreUsuario"
              type="text"
              autocomplete="off"
              @keyup.enter="onCambiar"
            />
          </div>
          <div class="campo">
            <label for="cs-pass">Contraseña</label>
            <input
              id="cs-pass"
              v-model="contrasena"
              type="password"
              @keyup.enter="onCambiar"
            />
          </div>
          <button class="btn-cambiar" :disabled="cargando" @click="onCambiar">
            {{ cargando ? 'Cambiando...' : 'Cambiar' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'
import { useToast } from '../composables/useToast'

const props = defineProps({ visible: Boolean })
const emit = defineEmits(['update:visible'])

const auth = useAuthStore()
const sucursalStore = useSucursalStore()
const router = useRouter()
const toast = useToast()

const nombreUsuario = ref('')
const contrasena = ref('')
const cargando = ref(false)

watch(() => props.visible, (v) => {
  if (!v) {
    nombreUsuario.value = ''
    contrasena.value = ''
  }
})

const cerrar = () => emit('update:visible', false)

const onCambiar = async () => {
  if (!nombreUsuario.value || !contrasena.value) {
    toast.error('Completa usuario y contraseña')
    return
  }
  cargando.value = true
  try {
    const res = await fetch('/php/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        NombreUsuario: nombreUsuario.value,
        Contrasena: contrasena.value
      })
    })
    const data = await res.json()

    if (data.status === 1) {
      auth.aplicarSesion(data)
      sucursalStore.limpiar()
      toast.success('Sesión cambiada')
      cerrar()

      if (data.Rol === 'Cajero') {
        router.push('/app/dashboard')
      } else {
        router.push('/seleccionar-sucursal')
      }
    } else {
      toast.error('Usuario o contraseña incorrectos')
    }
  } catch (e) {
    toast.error('Error de conexión')
  } finally {
    cargando.value = false
  }
}
</script>
<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9000;
}
.modal-card {
  background: #fff;
  border-radius: 14px;
  width: 400px;
  max-width: 90vw;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}
.modal-header {
  background: #1a3a4a;
  color: #fff;
  padding: 16px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h2 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
}
.btn-cerrar {
  background: none;
  border: none;
  color: #fff;
  font-size: 1rem;
  cursor: pointer;
  opacity: 0.75;
}
.btn-cerrar:hover { opacity: 1; }

.modal-body {
  padding: 24px 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.campo {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.campo label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334;
}
.campo input {
  padding: 10px 12px;
  border: 1px solid #d0d6dc;
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}
.campo input:focus {
  border-color: #1a3a4a;
}
.btn-cambiar {
  margin-top: 6px;
  background: #1d9e75;
  color: #fff;
  border: none;
  padding: 11px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-cambiar:hover:not(:disabled) { background: #178a64; }
.btn-cambiar:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease; }
.modal-enter-active .modal-card,
.modal-leave-active .modal-card { transition: transform 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-card,
.modal-leave-to .modal-card { transform: scale(0.95); }
</style>