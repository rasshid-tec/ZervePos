import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { normalizeRole } from '../utils/roles'

const MENUS = {
  cajero: [
    { label: 'Ventas',     icon: 'ventas',     route: '/app/ventas' },
    { label: 'Productos',  icon: 'productos',  route: '/app/productos' },
    { label: 'Inventario', icon: 'inventario', route: '/app/inventario' },
    { label: 'Clientes',   icon: 'clientes',   route: '/app/clientes' },
    { label: 'Créditos',   icon: 'creditos',   route: '/app/creditos' },
    { label: 'Tickets',    icon: 'tickets',    route: '/app/tickets' },
  ],
  administrador: [
    { label: 'Ventas',           icon: 'ventas',          route: '/app/ventas' },
    { label: 'Productos',        icon: 'productos',       route: '/app/productos' },
    { label: 'Clientes',         icon: 'clientes',        route: '/app/clientes' },
    { label: 'Inventario',       icon: 'inventario',      route: '/app/inventario' },
    { label: 'Créditos',         icon: 'creditos',        route: '/app/creditos' },
    { label: 'Tickets',          icon: 'tickets',         route: '/app/tickets' },
    { label: 'Reportes',         icon: 'reportes',        route: '/app/reportes' },
    { label: 'Notificaciones',   icon: 'notificaciones',  route: '/app/notificaciones' },
    { label: 'Usuarios',         icon: 'usuarios',        route: '/app/usuarios' },
  ],
  dueno: [
    { label: 'Inicio',           icon: 'home',            route: '/app/dashboard' },
    { label: 'Ventas',           icon: 'ventas',          route: '/app/ventas' },
    { label: 'Productos',        icon: 'productos',       route: '/app/productos' },
    { label: 'Clientes',         icon: 'clientes',        route: '/app/clientes' },
    { label: 'Inventario',       icon: 'inventario',      route: '/app/inventario' },
    { label: 'Créditos',         icon: 'creditos',        route: '/app/creditos' },
    { label: 'Tickets',          icon: 'tickets',         route: '/app/tickets' },
    { label: 'Reportes',         icon: 'reportes',        route: '/app/reportes' },
    { label: 'Notificaciones',   icon: 'notificaciones',  route: '/app/notificaciones' },
    { label: 'Usuarios',         icon: 'usuarios',        route: '/app/usuarios' },
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