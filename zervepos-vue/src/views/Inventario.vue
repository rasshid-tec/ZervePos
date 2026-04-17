<template>
  <div class="inventario-container">

    <div class="page-header">
      <button class="btn-back" @click="$router.push('/app/inventario')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div>
        <h1 class="page-titulo">Inventario de Productos</h1>
        <p class="page-subtitulo">Consulta el stock actual por sucursal</p>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icono" style="background:#f1f5f9">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
        <div>
          <p class="stat-label">Total Productos</p>
          <p class="stat-valor">{{ estadisticas.totalProductos }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#f0fdf4">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <p class="stat-label">Disponibles</p>
          <p class="stat-valor verde">{{ estadisticas.disponibles }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#fff7ed">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
          <p class="stat-label">Bajo Stock</p>
          <p class="stat-valor naranja">{{ estadisticas.bajoStock }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#fef2f2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <p class="stat-label">Agotados</p>
          <p class="stat-valor rojo">{{ estadisticas.agotados }}</p>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-card">
      <div class="tabla-header">
        <div>
          <h2 class="tabla-titulo">Inventario</h2>
          <p class="tabla-subtitulo">Stock actual de todos los productos</p>
        </div>
        <div class="tabla-controles">

          <!-- Buscador -->
          <div class="buscador-wrap">
            <svg class="buscador-icono" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              v-model="textoBusqueda"
              type="text"
              placeholder="Buscar producto..."
              class="input-busqueda"
              @keyup.enter="buscarProducto(textoBusqueda)"
            />
          </div>

          <!-- Selector sucursales -->
          <div class="selector-wrap">
            <button class="btn-selector" @click="toggleSelectorSucursales">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              {{ sucursalesSeleccionadas.length }} Sucursal{{ sucursalesSeleccionadas.length !== 1 ? 'es' : '' }}
            </button>

            <div v-if="mostrarSelectorSucursales" class="dropdown">
              <div class="dropdown-header">
                <span>Sucursales</span>
                <button @click="toggleSelectorSucursales" class="btn-x">✕</button>
              </div>
              <div class="dropdown-acciones">
                <button @click="seleccionarTodas" class="btn-accion-sm">Todas</button>
                <button @click="deseleccionarTodas" class="btn-accion-sm">Ninguna</button>
              </div>
              <div class="dropdown-items">
                <label v-for="s in sucursales" :key="s.SucursalId" class="checkbox-item">
                  <input
                    type="checkbox"
                    :checked="estaSucursalSeleccionada(s.SucursalId)"
                    @change="alternarSucursal(s.SucursalId)"
                  />
                  <span>{{ s.NombreSucursal }}</span>
                </label>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla" v-if="!cargando && inventarioFiltrado.length > 0">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Precio</th>
              <th>Sucursal</th>
              <th>Stock</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in inventarioFiltrado" :key="`${item.SKU}-${item.SucursalId}`">
              <td class="td-sku">{{ item.SKU }}</td>
              <td class="td-producto">{{ item.Producto }}</td>
              <td class="td-precio">${{ parseFloat(item.Precio).toFixed(2) }}</td>
              <td>{{ item.NombreSucursal }}</td>
              <td :class="['td-stock', `stock-${getClaseStock(item.Estado)}`]">
                {{ item.Stock }} uds
              </td>
              <td>
                <span :class="['badge', `badge-${item.Estado.toLowerCase().replace(' ', '-')}`]">
                  {{ item.Estado }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="cargando" class="estado-vacio">
          <span class="spinner"></span>
          <p>Cargando inventario...</p>
        </div>

        <div v-if="!cargando && inventarioFiltrado.length === 0" class="estado-vacio">
          <p>No hay productos que coincidan</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useInventario } from '../composables/useInventario';

const mostrarSelectorSucursales = ref(false);

const {
  cargando, sucursalesSeleccionadas, textoBusqueda,
  sucursales, inventarioFiltrado, estadisticas,
  obtenerInventario, buscarProducto, alternarSucursal,
  seleccionarTodas, deseleccionarTodas, estaSucursalSeleccionada
} = useInventario();

const toggleSelectorSucursales = () => {
  mostrarSelectorSucursales.value = !mostrarSelectorSucursales.value;
};

const getClaseStock = (estado) => {
  if (estado === 'Agotado')    return 'rojo';
  if (estado === 'Bajo Stock') return 'naranja';
  return 'verde';
};

onMounted(() => obtenerInventario());
</script>

<style scoped>
.inventario-container { padding: 32px; max-width: 1100px; }

.page-header {
  display: flex; align-items: center; gap: 12px; margin-bottom: 28px;
}
.btn-back {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  width: 36px; height: 36px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; color: #1e293b;
  flex-shrink: 0;
}
.btn-back:hover { background: #f1f5f9; }
.page-titulo { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 13px; }

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.stat-icono {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-label { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin: 0 0 4px; }
.stat-valor { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.stat-valor.verde  { color: #16a34a; }
.stat-valor.naranja { color: #f97316; }
.stat-valor.rojo   { color: #dc2626; }

/* Tabla card */
.tabla-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.tabla-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 16px;
}
.tabla-titulo   { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.tabla-subtitulo { font-size: 13px; color: #64748b; margin: 0; }
.tabla-controles { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

/* Buscador */
.buscador-wrap { position: relative; }
.buscador-icono {
  position: absolute; left: 10px; top: 50%;
  transform: translateY(-50%); color: #94a3b8; pointer-events: none;
}
.input-busqueda {
  padding: 8px 12px 8px 34px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  width: 220px;
}
.input-busqueda:focus { border-color: #2563eb; }

/* Selector sucursales */
.selector-wrap { position: relative; }
.btn-selector {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 14px;
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; cursor: pointer; color: #1e293b;
}
.btn-selector:hover { background: #f1f5f9; }
.dropdown {
  position: absolute; top: calc(100% + 6px); right: 0;
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  z-index: 100; min-width: 220px;
}
.dropdown-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
  font-size: 13px; font-weight: 600; color: #1e293b;
}
.btn-x { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 14px; }
.dropdown-acciones { display: flex; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
.btn-accion-sm {
  flex: 1; padding: 5px 8px;
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 6px; font-size: 12px; cursor: pointer; color: #1e293b;
}
.btn-accion-sm:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.dropdown-items { padding: 8px; max-height: 260px; overflow-y: auto; }
.checkbox-item {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px; border-radius: 6px; cursor: pointer; font-size: 13px;
}
.checkbox-item:hover { background: #f8fafc; }

/* Tabla */
.tabla-wrapper { overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; }
.tabla thead { background: #f8fafc; }
.tabla th {
  padding: 12px 16px; text-align: left;
  font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}
.tabla td {
  padding: 12px 16px;
  font-size: 13px; color: #1e293b;
  border-bottom: 1px solid #f1f5f9;
}
.tabla tbody tr:hover { background: #f8fafc; }

.td-sku     { color: #2563eb; font-weight: 600; }
.td-producto { font-weight: 500; }
.td-precio  { color: #16a34a; font-weight: 600; }
.td-stock   { font-weight: 600; }
.stock-verde  { color: #16a34a; }
.stock-naranja { color: #f97316; }
.stock-rojo   { color: #dc2626; }

.badge {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.badge-disponible  { background: #f0fdf4; color: #16a34a; }
.badge-bajo-stock  { background: #fff7ed; color: #f97316; }
.badge-agotado     { background: #fef2f2; color: #dc2626; }

.estado-vacio {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 48px; gap: 12px; color: #94a3b8; font-size: 14px;
}
.spinner {
  width: 28px; height: 28px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>