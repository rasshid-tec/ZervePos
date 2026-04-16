<template>
  <div class="editar-producto">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.back()">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/productos" class="breadcrumb__link">Productos</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Editar Producto</span>
          </span>
          <h1 class="page-title">Editar Producto</h1>
        </div>
      </div>
    </div>

    <!-- Buscador -->
    <div class="card buscador-card">
      <div class="card__header">
        <div class="card__icon card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div>
          <h2 class="card__title">Buscar Producto</h2>
          <p class="card__subtitle">Por nombre, código de barras o marca</p>
        </div>
      </div>
      <div class="card__body buscador-body">
        <div class="buscador-wrap">
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              v-model="busqueda"
              type="text"
              class="input"
              placeholder="Buscar por nombre o marca..."
              @input="onBusqueda"
              @keydown.enter="buscarPorSKU"
              autocomplete="off"
            />
          </div>
          <Transition name="dropdown">
            <div v-if="sugerencias.length > 0" class="dropdown">
              <div
                v-for="p in sugerencias"
                :key="p.ProductoId"
                class="dropdown__item"
                @click="seleccionarProducto(p)"
              >
                <div class="dropdown__item-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <div class="dropdown__item-info">
                  <span class="dropdown__item-nombre">{{ p.NombreProducto }}</span>
                  <span class="dropdown__item-sub">{{ p.Marca }} · {{ p.CodigoBarras }}</span>
                </div>
                <span class="dropdown__item-cat">{{ p.NombreCategoria || '—' }}</span>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- Sin producto seleccionado -->
    <div v-if="!productoSeleccionado" class="empty-state">
      <div class="empty-state__icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><circle cx="15" cy="15" r="4" fill="none"/><line x1="18" y1="18" x2="20" y2="20"/></svg>
      </div>
      <h3 class="empty-state__titulo">Ningún producto seleccionado</h3>
      <p class="empty-state__desc">Usa el buscador de arriba para encontrar y seleccionar el producto que deseas editar.</p>
    </div>

    <!-- Formulario de edición -->
    <div v-else class="form-layout">
      <div class="form-col">

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>
            </div>
            <div>
              <h2 class="card__title">Información General</h2>
              <p class="card__subtitle">Datos básicos de identificación del producto</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field">
              <label class="field__label">NOMBRE DEL PRODUCTO <span class="required">*</span></label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>
                <input v-model="form.nombreProducto" type="text" class="input" />
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">MARCA <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                  <input v-model="form.marca" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">TAMAÑO / CONTENIDO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/></svg>
                  <input v-model="form.tamanio" type="text" class="input" />
                </div>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">CÓDIGO DE BARRAS <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="2" height="16"/><rect x="7" y="4" width="1" height="16"/><rect x="10" y="4" width="2" height="16"/><rect x="14" y="4" width="1" height="16"/><rect x="17" y="4" width="2" height="16"/></svg>
                  <input v-model="form.codigoBarras" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">SKU</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/></svg>
                  <input v-model="form.sku" type="text" class="input" />
                </div>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">CATEGORÍA</label>
                <div class="select-add-wrap">
                  <div class="input-wrap input-wrap--grow">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <select v-model="form.categoriaId" class="input input--select">
                      <option value="">Selecciona...</option>
                      <option v-for="cat in categorias" :key="cat.CategoriaId" :value="cat.CategoriaId">
                        {{ cat.NombreCategoria }}
                      </option>
                    </select>
                  </div>
                  <button class="btn-add" @click="abrirModalCategoria" title="Nueva categoría">+</button>
                </div>
              </div>
              <div class="field">
                <label class="field__label">PRESENTACIÓN <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/></svg>
                  <input v-model="form.presentacion" type="text" class="input" placeholder="Ej. Caja, Rollo, Pieza..." />
                </div>
              </div>
            </div>
            <div class="field field--half">
              <label class="field__label">UNIDADES POR VENTA</label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/></svg>
                <input v-model.number="form.unidadesPorVenta" type="number" min="1" class="input" />
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--dark">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <h2 class="card__title">Costos y Stock</h2>
              <p class="card__subtitle">Precio de compra y niveles mínimos de inventario</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field-row">
              <div class="field">
                <label class="field__label">PRECIO DE COMPRA <span class="required">*</span></label>
                <div class="input-wrap">
                  <span class="input-prefix">$</span>
                  <input v-model.number="form.precioCompra" type="number" min="0" step="0.01" class="input input--prefix" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">STOCK MÍNIMO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                  <input v-model.number="form.stockMinimo" type="number" min="0" class="input" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--orange">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            </div>
            <div>
              <h2 class="card__title">Porcentajes de Precio de Venta</h2>
              <p class="card__subtitle">Configura el margen de ganancia por tipo de cliente</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field-row">
              <div class="field">
                <label class="field__label">PORCENTAJE — MAYOREO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
                  <input v-model.number="form.pctMayoreo" type="number" min="0" step="0.01" class="input input--suffix" />
                  <span class="input-suffix">%</span>
                </div>
              </div>
              <div class="field">
                <label class="field__label">PORCENTAJE — MEDIO MAYOREO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                  <input v-model.number="form.pctMedioMayoreo" type="number" min="0" step="0.01" class="input input--suffix" />
                  <span class="input-suffix">%</span>
                </div>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">PORCENTAJE — MENUDEO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>
                  <input v-model.number="form.pctMenudeo" type="number" min="0" step="0.01" class="input input--suffix" />
                  <span class="input-suffix">%</span>
                </div>
              </div>
              <div class="field">
                <label class="field__label">PORCENTAJE — PÚBLICO GENERAL</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                  <input v-model.number="form.pctPublicoGeneral" type="number" min="0" step="0.01" class="input input--suffix" />
                  <span class="input-suffix">%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn--secondary" @click="cancelarEdicion">Cancelar</button>
          <button class="btn btn--primary" @click="guardarCambios" :disabled="guardando">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>

      </div>

      <!-- Columna derecha -->
      <div class="summary-col">
        <div class="card card--sticky">
          <div class="card__header">
            <div class="card__icon card__icon--dark">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <h2 class="card__title">Vista Previa de Precios</h2>
              <p class="card__subtitle">Calculado sobre el precio de compra</p>
            </div>
          </div>
          <div class="card__body">
            <div class="price-item price-item--mayoreo">
              <div class="price-item__header"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg><span>MAYOREO</span></div>
              <div class="price-item__detail"><span class="price-item__pct">+{{ form.pctMayoreo || 0 }}%</span><span class="price-item__value">${{ precioMayoreo }}</span></div>
            </div>
            <div class="price-item price-item--medio">
              <div class="price-item__header"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><span>MEDIO MAYOREO</span></div>
              <div class="price-item__detail"><span class="price-item__pct">+{{ form.pctMedioMayoreo || 0 }}%</span><span class="price-item__value">${{ precioMedioMayoreo }}</span></div>
            </div>
            <div class="price-item price-item--menudeo">
              <div class="price-item__header"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg><span>MENUDEO</span></div>
              <div class="price-item__detail"><span class="price-item__pct">+{{ form.pctMenudeo || 0 }}%</span><span class="price-item__value">${{ precioMenudeo }}</span></div>
            </div>
            <div class="price-item price-item--publico">
              <div class="price-item__header"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>PÚBLICO GENERAL</span></div>
              <div class="price-item__detail"><span class="price-item__pct">+{{ form.pctPublicoGeneral || 0 }}%</span><span class="price-item__value">${{ precioPublicoGeneral }}</span></div>
            </div>
          </div>
        </div>

        <div class="card card--sticky" style="margin-top:1rem">
          <div class="card__header">
            <div class="card__icon card__icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
            </div>
            <div><h2 class="card__title">Resumen del Producto</h2></div>
          </div>
          <div class="card__body">
            <div class="summary-row"><span class="summary-row__label">Nombre</span><span class="summary-row__value">{{ form.nombreProducto || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Marca</span><span class="summary-row__value">{{ form.marca || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Tamaño</span><span class="summary-row__value">{{ form.tamanio || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Código</span><span class="summary-row__value">{{ form.codigoBarras || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">SKU</span><span class="summary-row__value">{{ form.sku || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Presentación</span><span class="summary-row__value">{{ form.presentacion || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Uds/Venta</span><span class="summary-row__value">{{ form.unidadesPorVenta ? form.unidadesPorVenta + ' uds' : '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Stock Mín.</span><span class="summary-row__value">{{ form.stockMinimo ? form.stockMinimo + ' uds' : '—' }}</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Nueva Categoría -->
    <div v-if="modalCategoria" class="modal-overlay" @click.self="cerrarModalCategoria">
      <div class="modal">
        <div class="modal__header">
          <h3 class="modal__title">Nueva Categoría</h3>
          <button class="modal__close" @click="cerrarModalCategoria">✕</button>
        </div>
        <div class="modal__body">
          <div class="field">
            <label class="field__label">NOMBRE DE LA CATEGORÍA <span class="required">*</span></label>
            <div class="input-wrap">
              <input v-model="nuevaCategoria" type="text" class="input" placeholder="Ej. Bebidas, Abarrotes..." @keyup.enter="guardarCategoria" />
            </div>
          </div>
        </div>
        <div class="modal__footer">
          <button class="btn btn--secondary" @click="cerrarModalCategoria">Cancelar</button>
          <button class="btn btn--primary" @click="guardarCategoria" :disabled="guardandoCategoria">
            {{ guardandoCategoria ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmacionPeligrosa ref="confirmacion" />
    <AlertaToast ref="alerta" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AlertaToast from '../../components/AlertaToast.vue'
import ConfirmacionPeligrosa from '../../components/ConfirmacionPeligrosa.vue'

const alerta       = ref(null)
const confirmacion = ref(null)

const busqueda           = ref('')
const sugerencias        = ref([])
const productoSeleccionado = ref(null)
const categorias         = ref([])
const modalCategoria     = ref(false)
const nuevaCategoria     = ref('')
const guardando          = ref(false)
const guardandoCategoria = ref(false)
let   busquedaTimer      = null

const form = ref({
  productoId:        null,
  nombreProducto:    '',
  marca:             '',
  tamanio:           '',
  codigoBarras:      '',
  sku:               '',
  categoriaId:       '',
  presentacion:      '',
  unidadesPorVenta:  null,
  precioCompra:      null,
  stockMinimo:       null,
  pctMayoreo:        null,
  pctMedioMayoreo:   null,
  pctMenudeo:        null,
  pctPublicoGeneral: null,
})

const calcPrecio = (pct) => {
  if (!form.value.precioCompra || !pct) return '0.00'
  return (form.value.precioCompra * (1 + pct / 100)).toFixed(2)
}
const precioMayoreo        = computed(() => calcPrecio(form.value.pctMayoreo))
const precioMedioMayoreo   = computed(() => calcPrecio(form.value.pctMedioMayoreo))
const precioMenudeo        = computed(() => calcPrecio(form.value.pctMenudeo))
const precioPublicoGeneral = computed(() => calcPrecio(form.value.pctPublicoGeneral))

// ─── Búsqueda en tiempo real ──────────────────────────────
const onBusqueda = () => {
  clearTimeout(busquedaTimer)
  if (!busqueda.value.trim()) { sugerencias.value = []; return }
  busquedaTimer = setTimeout(async () => {
    try {
      const res  = await fetch('/php/buscar_productos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ busqueda: busqueda.value.trim() })
      })
      const data = await res.json()
      if (data.status === 1) sugerencias.value = data.productos
    } catch (e) { sugerencias.value = [] }
  }, 300)
}

// ─── Búsqueda exacta por SKU al presionar Enter ───────────
const buscarPorSKU = async () => {
  if (!busqueda.value.trim()) return
  try {
    const res  = await fetch('/php/obtener_producto_sku.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sku: busqueda.value.trim() })
    })
    const data = await res.json()
    if (data.status === 1 && data.producto) {
      seleccionarProducto(data.producto)
    } else {
      alerta.value.mostrar('No se encontró ningún producto con ese SKU', 'advertencia')
    }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  }
}

// ─── Seleccionar producto ─────────────────────────────────
const seleccionarProducto = async (p) => {
  sugerencias.value      = []
  busqueda.value         = p.NombreProducto
  productoSeleccionado.value = p

  form.value = {
    productoId:       p.ProductoId,
    nombreProducto:   p.NombreProducto,
    marca:            p.Marca,
    tamanio:          p.Tamaño,
    codigoBarras:     p.CodigoBarras,
    sku:              p.SKU,
    categoriaId:      p.CategoriaId,
    presentacion:     p.Presentacion,
    unidadesPorVenta: p.UnidadesPorVenta,
    precioCompra:     p.PrecioCompra,
    stockMinimo:      p.StockMinimo,
    pctMayoreo:       null,
    pctMedioMayoreo:  null,
    pctMenudeo:       null,
    pctPublicoGeneral: null,
  }

  // Cargar porcentajes
  try {
    const res  = await fetch('/php/obtener_niveles_producto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ productoId: p.ProductoId })
    })
    const data = await res.json()
    if (data.status === 1) {
      data.niveles.forEach(n => {
        if (n.NivelId === 1) form.value.pctMayoreo        = n.PorcentajeGanancia
        if (n.NivelId === 2) form.value.pctMedioMayoreo   = n.PorcentajeGanancia
        if (n.NivelId === 3) form.value.pctMenudeo         = n.PorcentajeGanancia
        if (n.NivelId === 4) form.value.pctPublicoGeneral  = n.PorcentajeGanancia
      })
    }
  } catch (e) {
    alerta.value.mostrar('Error al cargar porcentajes', 'error')
  }
}

// ─── Guardar cambios ──────────────────────────────────────
const guardarCambios = async () => {
  if (!form.value.nombreProducto || !form.value.marca || !form.value.codigoBarras || !form.value.precioCompra || !form.value.presentacion) {
    alerta.value.mostrar('Completa los campos obligatorios', 'advertencia')
    return
  }

  const confirmado = await confirmacion.value.mostrar(
    '¿Modificar producto?',
    'Estás a punto de editar información sensible del producto. Esta acción no se puede deshacer.'
  )
  if (!confirmado) return

  guardando.value = true
  try {
    const res  = await fetch('/php/editar_producto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        productoId:        form.value.productoId,
        categoriaId:       form.value.categoriaId || null,
        nombreProducto:    form.value.nombreProducto,
        marca:             form.value.marca,
        tamanio:           form.value.tamanio,
        presentacion:      form.value.presentacion,
        unidadesPorVenta:  form.value.unidadesPorVenta,
        codigoBarras:      form.value.codigoBarras,
        sku:               form.value.sku || null,
        precioCompra:      form.value.precioCompra,
        stockMinimo:       form.value.stockMinimo || 0,
        pctMayoreo:        form.value.pctMayoreo || 0,
        pctMedioMayoreo:   form.value.pctMedioMayoreo || 0,
        pctMenudeo:        form.value.pctMenudeo || 0,
        pctPublicoGeneral: form.value.pctPublicoGeneral || 0,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      alerta.value.mostrar('Producto actualizado correctamente', 'exito')
      cancelarEdicion()
    } else if (data.status === -2) {
      alerta.value.mostrar('Ya existe un producto con ese nombre', 'advertencia')
    } else if (data.status === -3) {
      alerta.value.mostrar('Ya existe un producto con ese código de barras', 'advertencia')
    } else if (data.status === -4) {
      alerta.value.mostrar('Ya existe un producto con ese SKU', 'advertencia')
    } else {
      alerta.value.mostrar('Error al actualizar el producto', 'error')
    }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  } finally {
    guardando.value = false
  }
}

const cancelarEdicion = () => {
  productoSeleccionado.value = null
  busqueda.value = ''
  sugerencias.value = []
  form.value = {
    productoId: null, nombreProducto: '', marca: '', tamanio: '',
    codigoBarras: '', sku: '', categoriaId: '', presentacion: '',
    unidadesPorVenta: null, precioCompra: null, stockMinimo: null,
    pctMayoreo: null, pctMedioMayoreo: null, pctMenudeo: null, pctPublicoGeneral: null,
  }
}

// ─── Categorías ───────────────────────────────────────────
const cargarCategorias = async () => {
  try {
    const res  = await fetch('/php/obtener_categorias.php')
    const data = await res.json()
    if (data.status === 1) categorias.value = data.categorias
  } catch (e) {
    alerta.value.mostrar('Error al cargar categorías', 'error')
  }
}

const abrirModalCategoria  = () => { modalCategoria.value = true; nuevaCategoria.value = '' }
const cerrarModalCategoria = () => { modalCategoria.value = false }

const guardarCategoria = async () => {
  if (!nuevaCategoria.value.trim()) return
  guardandoCategoria.value = true
  try {
    const res  = await fetch('/php/insertar_categoria.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombreCategoria: nuevaCategoria.value.trim() })
    })
    const data = await res.json()
    if (data.status === 1) {
      alerta.value.mostrar('Categoría agregada', 'exito')
      await cargarCategorias()
      const nueva = categorias.value.find(c => c.NombreCategoria === nuevaCategoria.value.trim())
      if (nueva) form.value.categoriaId = nueva.CategoriaId
      cerrarModalCategoria()
    } else if (data.status === -2) {
      alerta.value.mostrar('Esa categoría ya existe', 'advertencia')
    } else {
      alerta.value.mostrar('Error al guardar categoría', 'error')
    }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  } finally {
    guardandoCategoria.value = false
  }
}

onMounted(cargarCategorias)
</script>

<style scoped>
.editar-producto { padding: 1.5rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; align-items: center; margin-bottom: 1.5rem; }
.page-header__left { display: flex; align-items: center; gap: 1rem; }
.btn-back { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; color: #1a2b3c; transition: background 0.2s; }
.btn-back:hover { background: #f1f5f9; }
.breadcrumb { font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem; }
.breadcrumb__link { color: #f97316; text-decoration: none; }
.breadcrumb__link:hover { text-decoration: underline; }
.breadcrumb__sep { color: #cbd5e1; }
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.buscador-card { margin-bottom: 1.25rem; }
.buscador-body { padding-bottom: 1.25rem; }
.buscador-wrap { position: relative; }
.dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; overflow: hidden; }
.dropdown__item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 1rem; cursor: pointer; transition: background 0.15s; }
.dropdown__item:hover { background: #f8fafc; }
.dropdown__item-icon { width: 36px; height: 36px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #2563eb; }
.dropdown__item-icon svg { width: 18px; height: 18px; }
.dropdown__item-info { flex: 1; display: flex; flex-direction: column; gap: 0.15rem; }
.dropdown__item-nombre { font-size: 0.9rem; font-weight: 600; color: #1a2b3c; }
.dropdown__item-sub { font-size: 0.78rem; color: #94a3b8; }
.dropdown__item-cat { font-size: 0.78rem; font-weight: 600; color: #2563eb; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; gap: 0.75rem; text-align: center; }
.empty-state__icono { width: 80px; height: 80px; background: #eff6ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #2563eb; }
.empty-state__icono svg { width: 40px; height: 40px; }
.empty-state__titulo { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.empty-state__desc { font-size: 0.875rem; color: #94a3b8; margin: 0; max-width: 320px; line-height: 1.6; }
.form-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
.card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; overflow: hidden; }
.card--sticky { position: sticky; top: 1rem; }
.card__header { display: flex; align-items: center; gap: 0.875rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.card__icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card__icon svg { width: 18px; height: 18px; }
.card__icon--blue   { background: #1a2b3c; color: #fff; }
.card__icon--dark   { background: #1a2b3c; color: #fff; }
.card__icon--orange { background: #f97316; color: #fff; }
.card__title { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitle { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.field { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
.field:last-child { margin-bottom: 0; }
.field--half { max-width: 50%; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field__label { font-size: 0.72rem; font-weight: 700; color: #64748b; letter-spacing: 0.04em; }
.required { color: #f97316; }
.input-wrap { position: relative; display: flex; align-items: center; }
.input-wrap--grow { flex: 1; }
.input-icon { position: absolute; left: 0.75rem; width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.input-prefix { position: absolute; left: 0.75rem; font-size: 0.9rem; color: #64748b; pointer-events: none; }
.input-suffix { position: absolute; right: 0.75rem; font-size: 0.85rem; color: #64748b; pointer-events: none; }
.input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; }
.input:focus { border-color: #1a2b3c; background: #fff; }
.input--select { appearance: none; cursor: pointer; }
.input--prefix { padding-left: 1.5rem; }
.input--suffix { padding-right: 2rem; }
.select-add-wrap { display: flex; gap: 0.5rem; align-items: center; }
.btn-add { width: 36px; height: 36px; flex-shrink: 0; background: #1a2b3c; color: #fff; border: none; border-radius: 8px; font-size: 1.3rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.btn-add:hover { background: #f97316; }
.price-item { border-radius: 10px; padding: 0.875rem 1rem; margin-bottom: 0.75rem; border: 1px solid transparent; }
.price-item:last-child { margin-bottom: 0; }
.price-item__header { display: flex; align-items: center; gap: 0.5rem; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
.price-item__header svg { width: 14px; height: 14px; }
.price-item__detail { display: flex; align-items: center; justify-content: space-between; }
.price-item__pct { font-size: 0.8rem; color: #64748b; }
.price-item__value { font-size: 1.3rem; font-weight: 800; }
.price-item--mayoreo  { background: #eff6ff; border-color: #bfdbfe; }
.price-item--mayoreo .price-item__header  { color: #1d4ed8; }
.price-item--mayoreo .price-item__value   { color: #1d4ed8; }
.price-item--medio    { background: #faf5ff; border-color: #e9d5ff; }
.price-item--medio .price-item__header    { color: #7c3aed; }
.price-item--medio .price-item__value     { color: #7c3aed; }
.price-item--menudeo  { background: #f0fdf4; border-color: #bbf7d0; }
.price-item--menudeo .price-item__header  { color: #16a34a; }
.price-item--menudeo .price-item__value   { color: #16a34a; }
.price-item--publico  { background: #fff7ed; border-color: #fed7aa; }
.price-item--publico .price-item__header  { color: #ea580c; }
.price-item--publico .price-item__value   { color: #ea580c; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
.summary-row:last-child { border-bottom: none; }
.summary-row__label { color: #94a3b8; }
.summary-row__value { font-weight: 600; color: #1a2b3c; text-align: right; max-width: 60%; word-break: break-word; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem; }
.btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s, opacity 0.2s; }
.btn svg { width: 16px; height: 16px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--primary   { background: #f97316; color: #fff; }
.btn--primary:hover:not(:disabled) { background: #ea580c; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #fff; border-radius: 14px; width: 400px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden; }
.modal__header { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.modal__title { font-size: 1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.modal__close { background: none; border: none; font-size: 1rem; color: #94a3b8; cursor: pointer; }
.modal__body { padding: 1.25rem; }
.modal__footer { display: flex; gap: 0.75rem; justify-content: flex-end; padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; }
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }

.buscador-card {
  overflow: visible;
}

</style>