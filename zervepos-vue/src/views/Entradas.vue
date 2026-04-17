<template>
  <div class="entradas-container">

    <div class="page-header">
      <button class="btn-back" @click="$router.push('/app/inventario')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div>
        <h1 class="page-titulo">Entradas de Inventario</h1>
        <p class="page-subtitulo">Registra productos que ingresan al almacén</p>
      </div>
    </div>

    <!-- Tipo de entrada -->
    <div class="tipos-grid">
      <div
        v-for="tipo in tiposEntrada"
        :key="tipo.valor"
        :class="['tipo-card', { activo: tipoEntrada === tipo.valor }]"
        @click="tipoEntrada = tipo.valor"
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

          <!-- Motivo (solo Ajuste) -->
          <div v-if="tipoEntrada === 'Ajuste'" class="campo">
            <label>Motivo *</label>
            <input v-model="motivo" type="text" placeholder="Ej: Error de conteo, ajuste físico..." class="input-text" />
          </div>

          <!-- Referencia -->
          <div class="campo">
            <label>Referencia / Documento</label>
            <input
              v-model="referencia"
              type="text"
              :placeholder="tipoEntrada === 'Compra' ? 'Ej: Orden de compra...' : 'Ej: Comprobante...'"
              class="input-text"
            />
          </div>

          <!-- Notas -->
          <div class="campo">
            <label>Notas adicionales</label>
            <textarea v-model="notas" placeholder="Observaciones..." class="input-textarea" rows="3"></textarea>
          </div>

          <!-- Factura (solo Compra) -->
          <div v-if="tipoEntrada === 'Compra'" class="campo">
            <label class="checkbox-label">
              <input type="checkbox" v-model="facturado" />
              ¿Con factura?
            </label>
          </div>

          <!-- Datos factura -->
          <div v-if="tipoEntrada === 'Compra' && facturado" class="factura-box">
            <h4 class="factura-titulo">Datos de Factura</h4>

            <div class="campo">
              <label>Proveedor *</label>
              <div class="input-search-wrap">
                <svg class="search-icono" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input
                  v-model="proveedorBuscado"
                  @input="buscarProveedor(proveedorBuscado)"
                  type="text"
                  placeholder="Buscar proveedor..."
                  class="input-text input-search"
                />
                <div v-if="proveedoresDisponibles.length > 0" class="dropdown">
                  <div
                    v-for="p in proveedoresDisponibles"
                    :key="p.ProveedorId"
                    @click="seleccionarProveedor(p)"
                    class="dropdown-item"
                  >
                    <span class="item-nombre">{{ p.NombreEmpresa }}</span>
                    <span class="item-sku">{{ p.Nombre }} {{ p.Apellidos }} · {{ p.Telefono }}</span>
                  </div>
                </div>
              </div>
              <button @click="mostrarModalProveedor = true" type="button" class="btn-nuevo-proveedor">
                + Nuevo proveedor
              </button>
            </div>

            <div v-if="proveedorSeleccionado" class="proveedor-badge">
              <span>✓ {{ proveedorSeleccionado.NombreEmpresa }}</span>
              <button @click="proveedorSeleccionado = null; proveedorBuscado = ''" class="btn-x">✕</button>
            </div>

            <div class="campo">
              <label>RFC *</label>
              <input v-model="rfcFactura" type="text" placeholder="RFC del proveedor..." class="input-text" />
            </div>

            <div class="campo">
              <label>Fecha Factura *</label>
              <input v-model="fechaFactura" type="date" class="input-text" />
            </div>

            <div class="factura-total">
              <span>Total Factura</span>
              <strong>${{ totalFactura }}</strong>
            </div>
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
              <div class="producto-main">
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

              <div v-if="facturado && tipoEntrada === 'Compra'" class="producto-factura">
                <div class="factura-field">
                  <label>Lote</label>
                  <input v-model="p.lote" type="text" placeholder="Opcional" class="input-sm" />
                </div>
                <div class="factura-field">
                  <label>Vencimiento</label>
                  <input v-model="p.fechaVencimiento" type="date" class="input-sm" />
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Resumen derecho -->
      <div class="resumen-card">
        <h3 class="resumen-titulo">Resumen</h3>

        <div class="resumen-row">
          <span>Tipo</span>
          <span class="badge-tipo">{{ tipoEntrada }}</span>
        </div>
        <div class="resumen-row">
          <span>Fecha</span>
          <strong>{{ fechaActual }}</strong>
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
            @click="registrarEntrada"
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

    <!-- Modal nuevo proveedor -->
    <div v-if="mostrarModalProveedor" class="modal-overlay" @click.self="mostrarModalProveedor = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Nuevo Proveedor</h3>
          <button @click="mostrarModalProveedor = false" class="btn-x">✕</button>
        </div>
        <div class="modal-body">
          <div class="campo">
            <label>Nombre Empresa *</label>
            <input v-model="nuevoProveedor.NombreEmpresa" type="text" class="input-text" placeholder="Nombre de la empresa" />
          </div>
          <div class="campo">
            <label>Nombre</label>
            <input v-model="nuevoProveedor.Nombre" type="text" class="input-text" placeholder="Nombre del contacto" />
          </div>
          <div class="campo">
            <label>Apellidos</label>
            <input v-model="nuevoProveedor.Apellidos" type="text" class="input-text" placeholder="Apellidos" />
          </div>
          <div class="campo">
            <label>Teléfono</label>
            <input v-model="nuevoProveedor.Telefono" type="text" class="input-text" placeholder="Teléfono" />
          </div>
        </div>
        <div class="modal-footer">
          <button @click="mostrarModalProveedor = false" type="button" class="btn-cancelar">Cancelar</button>
          <button @click="registrarNuevoProveedor" type="button" class="btn-registrar">Guardar</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useEntradas } from '../composables/useEntradas';

const {
  tipoEntrada, productoBuscado, productosDisponibles,
  productosSeleccionados, motivo, referencia, notas,
  cargando, resumen, formularioValido,
  totalFactura, facturado, proveedorBuscado, proveedoresDisponibles,
  proveedorSeleccionado, mostrarModalProveedor, nuevoProveedor,
  rfcFactura, fechaFactura,
  obtenerProductosSucursal, buscarProducto, agregarProducto,
  eliminarProducto, actualizarCantidad, registrarEntrada, limpiarFormulario,
  buscarProveedor, seleccionarProveedor, registrarNuevoProveedor
} = useEntradas();

const tiposEntrada = [
  {
    valor: 'Compra',
    label: 'Compra',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`,
  },
  {
    valor: 'Ajuste',
    label: 'Ajuste',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>`,
  },
];

const fechaActual = computed(() =>
  new Date().toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' })
);

onMounted(() => obtenerProductosSucursal());
</script>

<style scoped>
.entradas-container { padding: 32px; max-width: 1100px; }

.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
.btn-back {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  width: 36px; height: 36px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; color: #1e293b; flex-shrink: 0;
}
.btn-back:hover { background: #f1f5f9; }
.page-titulo   { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 13px; }

/* Tipos */
.tipos-grid {
  display: flex; gap: 12px; margin-bottom: 24px;
}
.tipo-card {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 16px 28px; background: #fff;
  border: 2px solid #e2e8f0; border-radius: 12px;
  cursor: pointer; transition: all 0.2s; color: #64748b;
}
.tipo-card:hover { border-color: #2563eb; color: #2563eb; }
.tipo-card.activo { border-color: #2563eb; background: #eff6ff; color: #2563eb; }
.tipo-icono { display: flex; align-items: center; }
.tipo-label { font-size: 13px; font-weight: 700; }

/* Layout */
.layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 24px;
  align-items: start;
}

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
  transition: border-color 0.15s;
}
.input-text:focus, .input-textarea:focus { border-color: #2563eb; }
.input-textarea { resize: vertical; min-height: 80px; font-family: inherit; }

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

/* Checkbox */
.checkbox-label {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 600; cursor: pointer;
  text-transform: none !important; color: #1e293b !important;
}
.checkbox-label input { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }

/* Factura */
.factura-box {
  background: #eff6ff; border: 1px solid #bfdbfe;
  border-radius: 10px; padding: 16px; margin-top: 8px;
}
.factura-titulo {
  font-size: 13px; font-weight: 700; color: #2563eb; margin: 0 0 14px;
}
.factura-box .campo { margin-bottom: 12px; }
.btn-nuevo-proveedor {
  margin-top: 6px; padding: 5px 12px;
  background: transparent; border: 1px dashed #2563eb;
  border-radius: 6px; color: #2563eb; font-size: 12px;
  font-weight: 600; cursor: pointer;
}
.btn-nuevo-proveedor:hover { background: rgba(37,99,235,0.06); }
.proveedor-badge {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: 8px; font-size: 13px; font-weight: 600;
  color: #16a34a; margin-bottom: 12px;
}
.factura-total {
  display: flex; justify-content: space-between;
  padding: 10px 12px; background: #fff;
  border-radius: 8px; font-size: 14px; font-weight: 600; color: #1e293b;
}

/* Productos */
.empty-productos {
  text-align: center; padding: 28px;
  color: #94a3b8; font-size: 13px;
  background: #f8fafc; border-radius: 8px;
}
.productos-lista { display: flex; flex-direction: column; gap: 10px; }
.producto-item {
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 10px; overflow: hidden;
}
.producto-main {
  display: grid; grid-template-columns: 1fr auto auto auto;
  gap: 12px; align-items: center; padding: 12px 14px;
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
.producto-factura {
  display: flex; gap: 12px; padding: 10px 14px;
  background: #eff6ff; border-top: 1px solid #bfdbfe;
}
.factura-field { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.factura-field label { font-size: 11px; font-weight: 700; color: #2563eb; }
.input-sm {
  padding: 5px 8px; border: 1px solid #bfdbfe;
  border-radius: 6px; font-size: 12px; width: 100%; box-sizing: border-box;
}
.input-sm:focus { outline: none; border-color: #2563eb; }

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
  font-size: 15px; font-weight: 700;
  color: #1e293b; padding: 12px 0;
  border-top: 2px solid #e2e8f0;
  border-bottom: 2px solid #e2e8f0;
}
.badge-tipo {
  background: #eff6ff; color: #2563eb;
  font-size: 11px; font-weight: 700;
  padding: 3px 10px; border-radius: 999px;
  text-transform: uppercase;
}
.resumen-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }
.resumen-acciones { display: flex; gap: 8px; margin-top: 16px; }
.btn-cancelar, .btn-registrar {
  flex: 1; padding: 10px; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s;
}
.btn-cancelar { background: #f1f5f9; color: #1e293b; }
.btn-cancelar:hover { background: #e2e8f0; }
.btn-registrar { background: #16a34a; color: #fff; }
.btn-registrar:hover:not(:disabled) { background: #15803d; }
.btn-registrar:disabled { background: #94a3b8; cursor: not-allowed; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal {
  background: #fff; border-radius: 12px;
  width: 100%; max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 20px; border-bottom: 1px solid #e2e8f0;
}
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
.btn-x { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 16px; }
.btn-x:hover { color: #1e293b; }
.modal-body { padding: 20px; }
.modal-body .campo { margin-bottom: 14px; }
.modal-footer {
  display: flex; gap: 8px; padding: 14px 20px;
  border-top: 1px solid #e2e8f0; background: #f8fafc;
}

@media (max-width: 1024px) {
  .layout { grid-template-columns: 1fr; }
  .resumen-card { position: relative; top: 0; }
}
@media (max-width: 768px) {
  .entradas-container { padding: 16px; }
  .tipos-grid { flex-wrap: wrap; }
  .producto-main { grid-template-columns: 1fr; }
}
</style>