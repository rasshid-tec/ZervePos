<template>
  <div class="inventario-container">
    <!-- SECCIÓN DE ESTADÍSTICAS -->
    <section class="estadisticas-grid">
      <div class="estadistica-card">
        <div class="estadistica-header">
          <h3>Total Productos</h3>
          <span class="icono-settings">⚙️</span>
        </div>
        <p class="numero-grande">{{ estadisticas.totalProductos }}</p>
      </div>

      <div class="estadistica-card disponibles">
        <div class="estadistica-header">
          <h3>Disponibles</h3>
          <span class="indicador-verde"></span>
        </div>
        <p class="numero-grande">{{ estadisticas.disponibles }}</p>
      </div>

      <div class="estadistica-card bajo-stock">
        <div class="estadistica-header">
          <h3>Bajo Stock</h3>
          <span class="indicador-amarillo"></span>
        </div>
        <p class="numero-grande">{{ estadisticas.bajoStock }}</p>
      </div>

      <div class="estadistica-card agotados">
        <div class="estadistica-header">
          <h3>Agotados</h3>
          <span class="indicador-rojo"></span>
        </div>
        <p class="numero-grande">{{ estadisticas.agotados }}</p>
      </div>
    </section>

    <!-- SECCIÓN DE BÚSQUEDA Y TABLA -->
    <section class="inventario-tabla-section">
      <div class="tabla-header">
        <div class="tabla-titulo">
          <h2>Inventario de Productos</h2>
          <p>Gestiona todos los productos de tu tienda</p>
        </div>

        <div class="tabla-controles">
          <!-- BUSCADOR -->
          <div class="buscador-wrapper">
            <input
              v-model="textoBusqueda"
              type="text"
              placeholder="Buscar producto..."
              class="input-busqueda"
              @keyup.enter="buscarProducto(textoBusqueda)"
            />
            <span class="icono-busqueda">🔍</span>
          </div>

          <!-- SELECTOR DE SUCURSALES (MULTI-SELECCIÓN) -->
          <div class="selector-sucursales-wrapper">
            <button class="btn-selector-sucursales" @click="toggleSelectorSucursales">
              <span>🏢</span>
              {{ sucursalesSeleccionadas.length }} Sucursal{{ sucursalesSeleccionadas.length !== 1 ? 'es' : '' }}
            </button>
            
            <div v-if="mostrarSelectorSucursales" class="dropdown-sucursales">
              <div class="dropdown-header">
                <h4>Seleccionar Sucursales</h4>
                <button @click="toggleSelectorSucursales" class="btn-cerrar">✕</button>
              </div>

              <div class="dropdown-acciones">
                <button @click="seleccionarTodas" class="btn-accion">
                  Seleccionar Todo
                </button>
                <button @click="deseleccionarTodas" class="btn-accion">
                  Desseleccionar Todo
                </button>
              </div>

              <div class="dropdown-items">
                <label v-for="sucursal in sucursales" :key="sucursal.SucursalId" class="checkbox-item">
                  <input
                    type="checkbox"
                    :checked="estaSucursalSeleccionada(sucursal.SucursalId)"
                    @change="alternarSucursal(sucursal.SucursalId)"
                  />
                  <span>{{ sucursal.NombreSucursal }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- BOTÓN AGREGAR PRODUCTO -->
          <button class="btn-agregar">
            <span>+</span> Agregar Producto
          </button>
        </div>
      </div>

      <!-- TABLA DE INVENTARIO -->
      <div class="tabla-wrapper">
        <table class="tabla-inventario" v-if="!cargando && inventarioFiltrado.length > 0">
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
            <tr v-for="item in inventarioFiltrado" :key="`${item.SKU}-${item.SucursalId}`" :class="`estado-${item.Estado.toLowerCase().replace(' ', '-')}`">
              <td class="sku">{{ item.SKU }}</td>
              <td class="producto">{{ item.Producto }}</td>
              <td class="precio">${{ parseFloat(item.Precio).toFixed(2) }}</td>
              <td class="sucursal">{{ item.NombreSucursal }}</td>
              <td class="stock" :class="`stock-${getClaseStock(item.Estado)}`">
                {{ item.Stock }} unidades
              </td>
              <td class="estado">
                <span :class="`badge-${item.Estado.toLowerCase().replace(' ', '-')}`">
                  {{ item.Estado }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- ESTADO DE CARGA -->
        <div v-if="cargando" class="estado-carga">
          <p>Cargando inventario...</p>
        </div>

        <!-- SIN RESULTADOS -->
        <div v-if="!cargando && inventarioFiltrado.length === 0" class="sin-resultados">
          <p>No hay productos que coincidan con tu búsqueda</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useInventario } from '../composables/useInventario';

const mostrarSelectorSucursales = ref(false);

const {
  inventario,
  cargando,
  sucursalesSeleccionadas,
  textoBusqueda,
  sucursales,
  inventarioFiltrado,
  estadisticas,
  obtenerInventario,
  buscarProducto,
  alternarSucursal,
  seleccionarTodas,
  deseleccionarTodas,
  estaSucursalSeleccionada
} = useInventario();

// ==================== MÉTODOS ====================

/**
 * Alterna la visibilidad del selector de sucursales
 */
const toggleSelectorSucursales = () => {
  mostrarSelectorSucursales.value = !mostrarSelectorSucursales.value;
};

/**
 * Determina la clase CSS según el estado del stock
 */
const getClaseStock = (estado) => {
  switch (estado) {
    case 'Agotado':
      return 'rojo';
    case 'Bajo Stock':
      return 'amarillo';
    default:
      return 'verde';
  }
};

// ==================== CICLO DE VIDA ====================

onMounted(() => {
  obtenerInventario();
});
</script>

<style scoped>
/* ==================== VARIABLES Y COLORES (TEMA CLARO) ==================== */
:root {
  --color-bg: #f5f5f5;
  --color-bg-card: #ffffff;
  --color-border: #e0e0e0;
  --color-text: #333333;
  --color-text-secondary: #666666;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  --color-primary: #0066cc;
}

/* ==================== CONTENEDOR PRINCIPAL ==================== */
.inventario-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

/* ==================== SECCIÓN DE ESTADÍSTICAS ==================== */
.estadisticas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.estadistica-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.estadistica-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.estadistica-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.estadistica-header h3 {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  margin: 0;
}

.icono-settings {
  font-size: 1.5rem;
  opacity: 0.3;
}

.numero-grande {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text);
}

/* Indicadores de color */
.indicador-verde,
.indicador-amarillo,
.indicador-rojo {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.estadistica-card.disponibles .indicador-verde {
  background: var(--color-success);
}

.estadistica-card.bajo-stock .indicador-amarillo {
  background: var(--color-warning);
}

.estadistica-card.agotados .indicador-rojo {
  background: var(--color-danger);
}

/* ==================== SECCIÓN TABLA Y BÚSQUEDA ==================== */
.inventario-tabla-section {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.tabla-header {
  padding: 2rem;
  border-bottom: 1px solid var(--color-border);
}

.tabla-titulo h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  color: var(--color-text);
}

.tabla-titulo p {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}

.tabla-controles {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
  align-items: center;
}

/* BUSCADOR */
.buscador-wrapper {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.input-busqueda {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  background: #fafafa;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-text);
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.input-busqueda:focus {
  outline: none;
  border-color: var(--color-primary);
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-busqueda::placeholder {
  color: var(--color-text-secondary);
}

.icono-busqueda {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-secondary);
}

/* SELECTOR SUCURSALES */
.selector-sucursales-wrapper {
  position: relative;
  min-width: 180px;
}

.btn-selector-sucursales {
  width: 100%;
  padding: 0.75rem 1rem;
  background: #fafafa;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-text);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.btn-selector-sucursales:hover {
  border-color: var(--color-primary);
  background: white;
}

.btn-selector-sucursales:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.dropdown-sucursales {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  z-index: 100;
  margin-top: 0.5rem;
}

.dropdown-header {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-header h4 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text);
}

.btn-cerrar {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--color-text-secondary);
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-cerrar:hover {
  color: var(--color-text);
}

.dropdown-acciones {
  padding: 0.75rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  gap: 0.5rem;
}

.btn-accion {
  flex: 1;
  padding: 0.5rem;
  background: #f5f5f5;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  color: var(--color-text);
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-accion:hover {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.dropdown-items {
  max-height: 300px;
  overflow-y: auto;
  padding: 0.5rem;
}

.checkbox-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.checkbox-item:hover {
  background: #f5f5f5;
}

.checkbox-item input {
  margin-right: 0.75rem;
  cursor: pointer;
}

.checkbox-item span {
  font-size: 0.875rem;
  color: var(--color-text);
}

/* BOTÓN AGREGAR */
.btn-agregar {
  padding: 0.75rem 1.5rem;
  background: var(--color-success);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.btn-agregar:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2);
}

.btn-agregar:active {
  transform: translateY(0);
}

/* ==================== TABLA ==================== */
.tabla-wrapper {
  overflow-x: auto;
}

.tabla-inventario {
  width: 100%;
  border-collapse: collapse;
}

.tabla-inventario thead {
  background: #f5f5f5;
  border-bottom: 2px solid var(--color-border);
  position: sticky;
  top: 0;
}

.tabla-inventario th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tabla-inventario td {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
  font-size: 0.875rem;
}

.tabla-inventario tbody tr {
  transition: all 0.2s ease;
}

.tabla-inventario tbody tr:hover {
  background: #f5f5f5;
}

/* Columnas específicas */
.sku {
  color: var(--color-primary);
  font-weight: 600;
}

.producto {
  color: var(--color-text);
  font-weight: 500;
}

.precio {
  color: var(--color-success);
  font-weight: 600;
}

.sucursal {
  color: var(--color-text);
  font-weight: 500;
}

.stock {
  font-weight: 600;
}

.stock-verde {
  color: var(--color-success);
}

.stock-amarillo {
  color: var(--color-warning);
}

.stock-rojo {
  color: var(--color-danger);
}

/* BADGES DE ESTADO */
.estado span {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-disponible {
  background: #d1fae5;
  color: #065f46;
}

.badge-bajo-stock {
  background: #fef3c7;
  color: #92400e;
}

.badge-agotado {
  background: #fee2e2;
  color: #991b1b;
}

/* ==================== ESTADOS ==================== */
.estado-carga,
.sin-resultados {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}

.estado-carga {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.estado-carga p::after {
  content: '';
  display: inline-block;
  margin-left: 0.5rem;
  width: 1rem;
  height: 1rem;
  border: 2px solid var(--color-text-secondary);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
  .inventario-container {
    padding: 1rem;
  }

  .estadisticas-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .numero-grande {
    font-size: 2rem;
  }

  .tabla-header {
    padding: 1rem;
  }

  .tabla-controles {
    flex-direction: column;
  }

  .buscador-wrapper,
  .selector-sucursales-wrapper,
  .btn-agregar {
    width: 100%;
  }

  .tabla-inventario th,
  .tabla-inventario td {
    padding: 0.75rem 0.5rem;
    font-size: 0.75rem;
  }

  .numero-grande {
    font-size: 1.75rem;
  }
}

@media (max-width: 480px) {
  .estadisticas-grid {
    grid-template-columns: 1fr;
  }

  .tabla-titulo h2 {
    font-size: 1.25rem;
  }

  .tabla-inventario th,
  .tabla-inventario td {
    padding: 0.5rem;
  }
}
</style>