<template>
  <div class="reporte-ventas">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/reportes')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/reportes" class="breadcrumb__link">Reportes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Ventas</span>
          </span>
          <h1 class="page-titulo">Reporte de Ventas</h1>
        </div>
      </div>
      <div class="header-acciones">
        <button class="btn-export btn-export--excel" :disabled="!ventas.length" @click="exportarExcel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Excel
        </button>
        <button class="btn-export btn-export--pdf" :disabled="!ventas.length" @click="exportarPDF">
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
    <div class="card">
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
            <label>Cajero</label>
            <select v-model="filtros.empleadoId" class="input">
              <option value="">Todos</option>
              <option v-for="c in cajeros" :key="c.EmpleadoId" :value="c.EmpleadoId">
                {{ c.NombreCompleto }}
              </option>
            </select>
          </div>

          <div class="filtro-campo">
            <label>Caja ID</label>
            <input v-model="filtros.cajaId" type="number" min="1" placeholder="Ej. 5" class="input" />
          </div>

          <div class="filtro-campo">
            <label>Método de pago</label>
            <select v-model="filtros.metodoPago" class="input">
              <option value="">Todos</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Tarjeta">Tarjeta</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Credito">Crédito</option>
            </select>
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

    <!-- Totales -->
    <div v-if="ventas.length" class="totales-grid">
      <div class="total-card">
        <span class="total-label">Total ventas</span>
        <span class="total-valor">{{ ventas.length }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Monto total</span>
        <span class="total-valor verde">${{ formato(montoTotal) }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Promedio por venta</span>
        <span class="total-valor">${{ formato(promedio) }}</span>
      </div>
    </div>

    <!-- Resultados -->
    <div class="card tabla-card">
      <div class="card__header">
        <div class="card__icono card__icono--verde">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </div>
        <div>
          <h2 class="card__titulo">Resultados</h2>
          <p class="card__subtitulo">{{ ventas.length }} ventas encontradas</p>
        </div>
      </div>
      <div class="card__body p0">

        <div v-if="cargando" class="estado-carga">
          <span class="spinner"></span>
          <p>Cargando reporte...</p>
        </div>

        <div v-else-if="!ventas.length && buscado" class="empty-state">
          <div class="empty-state__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3 class="empty-state__titulo">Sin resultados</h3>
          <p class="empty-state__desc">No hay ventas con los filtros seleccionados</p>
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
                <th>Venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Cajero</th>
                <th>Sucursal</th>
                <th>Caja</th>
                <th>Pagos</th>
                <th class="text-right">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="v in ventas" :key="v.VentaId">
                <tr class="fila-venta" @click="toggleDetalle(v.VentaId)">
                  <td><span class="venta-id">#{{ v.VentaId }}</span></td>
                  <td>{{ v.Fecha }}</td>
                  <td>{{ v.Cliente }}</td>
                  <td>{{ v.Cajero }}</td>
                  <td>{{ v.NombreSucursal }}</td>
                  <td class="text-center">{{ v.CajaId }}</td>
                  <td><span class="pagos-text">{{ v.Pagos }}</span></td>
                  <td class="text-right"><strong class="verde">${{ formato(v.Total) }}</strong></td>
                  <td class="text-center">
                    <span class="toggle-icon" :class="{ abierto: detalleAbierto === v.VentaId }">▸</span>
                  </td>
                </tr>
                <!-- Fila detalle productos -->
                <tr v-if="detalleAbierto === v.VentaId" class="fila-detalle">
                  <td colspan="9">
                    <div class="detalle-productos">
                      <span class="detalle-label">Productos:</span>
                      <span class="detalle-valor">{{ v.Productos }}</span>
                    </div>
                  </td>
                </tr>
              </template>
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
const ventas   = ref([])
const sucursales = ref([])
const cajeros    = ref([])
const detalleAbierto = ref(null)

const filtros = ref({
  fechaDesde:  '',
  fechaHasta:  '',
  sucursalId:  '',
  empleadoId:  '',
  cajaId:      '',
  metodoPago:  '',
})

const formato    = (n) => Number(n || 0).toFixed(2)
const montoTotal = computed(() => ventas.value.reduce((s, v) => s + Number(v.Total), 0))
const promedio   = computed(() => ventas.value.length ? montoTotal.value / ventas.value.length : 0)

function toggleDetalle(ventaId) {
  detalleAbierto.value = detalleAbierto.value === ventaId ? null : ventaId
}

async function cargarSucursales() {
  try {
    const res  = await fetch('/php/obtener_sucursales_reporte.php')
    const data = await res.json()
    if (data.status === 1) sucursales.value = data.sucursales
  } catch { /* silencioso */ }
}

async function cargarCajeros() {
  try {
    const res  = await fetch('/php/obtener_cajeros.php')
    const data = await res.json()
    if (data.status === 1) cajeros.value = data.cajeros
  } catch { /* silencioso */ }
}

async function cargarReporte() {
  cargando.value = true
  buscado.value  = false
  ventas.value   = []
  try {
    const res  = await fetch('/php/reporte_ventas.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        fechaDesde:  filtros.value.fechaDesde  || null,
        fechaHasta:  filtros.value.fechaHasta  || null,
        sucursalId:  filtros.value.sucursalId  || null,
        empleadoId:  filtros.value.empleadoId  || null,
        cajaId:      filtros.value.cajaId      || null,
        metodoPago:  filtros.value.metodoPago  || null,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      ventas.value = data.ventas
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
  filtros.value = { fechaDesde: '', fechaHasta: '', sucursalId: '', empleadoId: '', cajaId: '', metodoPago: '' }
  ventas.value  = []
  buscado.value = false
  detalleAbierto.value = null
}

/* ── Exportar Excel ──────────────────────────────────────────── */
async function exportarExcel() {
  const ExcelJS = (await import('exceljs')).default
  const wb      = new ExcelJS.Workbook()
  const ws      = wb.addWorksheet('Ventas')

  ws.columns = [
    { header: 'Venta',     key: 'VentaId',        width: 10 },
    { header: 'Fecha',     key: 'Fecha',           width: 14 },
    { header: 'Cliente',   key: 'Cliente',         width: 24 },
    { header: 'Cajero',    key: 'Cajero',          width: 24 },
    { header: 'Sucursal',  key: 'NombreSucursal',  width: 20 },
    { header: 'Caja',      key: 'CajaId',          width: 8  },
    { header: 'Pagos',     key: 'Pagos',           width: 30 },
    { header: 'Productos', key: 'Productos',       width: 40 },
    { header: 'Total',     key: 'Total',           width: 14 },
  ]

  ws.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })

  ventas.value.forEach(v => ws.addRow(v))

  const buffer = await wb.xlsx.writeBuffer()
  const blob   = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
  const url    = URL.createObjectURL(blob)
  const a      = document.createElement('a')
  a.href       = url
  a.download   = `ventas_${new Date().toISOString().slice(0,10)}.xlsx`
  a.click()
  URL.revokeObjectURL(url)
}

/* ── Exportar PDF ────────────────────────────────────────────── */
async function exportarPDF() {
  const { default: jsPDF }     = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'landscape' })
  doc.setFontSize(16)
  doc.text('Reporte de Ventas', 14, 16)
  doc.setFontSize(10)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-MX')}`, 14, 23)
  doc.text(`Total: $${formato(montoTotal.value)} | Ventas: ${ventas.value.length}`, 14, 29)

  autoTable(doc, {
    startY: 34,
    head: [['Venta', 'Fecha', 'Cliente', 'Cajero', 'Sucursal', 'Caja', 'Pagos', 'Total']],
    body: ventas.value.map(v => [
      `#${v.VentaId}`, v.Fecha, v.Cliente, v.Cajero,
      v.NombreSucursal, v.CajaId, v.Pagos, `$${formato(v.Total)}`
    ]),
    headStyles:         { fillColor: [26, 43, 60], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    styles:             { fontSize: 8 },
  })

  doc.save(`ventas_${new Date().toISOString().slice(0,10)}.pdf`)
}

onMounted(async () => {
  await Promise.all([cargarSucursales(), cargarCajeros()])
})
</script>

<style scoped>
.reporte-ventas { max-width: 1200px; margin: 0 auto; }

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
.card__icono--azul  { background: #1a2b3c; color: #fff; }
.card__icono--verde { background: #f0fdf4; color: #16a34a; }
.card__titulo { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitulo { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.p0 { padding: 0; }

/* Filtros */
.filtros-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr) repeat(3, 1fr) auto;
  gap: 1rem;
  align-items: end;
}
.filtro-campo { display: flex; flex-direction: column; gap: 0.4rem; }
.filtro-campo label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.input {
  padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none;
  transition: border-color 0.2s; width: 100%; box-sizing: border-box;
}
.input:focus { border-color: #2563eb; background: #fff; }
.filtro-acciones { display: flex; gap: 8px; }
.btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.btn--primary { background: #1a2b3c; color: #fff; }
.btn--primary:hover:not(:disabled) { background: #2563eb; }
.btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }

/* Totales */
.totales-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 1rem; margin-bottom: 1.25rem;
}
.total-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 4px;
}
.total-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.total-valor { font-size: 1.4rem; font-weight: 800; color: #1a2b3c; }
.total-valor.verde { color: #16a34a; }

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
.fila-venta { cursor: pointer; transition: background 0.15s; }
.fila-venta:hover { background: #f8fafc; }
.text-right  { text-align: right; }
.text-center { text-align: center; }

.venta-id { font-weight: 700; color: #2563eb; }
.verde { color: #16a34a; }
.pagos-text { font-size: 0.78rem; color: #64748b; }

.toggle-icon {
  display: inline-block; font-size: 0.8rem; color: #94a3b8;
  transition: transform 0.2s;
}
.toggle-icon.abierto { transform: rotate(90deg); color: #2563eb; }

.fila-detalle td { background: #f8fafc; padding: 0.75rem 1rem; }
.detalle-productos { display: flex; align-items: flex-start; gap: 8px; }
.detalle-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; white-space: nowrap; }
.detalle-valor { font-size: 0.85rem; color: #1a2b3c; line-height: 1.5; }

/* Empty / carga */
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
</style>