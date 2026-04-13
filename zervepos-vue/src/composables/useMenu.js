import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { normalizeRole } from '../utils/roles'

const MENUS = {
  cajero: [
    { label: 'Ventas',     icon: '🛒', route: '/app/ventas' },
    { label: 'Productos',  icon: '📦', route: '/app/productos' },
    { label: 'Inventario', icon: '🏪', route: '/app/inventario' },
    { label: 'Clientes',   icon: '👥', route: '/app/clientes' },
  ],
  administrador: [
    { label: 'Ventas',     icon: '🛒', route: '/app/ventas' },
    { label: 'Productos',  icon: '📦', route: '/app/productos' },
    { label: 'Clientes',   icon: '👥', route: '/app/clientes' },
    { label: 'Inventario', icon: '🏪', route: '/app/inventario' },
    { label: 'Usuarios',   icon: '👤', route: '/app/usuarios' },
  ],
  dueno: [
    { label: 'Inicio',     icon: '🏠', route: '/app/dashboard' },
    { label: 'Ventas',     icon: '🛒', route: '/app/ventas' },
    { label: 'Productos',  icon: '📦', route: '/app/productos' },
    { label: 'Clientes',   icon: '👥', route: '/app/clientes' },
    { label: 'Inventario', icon: '🏪', route: '/app/inventario' },
    { label: 'Reportes',   icon: '📊', route: '/app/reportes' },
    { label: 'Usuarios',   icon: '👤', route: '/app/usuarios' },
  ],
}

export function useMenu() {
  const auth   = useAuthStore()
  const router = useRouter()

  const menuItems = computed(() => {
    const role = normalizeRole(auth.rol)
    return MENUS[role] ?? []
  })

  function logout() {
    auth.logout()
    router.push('/login')
  }

  return { menuItems, logout }
}