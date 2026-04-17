<template>
  <div class="reporte-creditos">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/reportes')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/reportes" class="breadcrumb__link">Reportes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Créditos</span>
          </span>
          <h1 class="page-titulo">Reporte de Créditos</h1>
        </div>
      </div>
      <div class="header-acciones">
        <button class="btn-export btn-export--excel" :disabled="!creditos.length" @click="exportarExcel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Excel
        </button>
        <button class="btn-export btn-export--pdf" :disabled="!creditos.length" @click="exportarPDF">
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
            <label>Estado</label>
            <select v-model="filtros.estado" class="input">
              <option value="">Todos</option>
              <option value="ACTIVO">Activo</option>
              <option value="PAGADO">Pagado</option>
              <option value="CANCELADO">Cancelado</option>
            </select>
          </div>

          <!-- Buscador cliente -->
          <div class="filtro-campo filtro-campo--cliente">
            <div class="checkbox-wrap">
              <input id="filtrarCliente" v-model="filtrarPorCliente" type="checkbox" class="checkbox" @change="onToggleCliente" />
              <label for="filtrarCliente" class="checkbox-label">Filtrar por cliente</label>
            </div>
            <div v-if="filtrarPorCliente" class="cliente-buscador">
              <BuscadorClientes @seleccionar="onClienteSeleccionado" />
              <div v-if="clienteSeleccionado" class="cliente-tag">
                {{ clienteSeleccionado.NombreCliente }}
                <button @click="limpiarCliente" class="btn-limpiar-tag">✕</button>
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

    <!-- Totales -->
    <div v-if="creditos.length" class="totales-grid">
      <div class="total-card">
        <span class="total-label">Total créditos</span>
        <span class="total-valor">{{ creditos.length }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Monto total</span>
        <span class="total-valor">${{ formato(totalMonto) }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Total pagado</span>
        <span class="total-valor verde">${{ formato(totalPagado) }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Total pendiente</span>
        <span class="total-valor rojo">${{ formato(totalPendiente) }}</span>
      </div>
    </div>

    <!-- Resultados -->
    <div class="card tabla-card">
      <div class="card__header">
        <div class="card__icono card__icono--azul2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <line x1="2" y1="10" x2="22" y2="10"/>
            <line x1="6" y1="15" x2="10" y2="15"/>
          </svg>
        </div>
        <div>
          <h2 class="card__titulo">Resultados</h2>
          <p class="card__subtitulo">{{ creditos.length }} créditos encontrados</p>
        </div>
      </div>
      <div class="card__body p0">

        <div v-if="cargando" class="estado-carga">
          <span class="spinner"></span>
          <p>Cargando reporte...</p>
        </div>

        <div v-else-if="!creditos.length && buscado" class="empty-state">
          <div class="empty-state__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3 class="empty-state__titulo">Sin resultados</h3>
          <p class="empty-state__desc">No hay créditos con los filtros seleccionados</p>
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
                <th>Crédito</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Negocio</th>
                <th>Venta</th>
                <th class="text-right">Original</th>
                <th class="text-right">Pagado</th>
                <th class="text-right">Pendiente</th>
                <th>Estado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="c in creditos" :key="c.CreditoId">
                <tr class="fila-credito" @click="toggleDetalle(c.CreditoId)">
                  <td><span class="credito-id">#{{ c.CreditoId }}</span></td>
                  <td>{{ c.FechaCredito }}</td>
                  <td>{{ c.NombreCliente }}</td>
                  <td>{{ c.Negocio || '—' }}</td>
                  <td>#{{ c.VentaId }}</td>
                  <td class="text-right">${{ formato(c.MontoTotal) }}</td>
                  <td class="text-right verde">${{ formato(c.TotalPagado) }}</td>
                  <td class="text-right rojo">${{ formato(c.SaldoActual) }}</td>
                  <td>
                    <span class="badge" :class="badgeEstado(c.Estado)">{{ c.Estado }}</span>
                  </td>
                  <td class="text-center">
                    <span class="toggle-icon" :class="{ abierto: detalleAbierto === c.CreditoId }">▸</span>
                  </td>
                </tr>
                <!-- Abonos -->
                <tr v-if="detalleAbierto === c.CreditoId" class="fila-detalle">
                  <td colspan="10">
                    <div class="abonos-wrap">
                      <p class="abonos-titulo">Historial de abonos</p>
                      <div v-if="abonosPorCredito(c.CreditoId).length === 0" class="abonos-vacio">
                        Sin abonos registrados
                      </div>
                      <table v-else class="tabla-abonos">
                        <thead>
                          <tr>
                            <th>Abono #</th>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th class="text-right">Monto</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="a in abonosPorCredito(c.CreditoId)" :key="a.AbonoId">
                            <td>#{{ a.AbonoId }}</td>
                            <td>{{ a.FechaAbono }}</td>
                            <td>{{ a.MetodoPago }}</td>
                            <td class="text-right verde">${{ formato(a.MontoAbono) }}</td>
                          </tr>
                        </tbody>
                      </table>
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
import { ref, computed } from 'vue'
import BuscadorClientes  from '../../components/BuscadorClientes.vue'
import { useToast }      from '../../composables/useToast'

const toast    = useToast()
const cargando = ref(false)
const buscado  = ref(false)
const creditos = ref([])
const abonos   = ref([])
const detalleAbierto    = ref(null)
const filtrarPorCliente = ref(false)
const clienteSeleccionado = ref(null)

const filtros = ref({
  fechaDesde: '',
  fechaHasta: '',
  estado:     '',
})

const formato        = (n) => Number(n || 0).toFixed(2)
const totalMonto     = computed(() => creditos.value.reduce((s, c) => s + Number(c.MontoTotal),  0))
const totalPagado    = computed(() => creditos.value.reduce((s, c) => s + Number(c.TotalPagado), 0))
const totalPendiente = computed(() => creditos.value.reduce((s, c) => s + Number(c.SaldoActual), 0))

const abonosPorCredito = (creditoId) => abonos.value.filter(a => a.CreditoId === creditoId)

function toggleDetalle(id) {
  detalleAbierto.value = detalleAbierto.value === id ? null : id
}

function badgeEstado(estado) {
  if (estado === 'ACTIVO')   return 'badge--rojo'
  if (estado === 'PAGADO')   return 'badge--verde'
  if (estado === 'CANCELADO') return 'badge--gris'
  return ''
}

function onToggleCliente() {
  if (!filtrarPorCliente.value) limpiarCliente()
}

function onClienteSeleccionado(c) {
  clienteSeleccionado.value = c
}

function limpiarCliente() {
  clienteSeleccionado.value = null
}

async function cargarReporte() {
  cargando.value = true
  buscado.value  = false
  creditos.value = []
  abonos.value   = []
  try {
    const res  = await fetch('/php/reporte_creditos.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        fechaDesde: filtros.value.fechaDesde || null,
        fechaHasta: filtros.value.fechaHasta || null,
        estado:     filtros.value.estado     || null,
        clienteId:  clienteSeleccionado.value?.ClienteId || null,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      creditos.value = data.creditos
      abonos.value   = data.abonos
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
  filtros.value = { fechaDesde: '', fechaHasta: '', estado: '' }
  filtrarPorCliente.value   = false
  clienteSeleccionado.value = null
  creditos.value            = []
  abonos.value              = []
  buscado.value             = false
  detalleAbierto.value      = null
}

/* ── Exportar Excel ──────────────────────────────────────────── */
async function exportarExcel() {
  const ExcelJS = (await import('exceljs')).default
  const wb      = new ExcelJS.Workbook()
  const ws      = wb.addWorksheet('Créditos')

  ws.columns = [
    { header: 'Crédito',   key: 'CreditoId',    width: 10 },
    { header: 'Fecha',     key: 'FechaCredito', width: 14 },
    { header: 'Cliente',   key: 'NombreCliente', width: 24 },
    { header: 'Negocio',   key: 'Negocio',      width: 20 },
    { header: 'Venta',     key: 'VentaId',      width: 10 },
    { header: 'Original',  key: 'MontoTotal',   width: 14 },
    { header: 'Pagado',    key: 'TotalPagado',  width: 14 },
    { header: 'Pendiente', key: 'SaldoActual',  width: 14 },
    { header: 'Estado',    key: 'Estado',       width: 12 },
  ]

  ws.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })

  creditos.value.forEach(c => ws.addRow(c))

  // Hoja de abonos
  const ws2 = wb.addWorksheet('Abonos')
  ws2.columns = [
    { header: 'Abono',   key: 'AbonoId',   width: 10 },
    { header: 'Crédito', key: 'CreditoId', width: 10 },
    { header: 'Fecha',   key: 'FechaAbono', width: 14 },
    { header: 'Método',  key: 'MetodoPago', width: 16 },
    { header: 'Monto',   key: 'MontoAbono', width: 14 },
  ]
  ws2.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })
  abonos.value.forEach(a => ws2.addRow(a))

  const buffer = await wb.xlsx.writeBuffer()
  const blob   = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
  const url    = URL.createObjectURL(blob)
  const a      = document.createElement('a')
  a.href       = url
  a.download   = `creditos_${new Date().toISOString().slice(0,10)}.xlsx`
  a.click()
  URL.revokeObjectURL(url)
}

/* ── Exportar PDF ────────────────────────────────────────────── */
async function exportarPDF() {
  const { default: jsPDF }     = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'landscape' })
  doc.setFontSize(16)
  doc.text('Reporte de Créditos', 14, 16)
  doc.setFontSize(10)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-MX')}`, 14, 23)
  doc.text(`Total pendiente: $${formato(totalPendiente.value)} | Total pagado: $${formato(totalPagado.value)}`, 14, 29)

  autoTable(doc, {
    startY: 34,
    head: [['Crédito', 'Fecha', 'Cliente', 'Venta', 'Original', 'Pagado', 'Pendiente', 'Estado']],
    body: creditos.value.map(c => [
      `#${c.CreditoId}`, c.FechaCredito, c.NombreCliente, `#${c.VentaId}`,
      `$${formato(c.MontoTotal)}`, `$${formato(c.TotalPagado)}`,
      `$${formato(c.SaldoActual)}`, c.Estado
    ]),
    headStyles:         { fillColor: [26, 43, 60], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    styles:             { fontSize: 8 },
  })

  doc.save(`creditos_${new Date().toISOString().slice(0,10)}.pdf`)
}
</script>

<style scoped>
.reporte-creditos { max-width: 1200px; margin: 0 auto; }

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
.card__icono--azul2 { background: #eff6ff; color: #2563eb; }
.card__titulo { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitulo { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.p0 { padding: 0; }

/* Filtros */
.filtros-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 2fr auto;
  gap: 1rem;
  align-items: start;
}
.filtro-campo { display: flex; flex-direction: column; gap: 0.4rem; }
.filtro-campo label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.filtro-campo--cliente { position: relative; }
.checkbox-wrap { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.checkbox { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
.checkbox-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; cursor: pointer; }
.cliente-buscador { position: relative; }
.cliente-tag {
  display: inline-flex; align-items: center; gap: 6px; margin-top: 6px;
  padding: 4px 10px; background: #eff6ff; border-radius: 6px;
  font-size: 0.8rem; font-weight: 600; color: #2563eb; border: 1px solid #bfdbfe;
}
.btn-limpiar-tag { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 12px; padding: 0; }

.input {
  padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none;
  transition: border-color 0.2s; width: 100%; box-sizing: border-box;
}
.input:focus { border-color: #2563eb; background: #fff; }
.filtro-acciones { display: flex; gap: 8px; align-self: end; }
.btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.btn--primary { background: #1a2b3c; color: #fff; }
.btn--primary:hover:not(:disabled) { background: #2563eb; }
.btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }

/* Totales */
.totales-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 1rem; margin-bottom: 1.25rem;
}
.total-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 4px;
}
.total-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.total-valor { font-size: 1.3rem; font-weight: 800; color: #1a2b3c; }
.total-valor.verde { color: #16a34a; }
.total-valor.rojo  { color: #ef4444; }

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
.fila-credito { cursor: pointer; transition: background 0.15s; }
.fila-credito:hover { background: #f8fafc; }
.text-right  { text-align: right; }
.text-center { text-align: center; }
.credito-id { font-weight: 700; color: #2563eb; }
.verde { color: #16a34a; }
.rojo  { color: #ef4444; }

.badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.badge--verde { background: #f0fdf4; color: #16a34a; }
.badge--rojo  { background: #fef2f2; color: #ef4444; }
.badge--gris  { background: #f1f5f9; color: #64748b; }

.toggle-icon { display: inline-block; font-size: 0.8rem; color: #94a3b8; transition: transform 0.2s; }
.toggle-icon.abierto { transform: rotate(90deg); color: #2563eb; }

.fila-detalle td { background: #f8fafc; padding: 1rem; }
.abonos-wrap { padding: 0.5rem; }
.abonos-titulo { font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 0.75rem; }
.abonos-vacio { font-size: 0.85rem; color: #94a3b8; padding: 0.5rem 0; }
.tabla-abonos { width: 100%; border-collapse: collapse; font-size: 0.825rem; }
.tabla-abonos th {
  padding: 0.5rem 0.75rem; text-align: left; font-size: 0.72rem;
  font-weight: 700; color: #64748b; text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
}
.tabla-abonos td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; }

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