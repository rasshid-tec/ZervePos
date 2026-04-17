import { createRouter, createWebHistory } from 'vue-router'
import Login               from '../views/Login.vue'
import SeleccionarSucursal from '../views/SeleccionarSucursal.vue'
import MainLayout          from '../layouts/MainLayout.vue'
import Dashboard           from '../views/Dashboard.vue'
import ComingSoon          from '../views/ComingSoon.vue'
import GestionUsuarios     from '../views/GestionUsuarios.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                     redirect: '/login' },
    { path: '/login',                name: 'login',                component: Login },
    { path: '/seleccionar-sucursal', name: 'seleccionar-sucursal', component: SeleccionarSucursal },
    { path: '/caja/abrir',           name: 'caja-abrir',           component: () => import('../views/AbrirCaja.vue') },
    { path: '/caja/cerrar',          name: 'caja-cerrar',          component: () => import('../views/CerrarCaja.vue') },
    {
      path: '/app',
      component: MainLayout,
      children: [
        { path: '',                          redirect: 'dashboard' },
        { path: 'dashboard',                 name: 'dashboard',               component: Dashboard },
        { path: 'ventas',                    name: 'ventas',                  component: () => import('../views/VentasView.vue') },
        { path: 'productos',                 name: 'productos',               component: () => import('../views/productos/Productos.vue') },
        { path: 'productos/nuevo',           name: 'productos-nuevo',         component: () => import('../views/productos/RegistrarProducto.vue') },
        { path: 'productos/editar',          name: 'productos-editar',        component: () => import('../views/productos/EditarProducto.vue') },
        { path: 'productos/eliminar',        name: 'productos-eliminar',      component: () => import('../views/productos/EliminarProducto.vue') },
        { path: 'clientes',                  name: 'clientes',                component: () => import('../views/clientes/Clientes.vue') },
        { path: 'clientes/nuevo',            name: 'clientes-nuevo',          component: () => import('../views/clientes/RegistrarCliente.vue') },
        { path: 'clientes/editar',           name: 'clientes-editar',         component: () => import('../views/clientes/EditarCliente.vue') },
        { path: 'clientes/eliminar',         name: 'clientes-eliminar',       component: () => import('../views/clientes/EliminarCliente.vue') },
        { path: 'inventario',                name: 'inventario',              component: () => import('../views/MenuInventario.vue') },
        { path: 'inventario/gestion',        name: 'inventario-gestion',      component: () => import('../views/Inventario.vue') },
        { path: 'inventario/entradas',       name: 'inventario-entradas',     component: () => import('../views/Entradas.vue') },
        { path: 'inventario/entradas/exito', name: 'entrada-exito',           component: () => import('../views/EntradaExito.vue') },
        { path: 'inventario/salidas',        name: 'inventario-salidas',      component: () => import('../views/Salidas.vue') },
        { path: 'inventario/salidas/exito',  name: 'salida-exito',            component: () => import('../views/SalidaExito.vue') },
        { path: 'inventario/sucursales',     name: 'inventario-sucursales',   component: () => import('../views/GestionSucursales.vue') },
        { path: 'creditos',                  name: 'creditos',                component: () => import('../views/creditos/CreditosMenu.vue') },
        { path: 'creditos/consultar',        name: 'creditos-consultar',      component: () => import('../views/creditos/ConsultarCreditos.vue') },
        { path: 'creditos/pagar',            name: 'creditos-pagar',          component: () => import('../views/creditos/PagarCredito.vue') },
        { path: 'tickets',                   name: 'tickets',                 component: () => import('../views/tickets/TicketsMenu.vue') },
        { path: 'notificaciones', name: 'notificaciones', component: () => import('../views/Notificaciones.vue') },
        { path: 'reportes',                  name: 'reportes',                component: () => import('../views/reportes/ReportesMenu.vue') },
        { path: 'reportes/salidas',          name: 'reportes-salidas',        component: () => import('../views/reportes/ReporteSalidas.vue') },
        { path: 'reportes/ventas',           name: 'reportes-ventas',         component: () => import('../views/reportes/ReporteVentas.vue') },
        { path: 'reportes/creditos',         name: 'reportes-creditos',       component: () => import('../views/reportes/ReporteCreditos.vue') },
        { path: 'reportes/clientes',         name: 'reportes-clientes',       component: () => import('../views/reportes/ReporteClientes.vue') },
        { path: 'reportes/productos',        name: 'reportes-productos',      component: () => import('../views/reportes/ReporteProductos.vue') },
        { path: 'usuarios',                  name: 'usuarios',                component: GestionUsuarios },
      ],
    },
  ],
})

export default router