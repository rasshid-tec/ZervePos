<template>
  <div class="salidas-container">
    <div class="salidas-header">
      <router-link to="/app/inventario" class="btn-volver">← Volver</router-link>
      <h1>Salidas de Inventario</h1>
      <p>Registra productos que salen del almacén</p>
    </div>

    <div class="salidas-content">
      <!-- SELECTOR DE TIPO DE SALIDA -->
      <div class="tipo-salida-section">
        <div class="tipo-salida-grid">
          <div
            v-for="tipo in tiposSalida"
            :key="tipo.valor"
            @click="tipoSalida = tipo.valor"
            :class="['tipo-card', { activo: tipoSalida === tipo.valor }]"
          >
            <span class="tipo-icono">{{ tipo.icono }}</span>
            <span class="tipo-label">{{ tipo.label }}</span>
          </div>
        </div>
      </div>

      <div class="salidas-form-container">
        <!-- LADO IZQUIERDO: FORMULARIO -->
        <div class="salidas-form">
          <div class="form-section">
            <h3>📦 Información del Movimiento</h3>

            <!-- Búsqueda de Producto -->
            <div class="form-group">
              <label>Producto *</label>
              <div class="input-busqueda-wrapper">
                <input
                  v-model="productoBuscado"
                  @input="buscarProducto(productoBuscado)"
                  type="text"
                  placeholder="Buscar por nombre o SKU..."
                  class="input-busqueda"
                />
                <span class="icono">🔍</span>

                <!-- Dropdown de productos -->
                <div v-if="productosDisponibles.length > 0" class="dropdown-productos">
                  <div
                    v-for="producto in productosDisponibles"
                    :key="producto.ProductoId"
                    @click="agregarProducto(producto)"
                    class="dropdown-item"
                  >
                    <div class="item-nombre">{{ producto.NombreProducto }}</div>
                    <div class="item-sku">SKU: {{ producto.ProductoId }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sucursal Destino (solo para Transferencia) -->
            <div v-if="tipoSalida === 'Transferencia'" class="form-group">
              <label>Sucursal Destino *</label>
              <select v-model="sucursalDestino" class="input-select">
                <option value="">Selecciona una sucursal...</option>
                <option v-for="sucursal in sucursales" :key="sucursal.SucursalId" :value="sucursal.SucursalId">
                  {{ sucursal.NombreSucursal }}
                </option>
              </select>
            </div>

            <!-- Motivo (para Merma y Descuento) -->
            <div v-if="['Merma', 'Descuento'].includes(tipoSalida)" class="form-group">
              <label>Motivo *</label>
              <textarea
                v-model="motivo"
                :placeholder="`Ej: ${tipoSalida === 'Merma' ? 'Producto dañado, vencido...' : 'Corrección de error, ajuste...'}`"
                class="input-textarea"
                rows="3"
              ></textarea>
            </div>

            <!-- Referencia / Documento -->
            <div class="form-group">
              <label>Referencia / Documento</label>
              <input
                v-model="referencia"
                type="text"
                placeholder="Ej: Comprobante, Nota..."
                class="input-text"
              />
            </div>

            <!-- Notas Adicionales -->
            <div class="form-group">
              <label>Notas Adicionales</label>
              <textarea
                v-model="notas"
                placeholder="Observaciones..."
                class="input-textarea"
                rows="3"
              ></textarea>
            </div>
          </div>

          <!-- PRODUCTOS SELECCIONADOS -->
          <div class="form-section">
            <h3>📋 Productos Agregados</h3>

            <div v-if="productosSeleccionados.length === 0" class="sin-productos">
              <p>No hay productos agregados</p>
            </div>

            <div v-else class="productos-lista">
              <div
                v-for="producto in productosSeleccionados"
                :key="producto.ProductoId"
                class="producto-item"
              >
                <div class="producto-info">
                  <div class="producto-nombre">{{ producto.NombreProducto }}</div>
                  <div class="producto-sku">SKU: {{ producto.ProductoId }}</div>
                </div>

                <div class="producto-cantidad">
                  <input
                    :value="producto.cantidad"
                    @input="actualizarCantidad(producto.ProductoId, $event.target.value)"
                    type="number"
                    min="1"
                    class="input-cantidad"
                  />
                  <span class="cantidad-label">unidades</span>
                </div>

                <div class="producto-precio">
                  <span>${{ (producto.PrecioUnitario * producto.cantidad).toFixed(2) }}</span>
                </div>

                <button
                  @click="eliminarProducto(producto.ProductoId)"
                  class="btn-eliminar"
                  type="button"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- LADO DERECHO: RESUMEN -->
        <div class="salidas-resumen">
          <div class="resumen-card">
            <h3>Resumen del Movimiento</h3>

            <div class="resumen-tipo">
              <span class="label">Tipo</span>
              <span class="valor badge">{{ tipoSalida }}</span>
            </div>

            <div class="resumen-fecha">
              <span class="label">Fecha</span>
              <span class="valor">{{ fechaActual }}</span>
            </div>

            <div v-if="tipoSalida === 'Transferencia' && sucursalDestino" class="resumen-sucursal">
              <span class="label">Destino</span>
              <span class="valor">{{ sucursalDestinoNombre }}</span>
            </div>

            <div class="resumen-divider"></div>

            <div class="resumen-item">
              <span class="label">Total Productos:</span>
              <span class="valor">{{ resumen.totalProductos }}</span>
            </div>

            <div class="resumen-item">
              <span class="label">Total Cantidad:</span>
              <span class="valor">{{ resumen.totalCantidad }} unidades</span>
            </div>

            <div class="resumen-item total">
              <span class="label">Valor Total:</span>
              <span class="valor">${{ resumen.totalValor }}</span>
            </div>

            <div class="resumen-divider"></div>

            <!-- Botones de Acción -->
            <div class="resumen-acciones">
              <button
                @click="limpiarFormulario"
                type="button"
                class="btn-cancelar"
              >
                Cancelar
              </button>
              <button
                @click="registrarSalida"
                :disabled="!formularioValido || cargando"
                type="button"
                class="btn-registrar"
              >
                <span v-if="!cargando">✓ Registrar</span>
                <span v-else>Registrando...</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useSalidas } from '../composables/useSalidas';

const {
  tipoSalida,
  productoBuscado,
  productosDisponibles,
  productosSeleccionados,
  motivo,
  referencia,
  notas,
  sucursalDestino,
  sucursales,
  cargando,
  tiposSalida,
  resumen,
  formularioValido,
  obtenerProductosSucursal,
  obtenerSucursales,
  buscarProducto,
  agregarProducto,
  eliminarProducto,
  actualizarCantidad,
  registrarSalida,
  limpiarFormulario
} = useSalidas();

const fechaActual = computed(() => {
  const hoy = new Date();
  return hoy.toLocaleDateString('es-MX', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
});

const sucursalDestinoNombre = computed(() => {
  const sucursal = sucursales.value.find(s => s.SucursalId === sucursalDestino.value);
  return sucursal?.NombreSucursal || 'Selecciona una sucursal';
});

onMounted(() => {
  obtenerProductosSucursal();
  obtenerSucursales();
});
</script>

<style scoped>
/* ==================== VARIABLES ==================== */
:root {
  --color-bg: #f5f5f5;
  --color-bg-card: #ffffff;
  --color-border: #e0e0e0;
  --color-text: #333333;
  --color-text-secondary: #666666;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-primary: #0066cc;
}

/* ==================== CONTENEDOR ==================== */
.salidas-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

/* ==================== HEADER ==================== */
.salidas-header {
  margin-bottom: 2rem;
}

.btn-volver {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #f0f0f0;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  text-decoration: none;
  color: var(--color-text);
  font-size: 0.875rem;
  margin-bottom: 1rem;
  transition: all 0.2s ease;
}

.btn-volver:hover {
  background: var(--color-border);
}

.salidas-header h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  color: var(--color-text);
}

.salidas-header p {
  margin: 0;
  color: var(--color-text-secondary);
}

/* ==================== SELECTOR TIPO SALIDA ==================== */
.tipo-salida-section {
  margin-bottom: 2rem;
}

.tipo-salida-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.tipo-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.5rem;
  background: var(--color-bg-card);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tipo-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.1);
}

.tipo-card.activo {
  border-color: var(--color-primary);
  background: rgba(0, 102, 204, 0.05);
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
}

.tipo-icono {
  font-size: 1.5rem;
}

.tipo-label {
  font-weight: 600;
  font-size: 0.875rem;
  text-align: center;
}

/* ==================== FORMULARIO ==================== */
.salidas-form-container {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 2rem;
}

.salidas-form {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
}

.form-section:last-child {
  margin-bottom: 0;
}

.form-section h3 {
  margin: 0 0 1.5rem 0;
  font-size: 1rem;
  color: var(--color-text);
  font-weight: 600;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--color-text);
}

.input-text,
.input-textarea,
.input-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.input-text:focus,
.input-textarea:focus,
.input-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding-right: 2rem;
}

.input-textarea {
  resize: vertical;
  min-height: 80px;
}

/* BÚSQUEDA DE PRODUCTO */
.input-busqueda-wrapper {
  position: relative;
}

.input-busqueda {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.input-busqueda:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-busqueda-wrapper .icono {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-secondary);
  pointer-events: none;
}

.dropdown-productos {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-top: none;
  border-radius: 0 0 6px 6px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  padding: 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid var(--color-border);
  transition: all 0.2s ease;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background: #f5f5f5;
}

.item-nombre {
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--color-text);
}

.item-sku {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

/* PRODUCTOS SELECCIONADOS */
.sin-productos {
  text-align: center;
  padding: 2rem;
  color: var(--color-text-secondary);
  background: #fafafa;
  border-radius: 6px;
}

.productos-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.producto-item {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  gap: 1rem;
  align-items: center;
  padding: 1rem;
  background: #fafafa;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  transition: all 0.2s ease;
}

.producto-item:hover {
  background: #f0f0f0;
}

.producto-info {
  min-width: 0;
}

.producto-nombre {
  font-weight: 500;
  color: var(--color-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.producto-sku {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

.producto-cantidad {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.input-cantidad {
  width: 70px;
  padding: 0.5rem;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-size: 0.875rem;
  text-align: center;
}

.cantidad-label {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  white-space: nowrap;
}

.producto-precio {
  font-weight: 600;
  color: var(--color-success);
  min-width: 80px;
  text-align: right;
}

.btn-eliminar {
  width: 32px;
  height: 32px;
  padding: 0;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 4px;
  color: #dc2626;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-eliminar:hover {
  background: #fecaca;
}

/* ==================== RESUMEN ==================== */
.salidas-resumen {
  position: sticky;
  top: 2rem;
  height: fit-content;
}

.resumen-card {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.resumen-card h3 {
  margin: 0 0 1.5rem 0;
  font-size: 1rem;
  color: var(--color-text);
}

.resumen-tipo,
.resumen-fecha,
.resumen-sucursal,
.resumen-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
}

.label {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
}

.badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  background: rgba(245, 158, 11, 0.1);
  color: var(--color-warning);
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
}

.valor {
  font-weight: 600;
  color: var(--color-text);
}

.resumen-divider {
  height: 1px;
  background: var(--color-border);
  margin: 1rem 0;
}

.resumen-item.total {
  font-size: 1.125rem;
  color: var(--color-success);
  padding-top: 1rem;
  padding-bottom: 1rem;
  border-top: 2px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
}

.resumen-acciones {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-cancelar,
.btn-registrar {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancelar {
  background: #f0f0f0;
  color: var(--color-text);
  border: 1px solid var(--color-border);
}

.btn-cancelar:hover {
  background: var(--color-border);
}

.btn-registrar {
  background: var(--color-warning);
  color: white;
}

.btn-registrar:hover:not(:disabled) {
  background: #d97706;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.btn-registrar:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 1024px) {
  .salidas-form-container {
    grid-template-columns: 1fr;
  }

  .salidas-resumen {
    position: relative;
    top: 0;
  }
}

@media (max-width: 768px) {
  .salidas-container {
    padding: 1rem;
  }

  .salidas-header h1 {
    font-size: 1.5rem;
  }

  .tipo-salida-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .salidas-form {
    padding: 1rem;
  }

  .producto-item {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}
</style>
