<template>
  <div class="entradas-container">
    <div class="entradas-header">
      <router-link to="/app/inventario" class="btn-volver">← Volver</router-link>
      <h1>Entradas de Inventario</h1>
      <p>Registra productos que ingresan al almacén</p>
    </div>

    <div class="entradas-content">
      <!-- SELECTOR DE TIPO -->
      <div class="tipo-entrada-section">
        <div class="tipo-entrada-grid">
          <div
            v-for="tipo in tiposEntrada"
            :key="tipo.valor"
            @click="tipoEntrada = tipo.valor"
            :class="['tipo-card', { activo: tipoEntrada === tipo.valor }]"
          >
            <span class="tipo-icono">{{ tipo.icono }}</span>
            <span class="tipo-label">{{ tipo.label }}</span>
          </div>
        </div>
      </div>

      <div class="entradas-form-container">
        <!-- LADO IZQUIERDO -->
        <div class="entradas-form">
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

            <!-- Sucursal Origen (solo Transferencia) -->
            <div v-if="tipoEntrada === 'Transferencia'" class="form-group">
              <label>Sucursal Origen *</label>
              <select v-model="sucursalOrigen" class="input-text">
                <option :value="null" disabled>Selecciona una sucursal...</option>
                <option
                  v-for="s in sucursalesDisponibles"
                  :key="s.SucursalId"
                  :value="s.SucursalId"
                >{{ s.NombreSucursal }}</option>
              </select>
            </div>

            <!-- Motivo (Devolución / Ajuste) -->
            <div v-if="['Devolución', 'Ajuste'].includes(tipoEntrada)" class="form-group">
              <label>Motivo *</label>
              <input
                v-model="motivo"
                type="text"
                placeholder="Ej: Producto defectuoso, error de conteo..."
                class="input-text"
              />
            </div>

            <!-- Referencia -->
            <div class="form-group">
              <label>Referencia / Documento</label>
              <input
                v-model="referencia"
                type="text"
                :placeholder="`Ej: ${tipoEntrada === 'Compra' ? 'Orden de compra' : 'Comprobante'}...`"
                class="input-text"
              />
            </div>

            <!-- Notas -->
            <div class="form-group">
              <label>Notas Adicionales</label>
              <textarea
                v-model="notas"
                placeholder="Observaciones..."
                class="input-textarea"
                rows="3"
              ></textarea>
            </div>

            <!-- ── CHECKBOX FACTURA (solo Compra) ── -->
            <div v-if="tipoEntrada === 'Compra'" class="form-group">
              <div class="checkbox-container">
                <input type="checkbox" id="chk-facturado" v-model="facturado" />
                <label for="chk-facturado" class="checkbox-label">¿Con factura?</label>
              </div>
            </div>

            <!-- ── DATOS DE FACTURA ── -->
            <div v-if="tipoEntrada === 'Compra' && facturado" class="factura-section">
              <h4 class="factura-titulo">🧾 Datos de Factura</h4>

              <!-- Buscar proveedor -->
              <div class="form-group">
                <label>Proveedor *</label>
                <div class="input-busqueda-wrapper">
                  <input
                    v-model="proveedorBuscado"
                    @input="buscarProveedor(proveedorBuscado)"
                    type="text"
                    placeholder="Buscar proveedor por nombre..."
                    class="input-busqueda"
                  />
                  <span class="icono">🔍</span>
                  <div v-if="proveedoresDisponibles.length > 0" class="dropdown-productos">
                    <div
                      v-for="p in proveedoresDisponibles"
                      :key="p.ProveedorId"
                      @click="seleccionarProveedor(p)"
                      class="dropdown-item"
                    >
                      <div class="item-nombre">{{ p.NombreEmpresa }}</div>
                      <div class="item-sku">{{ p.Nombre }} {{ p.Apellidos }} · {{ p.Telefono }}</div>
                    </div>
                  </div>
                </div>
                <button
                  @click="mostrarModalProveedor = true"
                  type="button"
                  class="btn-nuevo-proveedor"
                >+ Nuevo proveedor</button>
              </div>

              <!-- Proveedor seleccionado -->
              <div v-if="proveedorSeleccionado" class="proveedor-badge">
                ✓ {{ proveedorSeleccionado.NombreEmpresa }}
                <button @click="proveedorSeleccionado = null; proveedorBuscado = ''" class="btn-quitar">✕</button>
              </div>

              <!-- RFC -->
              <div class="form-group">
                <label>RFC *</label>
                <input v-model="rfcFactura" type="text" placeholder="RFC del proveedor..." class="input-text" />
              </div>

              <!-- Fecha Factura -->
              <div class="form-group">
                <label>Fecha Factura *</label>
                <input v-model="fechaFactura" type="date" class="input-text" />
              </div>

              <!-- Total calculado -->
              <div class="factura-total">
                <span>Total Factura:</span>
                <span class="factura-total-valor">${{ totalFactura }}</span>
              </div>
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
                <!-- Fila principal -->
                <div class="producto-main-row">
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
                    <span class="cantidad-label">uds</span>
                  </div>
                  <div class="producto-precio">
                    ${{ ((producto.PrecioCompra || 0) * producto.cantidad).toFixed(2) }}
                  </div>
                  <button @click="eliminarProducto(producto.ProductoId)" class="btn-eliminar" type="button">✕</button>
                </div>

                <!-- Fila factura: Lote y Fecha Vencimiento -->
                <div v-if="facturado && tipoEntrada === 'Compra'" class="producto-factura-row">
                  <div class="factura-field">
                    <label>Lote</label>
                    <input v-model="producto.lote" type="text" placeholder="Lote (opcional)" class="input-small" />
                  </div>
                  <div class="factura-field">
                    <label>Vencimiento</label>
                    <input v-model="producto.fechaVencimiento" type="date" class="input-small" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- LADO DERECHO: RESUMEN -->
        <div class="entradas-resumen">
          <div class="resumen-card">
            <h3>Resumen del Movimiento</h3>
            <div class="resumen-tipo">
              <span class="label">Tipo</span>
              <span class="valor badge">{{ tipoEntrada }}</span>
            </div>
            <div class="resumen-fecha">
              <span class="label">Fecha</span>
              <span class="valor">{{ fechaActual }}</span>
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
            <div class="resumen-acciones">
              <button @click="limpiarFormulario" type="button" class="btn-cancelar">Cancelar</button>
              <button
                @click="registrarEntrada"
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

    <!-- ── MODAL NUEVO PROVEEDOR ── -->
    <div v-if="mostrarModalProveedor" class="modal-overlay" @click.self="mostrarModalProveedor = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Nuevo Proveedor</h3>
          <button @click="mostrarModalProveedor = false" class="btn-cerrar-modal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre Empresa *</label>
            <input v-model="nuevoProveedor.NombreEmpresa" type="text" class="input-text" placeholder="Nombre de la empresa" />
          </div>
          <div class="form-group">
            <label>Nombre</label>
            <input v-model="nuevoProveedor.Nombre" type="text" class="input-text" placeholder="Nombre del contacto" />
          </div>
          <div class="form-group">
            <label>Apellidos</label>
            <input v-model="nuevoProveedor.Apellidos" type="text" class="input-text" placeholder="Apellidos" />
          </div>
          <div class="form-group">
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
  sucursalOrigen, cargando, tiposEntrada, resumen, formularioValido,
  sucursalesDisponibles, totalFactura,
  facturado, proveedorBuscado, proveedoresDisponibles,
  proveedorSeleccionado, mostrarModalProveedor, nuevoProveedor,
  rfcFactura, fechaFactura,
  obtenerProductosSucursal, buscarProducto, agregarProducto,
  eliminarProducto, actualizarCantidad, registrarEntrada, limpiarFormulario,
  buscarProveedor, seleccionarProveedor, registrarNuevoProveedor
} = useEntradas();

const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-MX', {
    year: 'numeric', month: '2-digit', day: '2-digit'
  });
});

onMounted(() => {
  obtenerProductosSucursal();
});
</script>

<style scoped>
:root {
  --color-bg: #f5f5f5;
  --color-bg-card: #ffffff;
  --color-border: #e0e0e0;
  --color-text: #333333;
  --color-text-secondary: #666666;
  --color-success: #10b981;
  --color-primary: #0066cc;
}

.entradas-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

.entradas-header { margin-bottom: 2rem; }

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
.btn-volver:hover { background: var(--color-border); }

.entradas-header h1 { margin: 0 0 0.5rem 0; font-size: 2rem; }
.entradas-header p  { margin: 0; color: var(--color-text-secondary); }

/* TIPOS */
.tipo-entrada-section { margin-bottom: 2rem; }
.tipo-entrada-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}
.tipo-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  background: var(--color-bg-card);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}
.tipo-card:hover  { border-color: var(--color-primary); box-shadow: 0 4px 12px rgba(0,102,204,0.1); }
.tipo-card.activo { border-color: var(--color-primary); background: rgba(0,102,204,0.05); box-shadow: 0 4px 12px rgba(0,102,204,0.2); }
.tipo-icono { font-size: 1.5rem; }
.tipo-label { font-weight: 600; font-size: 0.875rem; text-align: center; }

/* LAYOUT */
.entradas-form-container {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 2rem;
}
.entradas-form {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  padding: 2rem;
}
.form-section { margin-bottom: 2rem; }
.form-section:last-child { margin-bottom: 0; }
.form-section h3 { margin: 0 0 1.5rem 0; font-size: 1rem; font-weight: 600; }
.form-group { margin-bottom: 1.5rem; }
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.input-text, .input-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.input-text:focus, .input-textarea:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
.input-textarea { resize: vertical; min-height: 80px; }

/* BÚSQUEDA */
.input-busqueda-wrapper { position: relative; }
.input-busqueda {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.input-busqueda:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
.input-busqueda-wrapper .icono {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}
.dropdown-productos {
  position: absolute;
  top: 100%;
  left: 0; right: 0;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-top: none;
  border-radius: 0 0 6px 6px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.dropdown-item {
  padding: 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid var(--color-border);
  transition: background 0.2s;
}
.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: #f5f5f5; }
.item-nombre { font-weight: 500; font-size: 0.875rem; }
.item-sku    { font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 0.25rem; }

/* CHECKBOX */
.checkbox-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.checkbox-container input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: var(--color-primary);
}
.checkbox-label {
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  margin-bottom: 0;
}

/* SECCIÓN FACTURA */
.factura-section {
  background: #f0f6ff;
  border: 1px solid #c3d9f5;
  border-radius: 8px;
  padding: 1.25rem;
  margin-top: 0.5rem;
}
.factura-titulo {
  margin: 0 0 1.25rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-primary);
}
.factura-section .form-group { margin-bottom: 1rem; }

.btn-nuevo-proveedor {
  margin-top: 0.5rem;
  padding: 0.4rem 0.875rem;
  background: transparent;
  border: 1px dashed var(--color-primary);
  border-radius: 6px;
  color: var(--color-primary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-nuevo-proveedor:hover { background: rgba(0,102,204,0.06); }

.proveedor-badge {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0.75rem;
  background: #d1fae5;
  border: 1px solid #6ee7b7;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #065f46;
  margin-bottom: 1rem;
}
.btn-quitar {
  background: none;
  border: none;
  color: #065f46;
  cursor: pointer;
  font-size: 0.8rem;
  padding: 0 0.25rem;
}

.factura-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 6px;
  border: 1px solid #c3d9f5;
  font-weight: 600;
  font-size: 0.9rem;
}
.factura-total-valor { color: var(--color-primary); font-size: 1.1rem; }

/* PRODUCTOS */
.sin-productos {
  text-align: center;
  padding: 2rem;
  color: var(--color-text-secondary);
  background: #fafafa;
  border-radius: 6px;
}
.productos-lista { display: flex; flex-direction: column; gap: 1rem; }

.producto-item {
  background: #fafafa;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  overflow: hidden;
  transition: all 0.2s;
}
.producto-item:hover { background: #f0f0f0; }

.producto-main-row {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  gap: 1rem;
  align-items: center;
  padding: 1rem;
}
.producto-info { min-width: 0; }
.producto-nombre { font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.producto-sku    { font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 0.25rem; }

.producto-cantidad { display: flex; align-items: center; gap: 0.5rem; }
.input-cantidad {
  width: 70px;
  padding: 0.5rem;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-size: 0.875rem;
  text-align: center;
}
.cantidad-label { font-size: 0.75rem; color: var(--color-text-secondary); }
.producto-precio { font-weight: 600; color: var(--color-success); min-width: 80px; text-align: right; }
.btn-eliminar {
  width: 32px; height: 32px; padding: 0;
  background: #fee2e2; border: 1px solid #fecaca;
  border-radius: 4px; color: #dc2626;
  cursor: pointer; font-weight: 600;
  transition: all 0.2s;
}
.btn-eliminar:hover { background: #fecaca; }

/* FILA FACTURA EN PRODUCTO */
.producto-factura-row {
  display: flex;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: #f0f6ff;
  border-top: 1px solid #c3d9f5;
}
.factura-field { display: flex; flex-direction: column; gap: 0.25rem; flex: 1; }
.factura-field label { font-size: 0.75rem; font-weight: 500; color: var(--color-primary); }
.input-small {
  padding: 0.4rem 0.6rem;
  border: 1px solid #c3d9f5;
  border-radius: 4px;
  font-size: 0.8rem;
  width: 100%;
  box-sizing: border-box;
}
.input-small:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(0,102,204,0.1);
}

/* RESUMEN */
.entradas-resumen { position: sticky; top: 2rem; height: fit-content; }
.resumen-card {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.resumen-card h3 { margin: 0 0 1.5rem 0; font-size: 1rem; }
.resumen-tipo, .resumen-fecha, .resumen-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
}
.badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  background: rgba(0,102,204,0.1);
  color: var(--color-primary);
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
}
.valor { font-weight: 600; color: var(--color-text); }
.resumen-divider { height: 1px; background: var(--color-border); margin: 1rem 0; }
.resumen-item.total {
  font-size: 1.125rem;
  color: var(--color-success);
  padding: 1rem 0;
  border-top: 2px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
}
.resumen-acciones { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
.btn-cancelar, .btn-registrar {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}
.btn-cancelar { background: #f0f0f0; color: var(--color-text); border: 1px solid var(--color-border); }
.btn-cancelar:hover { background: var(--color-border); }
.btn-registrar { background: var(--color-success); color: white; }
.btn-registrar:hover:not(:disabled) { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.2); }
.btn-registrar:disabled { opacity: 0.5; cursor: not-allowed; }

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  border-radius: 10px;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
}
.modal-header h3 { margin: 0; font-size: 1rem; }
.btn-cerrar-modal {
  background: none; border: none;
  font-size: 1.1rem; cursor: pointer;
  color: var(--color-text-secondary);
  transition: color 0.2s;
}
.btn-cerrar-modal:hover { color: var(--color-text); }
.modal-body { padding: 1.5rem; }
.modal-body .form-group { margin-bottom: 1rem; }
.modal-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border);
  background: #fafafa;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .entradas-form-container { grid-template-columns: 1fr; }
  .entradas-resumen { position: relative; top: 0; }
}
@media (max-width: 768px) {
  .entradas-container { padding: 1rem; }
  .entradas-header h1 { font-size: 1.5rem; }
  .tipo-entrada-grid { grid-template-columns: repeat(2, 1fr); }
  .entradas-form { padding: 1rem; }
  .producto-main-row { grid-template-columns: 1fr; }
  .producto-factura-row { flex-direction: column; }
}
</style>