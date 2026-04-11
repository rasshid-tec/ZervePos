import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const usuario       = ref(sessionStorage.getItem('usuarioLogueado') || '')
  const rol           = ref(sessionStorage.getItem('rolUsuario') || '')
  const usuarioId     = ref(Number(sessionStorage.getItem('usuarioId')) || 0)
  const empleadoId    = ref(Number(sessionStorage.getItem('empleadoId')) || 0)
  const sucursalId    = ref(Number(sessionStorage.getItem('sucursalId')) || 0)
  const sucursalNombre = ref(sessionStorage.getItem('sucursalNombre') || '')

  async function iniciarSesion(nombreUsuario, contrasena) {
    try {
      const respuesta = await fetch('/php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          NombreUsuario: nombreUsuario,
          Contrasena: contrasena
        })
      })

      const data = await respuesta.json()

      if (data.status === 1) {
        aplicarSesion(data)

        const rutasRoles = {
          'Cajero':        '/app/dashboard',
          'Administrador': '/seleccionar-sucursal',
          'Dueño':         '/seleccionar-sucursal'
        }

        const destino = rutasRoles[data.Rol]
        if (!destino) return { error: 'Rol desconocido: ' + data.Rol }

        return { ok: true, nombre: data.NombreCompleto, destino }
      } else {
        return { error: 'Usuario o contraseña incorrectos.' }
      }
    } catch (error) {
      console.error('Error atrapado en el JS:', error)
      return { error: 'Error interno: No se pudo procesar la solicitud.' }
    }
  }

  function aplicarSesion(data) {
    usuario.value     = data.NombreCompleto
    rol.value         = data.Rol
    usuarioId.value   = Number(data.UsuarioId) || 0
    empleadoId.value  = Number(data.EmpleadoId) || 0
    sucursalId.value  = Number(data.SucursalId) || 0
    sucursalNombre.value = ''

    sessionStorage.setItem('usuarioId',       data.UsuarioId)
    sessionStorage.setItem('empleadoId',      data.EmpleadoId)
    sessionStorage.setItem('usuarioLogueado', data.NombreCompleto)
    sessionStorage.setItem('rolUsuario',      data.Rol)
    sessionStorage.setItem('sucursalId',      data.SucursalId)
    sessionStorage.removeItem('sucursalNombre')
  }

  function setSucursal(id, nombre) {
    sucursalId.value     = Number(id) || 0
    sucursalNombre.value = nombre || ''
    sessionStorage.setItem('sucursalId',     id)
    sessionStorage.setItem('sucursalNombre', nombre)
  }

  function logout() {
    usuario.value        = ''
    rol.value            = ''
    usuarioId.value      = 0
    empleadoId.value     = 0
    sucursalId.value     = 0
    sucursalNombre.value = ''
    sessionStorage.clear()
  }

  return {
    usuario, rol, usuarioId, empleadoId, sucursalId, sucursalNombre,
    iniciarSesion, aplicarSesion, setSucursal, logout
  }
})