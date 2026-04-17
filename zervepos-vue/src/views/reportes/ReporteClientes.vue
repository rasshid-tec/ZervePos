<template>
  <div class="reporte-clientes">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/reportes')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/reportes" class="breadcrumb__link">Reportes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Compras por Cliente</span>
          </span>
          <h1 class="page-titulo">Compras por Cliente</h1>
        </div>
      </div>
      <div class="header-acciones">
        <button class="btn-export btn-export--excel" :disabled="!clientes.length" @click="exportarExcel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Excel
        </button>
        <button class="btn-export btn-export--pdf" :disabled="!clientes.length" @click="exportarPDF">
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
    <div v-if="clientes.length" class="totales-grid">
      <div class="total-card">
        <span class="total-label">Clientes</span>
        <span class="total-valor">{{ clientes.length }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Total ventas</span>
        <span class="total-valor">{{ totalVentas }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Monto total</span>
        <span class="total-valor verde">${{ formato(totalMonto) }}</span>
      </div>
      <div class="total-card">
        <span class="total-label">Deuda total</span>
        <span class="total-valor rojo">${{ formato(totalDeuda) }}</span>
      </div>
    </div>

    <!-- Resultados -->
    <div class="card tabla-card">
      <div class="card__header">
        <div class="card__icono card__icono--morado">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div>
          <h2 class="card__titulo">Resultados</h2>
          <p class="card__subtitulo">{{ clientes.length }} clientes encontrados</p>
        </div>
      </div>
      <div class="card__body p0">

        <div v-if="cargando" class="estado-carga">
          <span class="spinner"></span>
          <p>Cargando reporte...</p>
        </div>

        <div v-else-if="!clientes.length && buscado" class="empty-state">
          <div class="empty-state__icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3 class="empty-state__titulo">Sin resultados</h3>
          <p class="empty-state__desc">No hay clientes con los filtros seleccionados</p>
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
                <th>Cliente</th>
                <th>Negocio</th>
                <th>Teléfono</th>
                <th class="text-center">Ventas</th>
                <th class="text-right">Total compras</th>
                <th class="text-right">Deuda activa</th>
                <th>Última compra</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="c in clientes" :key="c.ClienteId">
                <tr class="fila-cliente" @click="toggleDetalle(c.ClienteId)">
                  <td>
                    <div class="cliente-cell">
                      <div class="cliente-avatar-sm">{{ c.NombreCliente.charAt(0) }}</div>
                      <span>{{ c.NombreCliente }}</span>
                    </div>
                  </td>
                  <td>{{ c.Negocio || '—' }}</td>
                  <td>{{ c.Telefono || '—' }}</td>
                  <td class="text-center"><span class="badge badge--azul">{{ c.TotalVentas }}</span></td>
                  <td class="text-right verde">${{ formato(c.MontoTotalCompras) }}</td>
                  <td class="text-right" :class="Number(c.DeudaActual) > 0 ? 'rojo' : ''">
                    ${{ formato(c.DeudaActual) }}
                  </td>
                  <td>{{ c.UltimaCompra || '—' }}</td>
                  <td class="text-center">
                    <span class="toggle-icon" :class="{ abierto: detalleAbierto === c.ClienteId }">▸</span>
                  </td>
                </tr>
                <!-- Ventas del cliente -->
                <tr v-if="detalleAbierto === c.ClienteId" class="fila-detalle">
                  <td colspan="8">
                    <div class="ventas-wrap">
                      <p class="ventas-titulo">Historial de compras</p>
                      <div v-if="ventasPorCliente(c.ClienteId).length === 0" class="ventas-vacio">
                        Sin compras registradas en este período
                      </div>
                      <table v-else class="tabla-ventas">
                        <thead>
                          <tr>
                            <th>Venta</th>
                            <th>Fecha</th>
                            <th>Sucursal</th>
                            <th>Cajero</th>
                            <th>Pagos</th>
                            <th class="text-right">Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="v in ventasPorCliente(c.ClienteId)" :key="v.VentaId">
                            <td><span class="venta-id">#{{ v.VentaId }}</span></td>
                            <td>{{ v.FechaVenta }}</td>
                            <td>{{ v.NombreSucursal }}</td>
                            <td>{{ v.Cajero }}</td>
                            <td><span class="pagos-text">{{ v.Pagos }}</span></td>
                            <td class="text-right verde">${{ formato(v.Total) }}</td>
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
const clientes = ref([])
const ventas   = ref([])
const detalleAbierto      = ref(null)
const filtrarPorCliente   = ref(false)
const clienteSeleccionado = ref(null)

const filtros = ref({
  fechaDesde: '',
  fechaHasta: '',
})

const formato        = (n) => Number(n || 0).toFixed(2)
const totalVentas    = computed(() => clientes.value.reduce((s, c) => s + Number(c.TotalVentas),       0))
const totalMonto     = computed(() => clientes.value.reduce((s, c) => s + Number(c.MontoTotalCompras), 0))
const totalDeuda     = computed(() => clientes.value.reduce((s, c) => s + Number(c.DeudaActual),       0))

const ventasPorCliente = (clienteId) => ventas.value.filter(v => v.ClienteId === clienteId)

function toggleDetalle(id) {
  detalleAbierto.value = detalleAbierto.value === id ? null : id
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
  clientes.value = []
  ventas.value   = []
  try {
    const res  = await fetch('/php/reporte_clientes.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        fechaDesde: filtros.value.fechaDesde || null,
        fechaHasta: filtros.value.fechaHasta || null,
        clienteId:  clienteSeleccionado.value?.ClienteId || null,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      clientes.value = data.clientes
      ventas.value   = data.ventas
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
  filtros.value             = { fechaDesde: '', fechaHasta: '' }
  filtrarPorCliente.value   = false
  clienteSeleccionado.value = null
  clientes.value            = []
  ventas.value              = []
  buscado.value             = false
  detalleAbierto.value      = null
}

/* ── Exportar Excel ──────────────────────────────────────────── */
async function exportarExcel() {
  const ExcelJS = (await import('exceljs')).default
  const wb      = new ExcelJS.Workbook()
  const ws      = wb.addWorksheet('Clientes')

  ws.columns = [
    { header: 'Cliente',       key: 'NombreCliente',    width: 24 },
    { header: 'Negocio',       key: 'Negocio',          width: 20 },
    { header: 'Teléfono',      key: 'Telefono',         width: 16 },
    { header: 'Total Ventas',  key: 'TotalVentas',      width: 14 },
    { header: 'Total Compras', key: 'MontoTotalCompras', width: 16 },
    { header: 'Deuda Activa',  key: 'DeudaActual',      width: 14 },
    { header: 'Última Compra', key: 'UltimaCompra',     width: 16 },
  ]

  ws.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })

  clientes.value.forEach(c => ws.addRow(c))

  const ws2 = wb.addWorksheet('Detalle Ventas')
  ws2.columns = [
    { header: 'Venta',    key: 'VentaId',        width: 10 },
    { header: 'Cliente',  key: 'ClienteId',      width: 10 },
    { header: 'Fecha',    key: 'FechaVenta',     width: 14 },
    { header: 'Sucursal', key: 'NombreSucursal', width: 20 },
    { header: 'Cajero',   key: 'Cajero',         width: 24 },
    { header: 'Pagos',    key: 'Pagos',          width: 30 },
    { header: 'Total',    key: 'Total',          width: 14 },
  ]
  ws2.getRow(1).eachCell(cell => {
    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' } }
    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A2B3C' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center' }
  })
  ventas.value.forEach(v => ws2.addRow(v))

  const buffer = await wb.xlsx.writeBuffer()
  const blob   = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
  const url    = URL.createObjectURL(blob)
  const a      = document.createElement('a')
  a.href       = url
  a.download   = `clientes_${new Date().toISOString().slice(0,10)}.xlsx`
  a.click()
  URL.revokeObjectURL(url)
}

/* ── Exportar PDF ────────────────────────────────────────────── */
async function exportarPDF() {
  const { default: jsPDF }     = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'landscape' })
  doc.setFontSize(16)
  doc.text('Reporte de Compras por Cliente', 14, 16)
  doc.setFontSize(10)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-MX')}`, 14, 23)

  autoTable(doc, {
    startY: 28,
    head: [['Cliente', 'Negocio', 'Ventas', 'Total Compras', 'Deuda Activa', 'Última Compra']],
    body: clientes.value.map(c => [
      c.NombreCliente, c.Negocio || '—', c.TotalVentas,
      `$${formato(c.MontoTotalCompras)}`, `$${formato(c.DeudaActual)}`,
      c.UltimaCompra || '—'
    ]),
    headStyles:         { fillColor: [26, 43, 60], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    styles:             { fontSize: 8 },
  })

  doc.save(`clientes_${new Date().toISOString().slice(0,10)}.pdf`)
}
</script>

<style scoped>
.reporte-clientes { max-width: 1200px; margin: 0 auto; }

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
.card__icono--azul   { background: #1a2b3c; color: #fff; }
.card__icono--morado { background: #faf5ff; color: #7c3aed; }
.card__titulo { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitulo { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.p0 { padding: 0; }

/* Filtros */
.filtros-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 2fr auto;
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
.fila-cliente { cursor: pointer; transition: background 0.15s; }
.fila-cliente:hover { background: #f8fafc; }
.text-right  { text-align: right; }
.text-center { text-align: center; }

.cliente-cell { display: flex; align-items: center; gap: 8px; }
.cliente-avatar-sm {
  width: 30px; height: 30px; background: #1a2b3c; color: #fff;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 700; flex-shrink: 0; text-transform: uppercase;
}

.verde { color: #16a34a; }
.rojo  { color: #ef4444; }

.badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.badge--azul { background: #eff6ff; color: #2563eb; }

.toggle-icon { display: inline-block; font-size: 0.8rem; color: #94a3b8; transition: transform 0.2s; }
.toggle-icon.abierto { transform: rotate(90deg); color: #2563eb; }

.fila-detalle td { background: #f8fafc; padding: 1rem; }
.ventas-wrap { padding: 0.5rem; }
.ventas-titulo { font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 0.75rem; }
.ventas-vacio { font-size: 0.85rem; color: #94a3b8; padding: 0.5rem 0; }
.tabla-ventas { width: 100%; border-collapse: collapse; font-size: 0.825rem; }
.tabla-ventas th {
  padding: 0.5rem 0.75rem; text-align: left; font-size: 0.72rem;
  font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;
}
.tabla-ventas td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; }
.venta-id { font-weight: 700; color: #2563eb; }
.pagos-text { font-size: 0.78rem; color: #64748b; }

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