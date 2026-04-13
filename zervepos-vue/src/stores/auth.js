import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const usuario    = ref(sessionStorage.getItem('usuarioLogueado') || '')
  const rol        = ref(sessionStorage.getItem('rolUsuario')      || '')
  const empleadoId = ref(sessionStorage.getItem('empleadoId')      || null)
  const usuarioId  = ref(sessionStorage.getItem('usuarioId')       || null)

  async function iniciarSesion(nombreUsuario, contrasena) {
    try {
      const respuesta = await fetch('/php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ NombreUsuario: nombreUsuario, Contrasena: contrasena })
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
      console.error('Error en iniciarSesion:', error)
      return { error: 'Error interno: No se pudo procesar la solicitud.' }
    }
  }

  function aplicarSesion(data) {
    usuario.value    = data.NombreCompleto
    rol.value        = data.Rol
    empleadoId.value = data.EmpleadoId
    usuarioId.value  = data.UsuarioId

    sessionStorage.setItem('usuarioLogueado', data.NombreCompleto)
    sessionStorage.setItem('rolUsuario',      data.Rol)
    sessionStorage.setItem('empleadoId',      data.EmpleadoId)
    sessionStorage.setItem('usuarioId',       data.UsuarioId)
    sessionStorage.setItem('sucursalId',      data.SucursalId)
  }

  function setSucursal(id, nombre) {
    sessionStorage.setItem('sucursalId',     id)
    sessionStorage.setItem('sucursalNombre', nombre)
  }

  function logout() {
    usuario.value    = ''
    rol.value        = ''
    empleadoId.value = null
    usuarioId.value  = null
    sessionStorage.clear()
  }

  return { usuario, rol, empleadoId, usuarioId, iniciarSesion, aplicarSesion, logout, setSucursal }
})