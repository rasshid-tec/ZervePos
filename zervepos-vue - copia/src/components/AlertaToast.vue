<template>
  <Transition name="toast">
    <div v-if="visible" :class="['toast', tipo]">
      <div class="toast-icono">
        <span v-if="tipo === 'exito'">✓</span>
        <span v-if="tipo === 'error'">✕</span>
        <span v-if="tipo === 'advertencia'">!</span>
      </div>
      <span class="toast-mensaje">{{ mensaje }}</span>
      <button class="toast-cerrar" @click="visible = false">✕</button>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const mensaje = ref('')
const tipo = ref('exito')

function mostrar(msg, t = 'exito', duracion = 3500) {
  mensaje.value = msg
  tipo.value = t
  visible.value = true
  setTimeout(() => visible.value = false, duracion)
}

defineExpose({ mostrar })
</script>

<style scoped>
.toast {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  color: white;
  min-width: 280px;
  max-width: 380px;
  backdrop-filter: blur(10px);
}

.toast.exito       { background: rgba(15, 110, 86, 0.92); border: 1px solid rgba(29,158,117,0.5); }
.toast.error       { background: rgba(153, 60, 29, 0.92); border: 1px solid rgba(216,90,48,0.5); }
.toast.advertencia { background: rgba(133, 79, 11, 0.92); border: 1px solid rgba(186,117,23,0.5); }

.toast-icono {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  flex-shrink: 0;
}

.toast-mensaje { flex: 1; line-height: 1.4; }

.toast-cerrar {
  background: none;
  border: none;
  color: rgba(255,255,255,0.6);
  cursor: pointer;
  font-size: 12px;
  padding: 2px 4px;
  flex-shrink: 0;
}

.toast-cerrar:hover { color: white; }

.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.4,0,0.2,1); }
.toast-enter-from  { opacity: 0; transform: translateX(40px); }
.toast-leave-to    { opacity: 0; transform: translateX(40px); }
</style>