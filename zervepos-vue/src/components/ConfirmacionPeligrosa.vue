<template>
  <Transition name="confirmacion">
    <div v-if="visible" class="overlay" @click.self="cancelar">
      <div class="dialog">

        <div class="dialog__icono">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>

        <h3 class="dialog__titulo">{{ titulo }}</h3>
        <p class="dialog__mensaje">{{ mensaje }}</p>

        <div class="dialog__barra-wrap">
          <div class="dialog__barra" :style="{ width: progreso + '%' }"></div>
        </div>
        <p class="dialog__cuenta">Puedes confirmar en {{ segundosRestantes }}s...</p>

        <div class="dialog__acciones">
          <button class="btn btn--cancelar" @click="cancelar">Cancelar</button>
          <button class="btn btn--confirmar" :disabled="segundosRestantes > 0" @click="confirmar">
            {{ segundosRestantes > 0 ? `Espera ${segundosRestantes}s` : 'Confirmar' }}
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const visible          = ref(false)
const titulo           = ref('')
const mensaje          = ref('')
const segundosRestantes = ref(5)
const progreso         = ref(100)
const duracion         = 5

let intervalo    = null
let resolveFn    = null

function mostrar(t, m) {
  titulo.value            = t
  mensaje.value           = m
  segundosRestantes.value = duracion
  progreso.value          = 100
  visible.value           = true

  intervalo = setInterval(() => {
    segundosRestantes.value--
    progreso.value = (segundosRestantes.value / duracion) * 100
    if (segundosRestantes.value <= 0) clearInterval(intervalo)
  }, 1000)

  return new Promise((resolve) => { resolveFn = resolve })
}

function confirmar() {
  clearInterval(intervalo)
  visible.value = false
  resolveFn?.(true)
}

function cancelar() {
  clearInterval(intervalo)
  visible.value = false
  resolveFn?.(false)
}

defineExpose({ mostrar })
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.dialog {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  width: 420px;
  max-width: 90vw;
  box-shadow: 0 24px 64px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  text-align: center;
}
.dialog__icono {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #fff4ed;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.25rem;
}
.dialog__icono svg { width: 28px; height: 28px; color: #f97316; }
.dialog__titulo { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.dialog__mensaje { font-size: 0.875rem; color: #64748b; margin: 0; line-height: 1.6; }
.dialog__barra-wrap {
  width: 100%;
  height: 4px;
  background: #f1f5f9;
  border-radius: 2px;
  overflow: hidden;
  margin-top: 0.25rem;
}
.dialog__barra {
  height: 100%;
  background: #f97316;
  border-radius: 2px;
  transition: width 1s linear;
}
.dialog__cuenta { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.dialog__acciones { display: flex; gap: 0.75rem; width: 100%; margin-top: 0.5rem; }
.btn {
  flex: 1;
  padding: 0.65rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: background 0.2s, opacity 0.2s;
}
.btn--cancelar { background: #f1f5f9; color: #1a2b3c; }
.btn--cancelar:hover { background: #e2e8f0; }
.btn--confirmar { background: #f97316; color: #fff; }
.btn--confirmar:hover:not(:disabled) { background: #ea580c; }
.btn--confirmar:disabled { opacity: 0.5; cursor: not-allowed; }
.confirmacion-enter-active, .confirmacion-leave-active { transition: opacity 0.2s; }
.confirmacion-enter-from, .confirmacion-leave-to { opacity: 0; }
</style>