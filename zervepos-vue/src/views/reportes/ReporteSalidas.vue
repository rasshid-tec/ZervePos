<template>
  <div class="reporte-salidas">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/reportes')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/reportes" class="breadcrumb__link">Reportes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Salidas de Inventario</span>
          </span>
          <h1 class="page-titulo">Salidas de Inventario</h1>
        </div>
      </div>
      <div class="header-acciones">
        <button class="btn-export btn-export--excel" :disabled="!salidasFiltradas.length" @click="exportarExcel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Excel
        </button>
        <button class="btn-export btn-export--pdf" :disabled="!salidasFiltradas.length" @click="exportarPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          PDF
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card filtros-card">
      <div class="card__header">
        <div class="card__icono card__icono--azul">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
        </div>
        <h2 class="card__titulo">Filtros</h2>
      </div>
      <div class="card__body">
        <div class="filtros-grid">

          <div class="filtro-campo">
            <label>Desde</label>
            <input v-model="filtros.fechaDesde" type="date" class="input" />
          </div>

          <div class="filtro-campo">
            <label>Hasta</label>
            <input v-model="filtros.fechaHasta" type="date" class="input" />
          </div>

          <div class="filtro-campo">
            <label>Sucursal</label>
            <select v-model="filtros.sucursalId" class="input">
              <option value="">Todas</option>
              <option v-for="s in sucursales" :key="s.SucursalId" :value="s.SucursalId">
                {{ s.NombreSucursal }}
              </option>
            </select>
          </div>

          <div class="filtro-campo">
            <label>Tipo</label>
            <select v-model="filtros.origen" class="input">
              <option value="">Todos</option>
              <option value="Venta">Ventas</option>
              <option value="Salida">Movimientos</option>
            </select>
          </div>

          <!-- Checkbox producto -->
          <div class="filtro-campo filtro-campo--producto">
            <div class="checkbox-wrap">
              <input
                id="filtrarProducto"
                v-model="filtrarPorProducto"
                type="checkbox"
                class="checkbox"
                @change="onToggleProducto"
              />
              <label for="filtrarProducto" class="checkbox-label">Filtrar por producto</label>
            </div>

            <!-- Buscador producto -->
            <div v-if="filtrarPorProducto" class="producto-buscador">
              <div class="input-contenedor">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                  v-model="terminoProducto"
                  type="text"
                  class="input input--icon"
                  placeholder="Buscar producto..."
                  autocomplete="off"
                  @input="onInputProducto"
                  @keydown.escape="cerrarDropdownProducto"
                />
                <button v-if="productoSeleccionado" class="btn-limpiar-producto" @click="limpiarProducto">✕</button>
              </div>

              <Transition name="dropdown">
                <div v-if="dropdownProducto.length" class="dropdown-producto">
                  <div
                    v-for="p in dropdownProducto"
                    :key="p.ProductoId"
                    class="dropdown-item"
                    @click="seleccionarProducto(p)"
                  >
                    <span class="dropdown-nombre">{{ p.NombreProducto }}</span>
                    <span class="dropdown-marca">{{ p.Marca }}</span>
                  </div>
                </div>
              </Transition>

              <div v-if="productoSeleccionado" class="producto-tag">
                <span>{{ productoSeleccionado.NombreProducto }}</span>
              </div>
            </div>
          </div>

          <div class="filtro-acciones">
            <button class="btn btn--secondary" @click="limpiarFiltros">Limpiar</button>
            <button class="btn btn--primary" @click="cargarReporte" :disabled="cargando">
              {{ cargando ? 'Buscando...' : 'Buscar' }}
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Resultados -->
    <div class="card tabla-card">
      <div class="card__header">
        <div class="card__icono card__icono--naranja">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
            <path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/>
          </svg>
        </div>
        <div>
          <h2 class="card__titulo">Resultados</h2>
          <p class="card__subtitulo">{{ salidasFiltradas.length }} registros encontrados</p>
        </div>
      </div>
      <div class="card__body p0">

        <div v-if="cargando" class="estado-carga">
          <span class="spinner"></span>
          <p>Cargando reporte...</p>
        </div>

        <div v-else-if="!salidas.length && buscado" class="empty-state">
          <div class="empty-state__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3 class="empty-state__titulo">Sin resultados</h3>
          <p class="empty-state__desc">No hay registros con los filtros seleccionados</p>
        </div>

        <div v-else-if="!buscado" class="empty-state">
          <div class="empty-state__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
          </div>
          <h3 class="empty-state__titulo">Aplica filtros para ver resultados</h3>
          <p class="empty-state__desc">Selecciona un rango de fechas y presiona Buscar</p>
        </div>

        <div v-else class="tabla-wrap">
          <table class="tabla">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Referencia</th>
                <th>Producto</th>
                <th>Marca</th>
                <th>Cantidad</th>
                <th>Sucursal</th>
                <th>Empleado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(s, idx) in salidasFiltradas" :key="idx">
                <td>{{ s.Fecha }}</td>
                <td>
                  <span class="badge" :class="s.Origen === 'Venta' ? 'badge--verde' : 'badge--naranja'">
                    {{ s.Origen }}
                  </span>
                </td>
                <td>#{{ s.ReferenciaId }}</td>
                <td>{{ s.NombreProducto }}</td>
                <td>{{ s.Marca }}</td>
                <td class="text-center">{{ s.Cantidad }}</td>
                <td>{{ s.NombreSucursal }}</td>
                <td>{{ s.Empleado }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast }                 from '../../composables/useToast'

const toast    = useToast()
const cargando = ref(false)
const buscado  = ref(false)
const salidas  = ref([])
const sucursales = ref([])

/* ── Filtros ─────────────────────────────────────────────────── */
const filtros = ref({
  fechaDesde: '',
  fechaHasta: '',
  sucursalId: '',
  origen:     '',
})

/* ── Buscador producto ───────────────────────────────────────── */
const filtrarPorProducto  = ref(false)
const terminoProducto     = ref('')
const dropdownProducto    = ref([])
const productoSeleccionado = ref(null)
let   timerProducto       = null

const salidasFiltradas = computed(() => salidas.value)

/* ── Sucursales ──────────────────────────────────────────────── */
async function cargarSucursales() {
  try {
    const res  = await fetch('/php/obtener_sucursales_reporte.php')
    const data = await res.json()
    if (data.status === 1) sucursales.value = data.sucursales
  } catch { /* silencioso */ }
}

/* ── Producto autocomplete ───────────────────────────────────── */
function onToggleProducto() {
  if (!filtrarPorProducto.value) limpiarProducto()
}

function onInputProducto() {
  clearTimeout(timerProducto)
  const t = terminoProducto.value.trim()
  if (t.length < 2) { dropdownProducto.value = []; return }
  timerProducto = setTimeout(() => buscarProductos(t), 300)
}

async function buscarProductos(texto) {
  try {
    const res  = await fetch('/php/buscar_productos_general.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ busqueda: texto })
    })
    const data = await res.json()
    dropdownProducto.value = data.status === 1 ? data.productos : []
  } catch {
    dropdownProducto.value = []
  }
}

function seleccionarProducto(p) {
  productoSeleccionado.value = p
  terminoProducto.value      = p.NombreProducto
  dropdownProducto.value     = []
}

function limpiarProducto() {
  productoSeleccionado.value = null
  terminoProducto.value      = ''
  dropdownProducto.value     = []
}

function cerrarDropdownProducto() {
  dropdownProducto.value = []
}

/* ── Reporte ─────────────────────────────────────────────────── */
async function cargarReporte() {
  cargando.value = true
  buscado.value  = false
  salidas.value  = []
  try {
    const res  = await fetch('/php/reporte_salidas.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        fechaDesde: filtros.value.fechaDesde || null,
        fechaHasta: filtros.value.fechaHasta || null,
        sucursalId: filtros.value.sucursalId || null,
        origen:     filtros.value.origen     || null,
        productoId: productoSeleccionado.value?.ProductoId || null,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      salidas.value = data.salidas
    } else {
      toast.error('Error al cargar reporte')
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    cargando.value = false
    buscado.value  = true
  }
}

function limpiarFiltros() {
  filtros.value = { fechaDesde: '', fechaHasta: '', sucursalId: '', origen: '' }
  filtrarPorProducto.value = false
  limpiarProducto()
  salidas.value = []
  buscado.value = false
}

/* ── Exportar Excel ──────────────────────────────────────────── */
async function exportarExcel() {
  const ExcelJS = (await import('exceljs')).default
  const wb      = new ExcelJS.Workbook()
  const ws      = wb.addWorksheet('Salidas de Inventario')

  ws.columns = [
    { header: 'Fecha',      key: 'Fecha',          width: 14 },
    { header: 'Tipo',       key: 'Origen',         width: 12 },
    { header: 'Referencia', key: 'ReferenciaId',   width: 12 },
    { header: 'Producto',   key: 'NombreProducto', width: 30 },
    { header: 'Marca',      key: 'Marca',          width: 16 },
    { header: 'Cantidad',   key: 'Cantidad',       width: 10 },
    { header: 'Sucursal',   key: 'NombreSucursal', width: 20 },
    { header: 'Empleado',   key: 'Empleado',       width: 24 },
  ]

  ws.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })

  salidasFiltradas.value.forEach(s => ws.addRow(s))

  const buffer = await wb.xlsx.writeBuffer()
  const blob   = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
  const url    = URL.createObjectURL(blob)
  const a      = document.createElement('a')
  a.href       = url
  a.download   = `salidas_inventario_${new Date().toISOString().slice(0,10)}.xlsx`
  a.click()
  URL.revokeObjectURL(url)
}

/* ── Exportar PDF ────────────────────────────────────────────── */
async function exportarPDF() {
  const { default: jsPDF }     = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'landscape' })
  doc.setFontSize(16)
  doc.text('Reporte de Salidas de Inventario', 14, 16)
  doc.setFontSize(10)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-MX')}`, 14, 23)

  autoTable(doc, {
    startY: 28,
    head: [['Fecha', 'Tipo', 'Referencia', 'Producto', 'Marca', 'Cantidad', 'Sucursal', 'Empleado']],
    body: salidasFiltradas.value.map(s => [
      s.Fecha, s.Origen, `#${s.ReferenciaId}`, s.NombreProducto,
      s.Marca, s.Cantidad, s.NombreSucursal, s.Empleado
    ]),
    headStyles:         { fillColor: [26, 43, 60], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    styles:             { fontSize: 9 },
  })

  doc.save(`salidas_inventario_${new Date().toISOString().slice(0,10)}.pdf`)
}

onMounted(cargarSucursales)
</script>

<style scoped>
.reporte-salidas { max-width: 1200px; margin: 0 auto; }

.page-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
}
.page-header__left { display: flex; align-items: center; gap: 1rem; }
.btn-back {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  width: 36px; height: 36px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; font-size: 1.1rem; color: #1a2b3c;
}
.btn-back:hover { background: #f1f5f9; }
.breadcrumb { font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem; }
.breadcrumb__link { color: #2563eb; text-decoration: none; }
.breadcrumb__sep { color: #cbd5e1; }
.page-titulo { font-size: 1.4rem; font-weight: 700; color: #1a2b3c; margin: 0; }

.header-acciones { display: flex; gap: 8px; }
.btn-export {
  display: flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 8px; font-size: 0.875rem;
  font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
}
.btn-export svg { width: 16px; height: 16px; }
.btn-export:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-export--excel { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.btn-export--excel:hover:not(:disabled) { background: #dcfce7; }
.btn-export--pdf   { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
.btn-export--pdf:hover:not(:disabled)   { background: #fee2e2; }

.card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; overflow: visible; }
.card__header { display: flex; align-items: center; gap: 0.875rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.card__icono { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card__icono svg { width: 18px; height: 18px; }
.card__icono--azul    { background: #1a2b3c; color: #fff; }
.card__icono--naranja { background: #fff4ed; color: #f97316; }
.card__titulo { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitulo { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.p0 { padding: 0; }

/* Filtros */
.filtros-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1.5fr auto;
  gap: 1rem;
  align-items: start;
}
.filtro-campo { display: flex; flex-direction: column; gap: 0.4rem; }
.filtro-campo label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.filtro-campo--producto { position: relative; }

.checkbox-wrap { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.checkbox { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
.checkbox-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; cursor: pointer; }

.producto-buscador { position: relative; }
.input-contenedor { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 10px; width: 14px; height: 14px; color: #94a3b8; pointer-events: none; }
.btn-limpiar-producto {
  position: absolute; right: 8px; background: none; border: none;
  color: #ef4444; cursor: pointer; font-size: 14px; padding: 0;
}

.input {
  padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none;
  transition: border-color 0.2s; width: 100%; box-sizing: border-box;
}
.input--icon { padding-left: 2rem; }
.input:focus { border-color: #2563eb; background: #fff; }

.dropdown-producto {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 200; overflow: hidden;
}
.dropdown-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 12px; cursor: pointer; transition: background 0.15s;
  border-bottom: 1px solid #f1f5f9;
}
.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: #f8fafc; }
.dropdown-nombre { font-size: 0.875rem; font-weight: 600; color: #1a2b3c; }
.dropdown-marca  { font-size: 0.75rem; color: #94a3b8; }

.producto-tag {
  margin-top: 6px; padding: 4px 10px; background: #eff6ff;
  border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: #2563eb;
  border: 1px solid #bfdbfe; display: inline-block;
}

.filtro-acciones { display: flex; gap: 8px; align-self: end; }
.btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.btn--primary { background: #1a2b3c; color: #fff; }
.btn--primary:hover:not(:disabled) { background: #2563eb; }
.btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }

/* Tabla */
.tabla-wrap { overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.tabla thead tr { background: #f8fafc; }
.tabla th {
  padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem;
  font-weight: 700; color: #64748b; text-transform: uppercase;
  letter-spacing: 0.04em; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.tabla td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #1a2b3c; }
.tabla tbody tr:hover { background: #f8fafc; }
.text-center { text-align: center; }

.badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.badge--verde   { background: #f0fdf4; color: #16a34a; }
.badge--naranja { background: #fff4ed; color: #f97316; }

.empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 4rem 2rem; gap: 0.75rem; text-align: center;
}
.empty-state__icono {
  width: 56px; height: 56px; background: #f1f5f9; border-radius: 14px;
  display: flex; align-items: center; justify-content: center; color: #94a3b8;
}
.empty-state__icono svg { width: 26px; height: 26px; }
.empty-state__titulo { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.empty-state__desc { font-size: 0.85rem; color: #94a3b8; margin: 0; max-width: 280px; line-height: 1.6; }

.estado-carga { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 3rem; color: #64748b; }
.spinner {
  width: 28px; height: 28px; border: 3px solid #e2e8f0;
  border-top-color: #2563eb; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>