import { createRouter, createWebHistory } from 'vue-router'
import Login               from '../views/Login.vue'
import SeleccionarSucursal from '../views/SeleccionarSucursal.vue'
import MainLayout          from '../layouts/MainLayout.vue'
import Dashboard           from '../views/Dashboard.vue'
import ComingSoon          from '../views/ComingSoon.vue'
import GestionUsuarios     from '../views/GestionUsuarios.vue'

// Importar componentes de Inventario
import MenuInventario      from '../views/MenuInventario.vue'
import Inventario          from '../views/Inventario.vue'
import Entradas            from '../views/Entradas.vue'
import Salidas             from '../views/Salidas.vue'
import GestionSucursales   from '../views/GestionSucursales.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                    redirect: '/login' },
    { path: '/login',               name: 'login',               component: Login },
    { path: '/seleccionar-sucursal',name: 'seleccionar-sucursal',component: SeleccionarSucursal },

    {
      path: '/app',
      component: MainLayout,
      children: [
        { path: '',          redirect: 'dashboard' },
        { path: 'dashboard', name: 'dashboard',  component: Dashboard  },
        { path: 'ventas',    name: 'ventas',     component: ComingSoon },
        { path: 'productos', name: 'productos',  component: ComingSoon },
        { path: 'clientes',  name: 'clientes',   component: ComingSoon },
        
        // Rutas de Inventario con subrutas
        {
          path: 'inventario',
          name: 'inventario',
          component: MenuInventario,
          children: [
            { path: 'gestion',    name: 'inventario-gestion',    component: Inventario },
            { path: 'entradas',   name: 'inventario-entradas',   component: Entradas },
            { path: 'salidas',    name: 'inventario-salidas',    component: Salidas },
            { path: 'sucursales', name: 'inventario-sucursales', component: GestionSucursales }
          ]
        },
        
        { path: 'usuarios',  name: 'usuarios',   component: GestionUsuarios },
        { path: 'reportes',  name: 'reportes',   component: ComingSoon },
      ],
    },
  ],
})

export default router