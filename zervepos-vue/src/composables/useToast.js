import { ref } from 'vue'

const toastRef = ref(null)

export function registrarToast(instancia) {
  toastRef.value = instancia
}

export function useToast() {
  const mostrar = (msg, tipo) => {
    if (toastRef.value && toastRef.value.mostrar) {
      toastRef.value.mostrar(msg, tipo)
    } else {
      console.warn('AlertaToast no está montado')
    }
  }

  return {
    success: (msg) => mostrar(msg, 'exito'),
    error:   (msg) => mostrar(msg, 'error'),
    warn:    (msg) => mostrar(msg, 'advertencia')
  }
}