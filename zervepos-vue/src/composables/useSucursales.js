import { ref } from 'vue'
import { useAuthStore }     from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'
import { useToast }         from './useToast'

export function useSucursales() {
  const auth          = useAuthStore()
  const sucursalStore = useSucursalStore()
  const toast         = useToast()
  const cargando      = ref(false)

  const cargarSucursales = async () => {
    cargando.value = true
    try {
      const res  = await fetch('/php/obtener_sucursales.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ UsuariosId: auth.usuarioId })
      })
      const data = await res.json()
      if (data.status === 1) {
        sucursalStore.setSucursales(data.sucursales)
      } else {
        toast.error('No se pudieron cargar las sucursales')
      }
    } catch {
      toast.error('Error de conexión al cargar sucursales')
    } finally {
      cargando.value = false
    }
  }

  const seleccionarSucursal = (sucursal) => {
    sucursalStore.setSucursalActiva(sucursal)
    auth.setSucursal(sucursal.SucursalId, sucursal.NombreSucursal)
  }

  return { cargando, cargarSucursales, seleccionarSucursal }
}