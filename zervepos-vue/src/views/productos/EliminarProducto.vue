<template>
  <div class="eliminar-producto">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.back()">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/productos" class="breadcrumb__link">Productos</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Eliminar Producto</span>
          </span>
          <h1 class="page-title">Eliminar Producto</h1>
        </div>
      </div>
    </div>

    <!-- Buscador -->
    <div class="card buscador-card">
      <div class="card__header">
        <div class="card__icon card__icon--red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div>
          <h2 class="card__title">Buscar Producto</h2>
          <p class="card__subtitle">Por nombre, código de barras o marca</p>
        </div>
      </div>
      <div class="card__body">
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
      </div>
      <h3 class="empty-state__titulo">Ningún producto seleccionado</h3>
      <p class="empty-state__desc">Usa el buscador de arriba para encontrar y seleccionar el producto que deseas eliminar.</p>
    </div>

    <!-- Tarjeta de confirmación -->
    <div v-else class="producto-card">
      <div class="producto-card__header">
        <div class="producto-card__icono">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div>
          <h2 class="producto-card__nombre">{{ productoSeleccionado.NombreProducto }}</h2>
          <p class="producto-card__sub">{{ productoSeleccionado.Marca }} · {{ productoSeleccionado.NombreCategoria || 'Sin categoría' }}</p>
        </div>
      </div>

      <div class="producto-card__detalle">
        <div class="detalle-row"><span class="detalle-row__label">Código de Barras</span><span class="detalle-row__value">{{ productoSeleccionado.CodigoBarras }}</span></div>
        <div class="detalle-row"><span class="detalle-row__label">SKU</span><span class="detalle-row__value">{{ productoSeleccionado.SKU || '—' }}</span></div>
        <div class="detalle-row"><span class="detalle-row__label">Tamaño</span><span class="detalle-row__value">{{ productoSeleccionado.Tamaño || '—' }}</span></div>
        <div class="detalle-row"><span class="detalle-row__label">Presentación</span><span class="detalle-row__value">{{ productoSeleccionado.Presentacion || '—' }}</span></div>
        <div class="detalle-row"><span class="detalle-row__label">Precio de Compra</span><span class="detalle-row__value">${{ productoSeleccionado.PrecioCompra }}</span></div>
        <div class="detalle-row"><span class="detalle-row__label">Stock Mínimo</span><span class="detalle-row__value">{{ productoSeleccionado.StockMinimo }} uds</span></div>
      </div>

      <div class="advertencia">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p>Este producto será desactivado del catálogo. No aparecerá en ventas ni búsquedas, pero sus registros históricos se conservarán.</p>
      </div>

      <div class="producto-card__acciones">
        <button class="btn btn--secondary" @click="cancelar">Cancelar</button>
        <button class="btn btn--danger" @click="eliminarProducto" :disabled="eliminando">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          {{ eliminando ? 'Eliminando...' : 'Eliminar Producto' }}
        </button>
      </div>
    </div>

    <ConfirmacionPeligrosa ref="confirmacion" />
    <AlertaToast ref="alerta" />

  </div>
</template>

<script setup>
import { ref } from 'vue'
import AlertaToast from '../../components/AlertaToast.vue'
import ConfirmacionPeligrosa from '../../components/ConfirmacionPeligrosa.vue'

const alerta             = ref(null)
const confirmacion       = ref(null)
const busqueda           = ref('')
const sugerencias        = ref([])
const productoSeleccionado = ref(null)
const eliminando         = ref(false)
let   busquedaTimer      = null

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

const seleccionarProducto = (p) => {
  sugerencias.value        = []
  busqueda.value           = p.NombreProducto
  productoSeleccionado.value = p
}

const eliminarProducto = async () => {
  const confirmado = await confirmacion.value.mostrar(
    '¿Eliminar producto?',
    `"${productoSeleccionado.value.NombreProducto}" será desactivado del catálogo. Esta acción requiere confirmación.`
  )
  if (!confirmado) return

  eliminando.value = true
  try {
    const res  = await fetch('/php/eliminar_producto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ productoId: productoSeleccionado.value.ProductoId })
    })
    const data = await res.json()
    if (data.status === 1) {
      alerta.value.mostrar('Producto eliminado correctamente', 'exito')
      cancelar()
    } else if (data.status === -2) {
      alerta.value.mostrar('El producto no existe o ya fue eliminado', 'advertencia')
    }else if (data.status === -3) {
  alerta.value.mostrar('No se puede eliminar, el producto tiene stock activo en una o más sucursales', 'advertencia')
} 
    
    else {
      alerta.value.mostrar('Error al eliminar el producto', 'error')
    }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  } finally {
    eliminando.value = false
  }
}

const cancelar = () => {
  productoSeleccionado.value = null
  busqueda.value             = ''
  sugerencias.value          = []
}
</script>

<style scoped>
.eliminar-producto { padding: 1.5rem; max-width: 800px; margin: 0 auto; }
.page-header { display: flex; align-items: center; margin-bottom: 1.5rem; }
.page-header__left { display: flex; align-items: center; gap: 1rem; }
.btn-back { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; color: #1a2b3c; transition: background 0.2s; }
.btn-back:hover { background: #f1f5f9; }
.breadcrumb { font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem; }
.breadcrumb__link { color: #f97316; text-decoration: none; }
.breadcrumb__link:hover { text-decoration: underline; }
.breadcrumb__sep { color: #cbd5e1; }
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; }
.buscador-card { overflow: visible; }
.card__header { display: flex; align-items: center; gap: 0.875rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.card__icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.card__icon svg { width: 18px; height: 18px; }
.card__icon--red { background: #fff1f1; color: #dc2626; }
.card__title { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitle { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.buscador-wrap { position: relative; }
.dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; overflow: hidden; }
.dropdown__item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 1rem; cursor: pointer; transition: background 0.15s; }
.dropdown__item:hover { background: #f8fafc; }
.dropdown__item-icon { width: 36px; height: 36px; background: #fff1f1; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #dc2626; }
.dropdown__item-icon svg { width: 18px; height: 18px; }
.dropdown__item-info { flex: 1; display: flex; flex-direction: column; gap: 0.15rem; }
.dropdown__item-nombre { font-size: 0.9rem; font-weight: 600; color: #1a2b3c; }
.dropdown__item-sub { font-size: 0.78rem; color: #94a3b8; }
.dropdown__item-cat { font-size: 0.78rem; font-weight: 600; color: #dc2626; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; gap: 0.75rem; text-align: center; }
.empty-state__icono { width: 80px; height: 80px; background: #fff1f1; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #dc2626; }
.empty-state__icono svg { width: 40px; height: 40px; }
.empty-state__titulo { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.empty-state__desc { font-size: 0.875rem; color: #94a3b8; margin: 0; max-width: 320px; line-height: 1.6; }
.producto-card { background: #fff; border-radius: 12px; border: 1px solid #fecaca; overflow: hidden; }
.producto-card__header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid #fee2e2; }
.producto-card__icono { width: 52px; height: 52px; background: #fff1f1; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0; }
.producto-card__icono svg { width: 26px; height: 26px; }
.producto-card__nombre { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; margin: 0 0 0.2rem; }
.producto-card__sub { font-size: 0.85rem; color: #94a3b8; margin: 0; }
.producto-card__detalle { padding: 1.25rem 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.detalle-row { display: flex; flex-direction: column; gap: 0.2rem; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; }
.detalle-row:nth-child(odd) { padding-right: 2rem; }
.detalle-row__label { font-size: 0.72rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.04em; }
.detalle-row__value { font-size: 0.9rem; font-weight: 600; color: #1a2b3c; }
.advertencia { display: flex; align-items: flex-start; gap: 0.75rem; margin: 0 1.5rem 1.25rem; padding: 1rem; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; }
.advertencia svg { width: 18px; height: 18px; color: #f97316; flex-shrink: 0; margin-top: 1px; }
.advertencia p { font-size: 0.85rem; color: #92400e; margin: 0; line-height: 1.6; }
.producto-card__acciones { display: flex; gap: 0.75rem; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #fee2e2; }
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 0.75rem; width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.input:focus { border-color: #dc2626; background: #fff; }
.btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s, opacity 0.2s; }
.btn svg { width: 16px; height: 16px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--secondary { background: #f1f5f9; color: #1a2b3c; }
.btn--secondary:hover { background: #e2e8f0; }
.btn--danger { background: #dc2626; color: #fff; }
.btn--danger:hover:not(:disabled) { background: #b91c1c; }
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>