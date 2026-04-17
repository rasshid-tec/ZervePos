<template>
  <div class="salidas-container">

    <div class="page-header">
      <button class="btn-back" @click="$router.push('/app/inventario')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div>
        <h1 class="page-titulo">Salidas de Inventario</h1>
        <p class="page-subtitulo">Registra productos que salen del almacén</p>
      </div>
    </div>

    <!-- Tipo de salida -->
    <div class="tipos-grid">
      <div
        v-for="tipo in tiposSalida"
        :key="tipo.valor"
        :class="['tipo-card', { activo: tipoSalida === tipo.valor }]"
        @click="tipoSalida = tipo.valor"
      >
        <span class="tipo-icono" v-html="tipo.icon"></span>
        <span class="tipo-label">{{ tipo.label }}</span>
      </div>
    </div>

    <div class="layout">

      <!-- Formulario izquierdo -->
      <div class="form-card">

        <div class="form-section">
          <h3 class="section-titulo">Información del Movimiento</h3>

          <!-- Búsqueda producto -->
          <div class="campo">
            <label>Producto *</label>
            <div class="input-search-wrap">
              <svg class="search-icono" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input
                v-model="productoBuscado"
                @input="buscarProducto(productoBuscado)"
                type="text"
                placeholder="Buscar por nombre o SKU..."
                class="input-text input-search"
              />
              <div v-if="productosDisponibles.length > 0" class="dropdown">
                <div
                  v-for="p in productosDisponibles"
                  :key="p.ProductoId"
                  @click="agregarProducto(p)"
                  class="dropdown-item"
                >
                  <span class="item-nombre">{{ p.NombreProducto }}</span>
                  <span class="item-sku">SKU: {{ p.ProductoId }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Sucursal destino (solo Transferencia) -->
          <div v-if="tipoSalida === 'Transferencia'" class="campo">
            <label>Sucursal Destino *</label>
            <select v-model="sucursalDestino" class="input-text input-select">
              <option :value="null" disabled>Selecciona una sucursal...</option>
              <option
                v-for="s in sucursalesDisponibles"
                :key="s.SucursalId"
                :value="s.SucursalId"
              >{{ s.NombreSucursal }}</option>
            </select>
          </div>

          <!-- Motivo (Merma y Ajuste) -->
          <div v-if="['Merma', 'Ajuste'].includes(tipoSalida)" class="campo">
            <label>Motivo *</label>
            <textarea
              v-model="motivo"
              :placeholder="tipoSalida === 'Merma' ? 'Ej: Producto dañado, vencido...' : 'Ej: Corrección de inventario...'"
              class="input-textarea"
              rows="3"
            ></textarea>
          </div>

          <!-- Referencia -->
          <div class="campo">
            <label>Referencia / Documento</label>
            <input v-model="referencia" type="text" placeholder="Ej: Comprobante, Nota..." class="input-text" />
          </div>

          <!-- Notas -->
          <div class="campo">
            <label>Notas adicionales</label>
            <textarea v-model="notas" placeholder="Observaciones..." class="input-textarea" rows="3"></textarea>
          </div>
        </div>

        <!-- Productos agregados -->
        <div class="form-section">
          <h3 class="section-titulo">Productos Agregados</h3>

          <div v-if="productosSeleccionados.length === 0" class="empty-productos">
            <p>No hay productos agregados aún</p>
          </div>

          <div v-else class="productos-lista">
            <div v-for="p in productosSeleccionados" :key="p.ProductoId" class="producto-item">
              <div class="producto-info">
                <span class="producto-nombre">{{ p.NombreProducto }}</span>
                <span class="producto-sku">SKU: {{ p.ProductoId }}</span>
              </div>
              <div class="producto-cantidad">
                <input
                  :value="p.cantidad"
                  @input="actualizarCantidad(p.ProductoId, $event.target.value)"
                  type="number" min="1" class="input-cantidad"
                />
                <span class="uds">uds</span>
              </div>
              <span class="producto-precio">${{ ((p.PrecioCompra || 0) * p.cantidad).toFixed(2) }}</span>
              <button @click="eliminarProducto(p.ProductoId)" class="btn-eliminar" type="button">✕</button>
            </div>
          </div>
        </div>

      </div>

      <!-- Resumen derecho -->
      <div class="resumen-card">
        <h3 class="resumen-titulo">Resumen</h3>

        <div class="resumen-row">
          <span>Tipo</span>
          <span class="badge-tipo">{{ tipoSalida }}</span>
        </div>
        <div class="resumen-row">
          <span>Fecha</span>
          <strong>{{ fechaActual }}</strong>
        </div>
        <div v-if="tipoSalida === 'Transferencia' && sucursalDestino" class="resumen-row">
          <span>Destino</span>
          <strong>{{ sucursalDestinoNombre }}</strong>
        </div>

        <div class="resumen-divider"></div>

        <div class="resumen-row">
          <span>Total productos</span>
          <strong>{{ resumen.totalProductos }}</strong>
        </div>
        <div class="resumen-row">
          <span>Total cantidad</span>
          <strong>{{ resumen.totalCantidad }} uds</strong>
        </div>
        <div class="resumen-row total">
          <span>Valor total</span>
          <strong>${{ resumen.totalValor }}</strong>
        </div>

        <div class="resumen-divider"></div>

        <div class="resumen-acciones">
          <button @click="limpiarFormulario" type="button" class="btn-cancelar">Cancelar</button>
          <button
            @click="registrarSalida"
            :disabled="!formularioValido || cargando"
            type="button"
            class="btn-registrar"
          >
            <span v-if="!cargando">Registrar</span>
            <span v-else>Registrando...</span>
          </button>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useSalidas } from '../composables/useSalidas';

const {
  tipoSalida, productoBuscado, productosDisponibles,
  productosSeleccionados, motivo, referencia, notas,
  sucursalDestino, sucursalesDisponibles, cargando,
  tiposSalida, resumen, formularioValido,
  obtenerProductosSucursal, buscarProducto, agregarProducto,
  eliminarProducto, actualizarCantidad, registrarSalida, limpiarFormulario
} = useSalidas();

const fechaActual = computed(() =>
  new Date().toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' })
);

const sucursalDestinoNombre = computed(() => {
  const s = sucursalesDisponibles.value.find(s => s.SucursalId === sucursalDestino.value);
  return s?.NombreSucursal || '';
});

onMounted(() => obtenerProductosSucursal());
</script>

<style scoped>
.salidas-container { padding: 32px; max-width: 1100px; }

.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
.btn-back {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  width: 36px; height: 36px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; color: #1e293b; flex-shrink: 0;
}
.btn-back:hover { background: #f1f5f9; }
.page-titulo    { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 13px; }

/* Tipos */
.tipos-grid { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.tipo-card {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 16px 28px; background: #fff;
  border: 2px solid #e2e8f0; border-radius: 12px;
  cursor: pointer; transition: all 0.2s; color: #64748b;
}
.tipo-card:hover  { border-color: #f97316; color: #f97316; }
.tipo-card.activo { border-color: #f97316; background: #fff7ed; color: #f97316; }
.tipo-icono { display: flex; align-items: center; }
.tipo-label { font-size: 13px; font-weight: 700; }

/* Layout */
.layout { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }

/* Form card */
.form-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 24px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.form-section { margin-bottom: 28px; }
.form-section:last-child { margin-bottom: 0; }
.section-titulo {
  font-size: 14px; font-weight: 700; color: #1e293b;
  margin: 0 0 16px; padding-bottom: 10px;
  border-bottom: 1px solid #f1f5f9;
}
.campo { margin-bottom: 16px; }
.campo label {
  display: block; font-size: 12px; font-weight: 700;
  color: #64748b; text-transform: uppercase; margin-bottom: 6px;
}
.input-text, .input-textarea {
  width: 100%; padding: 9px 12px;
  border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; outline: none; box-sizing: border-box;
  transition: border-color 0.15s; font-family: inherit;
}
.input-text:focus, .input-textarea:focus { border-color: #f97316; }
.input-textarea { resize: vertical; min-height: 80px; }
.input-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}

/* Search */
.input-search-wrap { position: relative; }
.search-icono {
  position: absolute; left: 10px; top: 50%;
  transform: translateY(-50%); color: #94a3b8; pointer-events: none;
}
.input-search { padding-left: 34px; }
.dropdown {
  position: absolute; top: 100%; left: 0; right: 0;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;
  max-height: 260px; overflow-y: auto; z-index: 100;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.dropdown-item {
  display: flex; flex-direction: column; gap: 2px;
  padding: 10px 12px; cursor: pointer;
  border-bottom: 1px solid #f1f5f9; transition: background 0.15s;
}
.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: #f8fafc; }
.item-nombre { font-size: 13px; font-weight: 500; color: #1e293b; }
.item-sku    { font-size: 11px; color: #94a3b8; }

/* Productos */
.empty-productos {
  text-align: center; padding: 28px; color: #94a3b8;
  font-size: 13px; background: #f8fafc; border-radius: 8px;
}
.productos-lista { display: flex; flex-direction: column; gap: 10px; }
.producto-item {
  display: grid; grid-template-columns: 1fr auto auto auto;
  gap: 12px; align-items: center; padding: 12px 14px;
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
}
.producto-info { min-width: 0; }
.producto-nombre { display: block; font-size: 13px; font-weight: 600; color: #1e293b; }
.producto-sku    { display: block; font-size: 11px; color: #94a3b8; margin-top: 2px; }
.producto-cantidad { display: flex; align-items: center; gap: 6px; }
.input-cantidad {
  width: 64px; padding: 6px 8px; text-align: center;
  border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;
}
.uds { font-size: 11px; color: #94a3b8; }
.producto-precio { font-size: 13px; font-weight: 700; color: #16a34a; min-width: 70px; text-align: right; }
.btn-eliminar {
  width: 28px; height: 28px; padding: 0;
  background: #fef2f2; border: 1px solid #fecaca;
  border-radius: 6px; color: #dc2626; cursor: pointer; font-size: 12px;
}
.btn-eliminar:hover { background: #fecaca; }

/* Resumen */
.resumen-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  position: sticky; top: 24px;
}
.resumen-titulo { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 16px; }
.resumen-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 0; font-size: 13px; color: #64748b;
}
.resumen-row strong { color: #1e293b; }
.resumen-row.total {
  font-size: 15px; font-weight: 700; color: #1e293b;
  padding: 12px 0; border-top: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0;
}
.badge-tipo {
  background: #fff7ed; color: #f97316;
  font-size: 11px; font-weight: 700;
  padding: 3px 10px; border-radius: 999px; text-transform: uppercase;
}
.resumen-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }
.resumen-acciones { display: flex; gap: 8px; margin-top: 16px; }
.btn-cancelar, .btn-registrar {
  flex: 1; padding: 10px; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s;
}
.btn-cancelar { background: #f1f5f9; color: #1e293b; }
.btn-cancelar:hover { background: #e2e8f0; }
.btn-registrar { background: #f97316; color: #fff; }
.btn-registrar:hover:not(:disabled) { background: #ea580c; }
.btn-registrar:disabled { background: #94a3b8; cursor: not-allowed; }

@media (max-width: 1024px) {
  .layout { grid-template-columns: 1fr; }
  .resumen-card { position: relative; top: 0; }
}
@media (max-width: 768px) {
  .salidas-container { padding: 16px; }
  .producto-item { grid-template-columns: 1fr; gap: 8px; }
}
</style>