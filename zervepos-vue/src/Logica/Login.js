import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const usuario = ref('')
  const rol = ref('')

  async function iniciarSesion(nombreUsuario, contrasena) {
    if (!nombreUsuario || !contrasena) {
      alert('Por favor, ingresa tu usuario y contraseña.')
      return
    }

    try {
      const respuesta = await fetch('/php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ NombreUsuario: nombreUsuario, Contrasena: contrasena })
      })

      const data = await respuesta.json()

      if (data.status === 'ok') {
        usuario.value = data.nombre
        rol.value = data.rol
        sessionStorage.setItem('usuarioLogueado', data.nombre)
        sessionStorage.setItem('rolUsuario', data.rol)

        switch (data.rol) {
          case 'Administrador': router.push('/admin'); break
          case 'Dueño':         router.push('/dueno'); break
          case 'Cajero':        router.push('/cajero'); break
          default: alert('Rol desconocido: ' + data.rol)
        }
      } else {
        alert('Error: ' + data.message)
      }
    } catch (error) {
      alert('Hubo un problema al conectar con el servidor.')
    }
  }

  return { usuario, rol, iniciarSesion }
})