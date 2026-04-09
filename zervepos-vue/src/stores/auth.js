import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const usuario = ref('')
  const rol = ref('')

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
        usuario.value = data.NombreCompleto
        rol.value = data.Rol

        sessionStorage.setItem('usuarioId', data.UsuarioId)
        sessionStorage.setItem('usuarioLogueado', data.NombreCompleto)
        sessionStorage.setItem('rolUsuario', data.Rol)
        sessionStorage.setItem('sucursalId', data.SucursalId)

        // CORRECCIÓN 1: Objeto de rutas asignado correctamente
        const rutasRoles = {
          'Cajero': '/cajero',
          'Administrador': '/seleccionar-sucursal',
          'Dueño': '/seleccionar-sucursal'
        }

        const destino = rutasRoles[data.Rol]

        if (!destino) return { error: 'Rol desconocido: ' + data.Rol }

        return { ok: true, nombre: data.NombreCompleto, destino }

      } else {
        return { error: 'Usuario o contraseña incorrectos.' }
      }

    } catch (error) {
      // CORRECCIÓN 2: Mostrar el error real en la consola para depurar fácilmente
      console.error('Error atrapado en el JS:', error)
      return { error: 'Error interno: No se pudo procesar la solicitud.' }
    }
  }

  return { usuario, rol, iniciarSesion }
})