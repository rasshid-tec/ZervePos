import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const usuario        = ref(sessionStorage.getItem('usuarioLogueado') || '')
  const rol            = ref(sessionStorage.getItem('rolUsuario')      || '')
  const empleadoId     = ref(sessionStorage.getItem('empleadoId')     || null)
  const usuarioId      = ref(sessionStorage.getItem('usuarioId')      || null)
  const sucursalId     = ref(sessionStorage.getItem('sucursalId')     || null)
  const sucursalNombre = ref(sessionStorage.getItem('sucursalNombre')  || '')

  async function iniciarSesion(nombreUsuario, contrasena) {
    try {
      const respuesta = await fetch('/php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ NombreUsuario: nombreUsuario, Contrasena: contrasena })
      })
      const data = await respuesta.json()

      if (data.status === 1) {
        // Guardamos en el estado y sessionStorage
        aplicarSesion(data)

        // --- LÓGICA DE REDIRECCIÓN ---

        // 1. CAJERO: Validar caja de su sucursal
        if (data.Rol === 'Cajero') {
          // Si tiene sucursal directa la tomamos, si no, la primera de su lista de acceso
          const sId = data.SucursalId || (data.AccesoSucursales[0]?.SucursalId)
          const sNom = data.AccesoSucursales[0]?.NombreSucursal || ''
          
          if (!sId) return { error: 'Cajero sin sucursal asignada.' }

          // Guardamos datos de sucursal de una vez
          setSucursal(sId, sNom)

          // Petición al PHP de verificar caja
          const cajaStatus = await verificarCajaServidor(sId)
          
          if (cajaStatus.abierta) {
            sessionStorage.setItem('cajaId', cajaStatus.cajaId)
            return { ok: true, nombre: data.NombreCompleto, destino: '/app/dashboard' }
          } else {
            return { ok: true, nombre: data.NombreCompleto, destino: '/caja/abrir' }
          }
        }

        // 2. DUEÑO / ADMINISTRADOR: Selección de sucursal
        // Guardamos la lista para que la vista 'SeleccionarSucursal' la pinte
        sessionStorage.setItem('misSucursales', JSON.stringify(data.AccesoSucursales))
        return { ok: true, nombre: data.NombreCompleto, destino: '/seleccionar-sucursal' }

      } else {
        return { error: data.mensaje || 'Credenciales inválidas.' }
      }
    } catch (error) {
      console.error('Error login:', error)
      return { error: 'Error de comunicación con el servidor.' }
    }
  }

  async function verificarCajaServidor(sId) {
    try {
      const resp = await fetch(`/php/verificar_caja.php?sucursalId=${sId}`)
      const d = await resp.json()
      // Normalizamos la respuesta según tu lógica de verificar_caja.php
      return { 
        abierta: d.cajaAbierta === true || d.status === 1, 
        cajaId: d.CajaId || null 
      }
    } catch (e) {
      return { abierta: false, cajaId: null }
    }
  }

  function aplicarSesion(data) {
    usuario.value        = data.NombreCompleto
    rol.value            = data.Rol
    empleadoId.value     = data.EmpleadoId
    usuarioId.value      = data.UsuarioId
    sucursalId.value     = data.SucursalId
    
    sessionStorage.setItem('usuarioLogueado', data.NombreCompleto)
    sessionStorage.setItem('rolUsuario',      data.Rol)
    sessionStorage.setItem('empleadoId',      data.EmpleadoId)
    sessionStorage.setItem('usuarioId',       data.UsuarioId)
    sessionStorage.setItem('sucursalId',      data.SucursalId || '')
  }

  function setSucursal(id, nombre) {
    sucursalId.value = id
    sucursalNombre.value = nombre
    sessionStorage.setItem('sucursalId', id)
    sessionStorage.setItem('sucursalNombre', nombre)
  }

  function logout() {
    sessionStorage.clear()
    location.reload() 
  }

  return {
    usuario, rol, empleadoId, usuarioId, sucursalId, sucursalNombre,
    iniciarSesion, setSucursal, logout
  }
})