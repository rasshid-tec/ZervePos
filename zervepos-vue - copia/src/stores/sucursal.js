import { defineStore } from 'pinia'

export const useSucursalStore = defineStore('sucursal', {
  state: () => ({
    sucursales: [],
    sucursalActiva: JSON.parse(sessionStorage.getItem('sucursalActiva')) || null
  }),
  actions: {
    setSucursales(lista) {
      this.sucursales = lista
    },
    setSucursalActiva(sucursal) {
      this.sucursalActiva = sucursal
      sessionStorage.setItem('sucursalActiva', JSON.stringify(sucursal))
    },
    limpiar() {
      this.sucursales = []
      this.sucursalActiva = null
      sessionStorage.removeItem('sucursalActiva')
    }
  }
})