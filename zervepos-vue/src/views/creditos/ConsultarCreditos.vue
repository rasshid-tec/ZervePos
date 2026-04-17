<template>
  <div class="consultar-creditos">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/creditos')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/creditos" class="breadcrumb__link">Créditos</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Consultar</span>
          </span>
          <h1 class="page-titulo">Consultar Créditos</h1>
        </div>
      </div>
      <button class="btn-filtrar" @click="mostrarFiltros = !mostrarFiltros">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
        {{ mostrarFiltros ? 'Ocultar filtros' : 'Filtrar' }}
      </button>
    </div>

    <!-- Filtros -->
    <Transition name="filtros">
      <div v-if="mostrarFiltros" class="card filtros-card">
        <div class="filtros-grid">
          <div class="filtro-campo">
            <label>Cliente</label>
            <input v-model="filtros.cliente" type="text" placeholder="Nombre del cliente..." class="input" />
          </div>
          <div class="filtro-campo">
            <label>Deuda mínima</label>
            <input v-model.number="filtros.deudaMin" type="number" min="0" placeholder="$0.00" class="input" />
          </div>
          <div class="filtro-campo">
            <label>Deuda máxima</label>
            <input v-model.number="filtros.deudaMax" type="number" min="0" placeholder="$0.00" class="input" />
          </div>
          <div class="filtro-campo">
            <label>Desde</label>
            <input v-model="filtros.fechaDesde" type="date" class="input" />
          </div>
          <div class="filtro-campo">
            <label>Hasta</label>
            <input v-model="filtros.fechaHasta" type="date" class="input" />
          </div>
          <div class="filtro-acciones">
            <button class="btn btn--secondary" @click="limpiarFiltros">Limpiar</button>
            <button class="btn btn--primary" @click="aplicarFiltros">Aplicar</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Cargando -->
    <div v-if="cargando" class="estado-carga">
      <span class="spinner"></span>
      <p>Cargando clientes con crédito...</p>
    </div>

    <!-- Sin resultados -->
    <div v-else-if="clientesFiltrados.length === 0" class="empty-state">
      <div class="empty-state__icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
        </svg>
      </div>
      <h3 class="empty-state__titulo">Sin resultados</h3>
      <p class="empty-state__desc">No hay clientes con créditos activos que coincidan con los filtros</p>
    </div>

    <!-- Lista de clientes con crédito -->
    <div v-else class="clientes-grid">
      <div
        v-for="c in clientesFiltrados"
        :key="c.ClienteId"
        class="cliente-card"
        @click="verDetalle(c)"
      >
        <div class="cliente-card__top">
          <div class="cliente-avatar">
            {{ c.NombreCliente.charAt(0).toUpperCase() }}
          </div>
          <div class="cliente-datos">
            <h3 class="cliente-nombre">{{ c.NombreCliente }}</h3>
            <p class="cliente-negocio">{{ c.Negocio || 'Sin negocio' }}</p>
          </div>
        </div>

        <div class="cliente-card__montos">
          <div class="monto-item">
            <span class="monto-label">Límite</span>
            <span class="monto-valor">${{ formato(c.LimiteCredito) }}</span>
          </div>
          <div class="monto-item">
            <span class="monto-label">Deuda</span>
            <span class="monto-valor rojo">${{ formato(c.TotalDeuda) }}</span>
          </div>
          <div class="monto-item">
            <span class="monto-label">Disponible</span>
            <span class="monto-valor verde">${{ formato(c.CreditoDisponible) }}</span>
          </div>
        </div>

        <div class="barra-wrap">
          <div
            class="barra-progreso"
            :style="{ width: porcentajeUsado(c) + '%' }"
            :class="{ 'barra-peligro': porcentajeUsado(c) >= 80 }"
          ></div>
        </div>
        <div class="barra-info">
          <span>{{ porcentajeUsado(c) }}% utilizado</span>
          <span>{{ c.NumCreditos }} crédito(s)</span>
        </div>
      </div>
    </div>

    <!-- Modal detalle cliente -->
    <Transition name="modal">
      <div v-if="clienteDetalle" class="modal-overlay" @click.self="clienteDetalle = null">
        <div class="modal">
          <div class="modal__header">
            <div class="cliente-avatar modal-avatar">
              {{ clienteDetalle.NombreCliente.charAt(0).toUpperCase() }}
            </div>
            <div>
              <h2 class="modal__titulo">{{ clienteDetalle.NombreCliente }}</h2>
              <p class="modal__sub">{{ clienteDetalle.Negocio || 'Sin negocio' }}</p>
            </div>
            <button class="modal__close" @click="clienteDetalle = null">✕</button>
          </div>

          <div class="modal__resumen">
            <div class="resumen-item">
              <span class="resumen-label">Límite</span>
              <span class="resumen-valor">${{ formato(clienteDetalle.LimiteCredito) }}</span>
            </div>
            <div class="resumen-item">
              <span class="resumen-label">Deuda total</span>
              <span class="resumen-valor rojo">${{ formato(clienteDetalle.TotalDeuda) }}</span>
            </div>
            <div class="resumen-item">
              <span class="resumen-label">Disponible</span>
              <span class="resumen-valor verde">${{ formato(clienteDetalle.CreditoDisponible) }}</span>
            </div>
          </div>

          <!-- Créditos del cliente -->
          <div v-if="cargandoDetalle" class="estado-carga">
            <span class="spinner"></span>
          </div>

          <div v-else class="creditos-detalle">
            <div v-for="cr in creditosDetalle" :key="cr.CreditoId" class="credito-item">
              <div class="credito-item__header">
                <span class="credito-badge">Crédito #{{ cr.CreditoId }}</span>
                <span class="credito-fecha">{{ cr.FechaCredito }}</span>
              </div>
              <div class="credito-item__body">
                <div class="credito-dato">
                  <span class="credito-dato__label">Venta origen</span>
                  <span class="credito-dato__valor">#{{ cr.VentaId }}</span>
                </div>
                <div class="credito-dato">
                  <span class="credito-dato__label">Original</span>
                  <span class="credito-dato__valor">${{ formato(cr.MontoTotal) }}</span>
                </div>
                <div class="credito-dato">
                  <span class="credito-dato__label">Pendiente</span>
                  <span class="credito-dato__valor rojo">${{ formato(cr.SaldoActual) }}</span>
                </div>
                <div class="credito-dato">
                  <span class="credito-dato__label">Pagado</span>
                  <span class="credito-dato__valor verde">${{ formato(cr.MontoTotal - cr.SaldoActual) }}</span>
                </div>
              </div>
              <div class="barra-wrap">
                <div
                  class="barra-progreso"
                  :style="{ width: porcentajePagado(cr) + '%' }"
                ></div>
              </div>
              <p class="credito-pct">{{ porcentajePagado(cr) }}% pagado</p>
            </div>
          </div>

          <div class="modal__footer">
            <button class="btn btn--secondary" @click="clienteDetalle = null">Cerrar</button>
            <button class="btn btn--primary" @click="irAPagar(clienteDetalle)">Registrar pago</button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter }                from 'vue-router'
import { useToast }                 from '../../composables/useToast'

const router = useRouter()
const toast  = useToast()

const cargando       = ref(false)
const clientes       = ref([])
const clienteDetalle = ref(null)
const creditosDetalle = ref([])
const cargandoDetalle = ref(false)
const mostrarFiltros = ref(false)

const filtros = ref({
  cliente:     '',
  deudaMin:    null,
  deudaMax:    null,
  fechaDesde:  '',
  fechaHasta:  '',
})

const filtrosAplicados = ref({ ...filtros.value })

const formato = (n) => Number(n || 0).toFixed(2)

const porcentajeUsado  = (c) => {
  if (!c.LimiteCredito) return 0
  return Math.min(100, Math.round((c.TotalDeuda / c.LimiteCredito) * 100))
}

const porcentajePagado = (cr) => {
  if (!cr.MontoTotal) return 0
  return Math.min(100, Math.round(((cr.MontoTotal - cr.SaldoActual) / cr.MontoTotal) * 100))
}

const clientesFiltrados = computed(() => {
  return clientes.value.filter(c => {
    if (filtrosAplicados.value.cliente) {
      const busq = filtrosAplicados.value.cliente.toLowerCase()
      if (!c.NombreCliente.toLowerCase().includes(busq)) return false
    }
    if (filtrosAplicados.value.deudaMin !== null && filtrosAplicados.value.deudaMin !== '') {
      if (Number(c.TotalDeuda) < filtrosAplicados.value.deudaMin) return false
    }
    if (filtrosAplicados.value.deudaMax !== null && filtrosAplicados.value.deudaMax !== '') {
      if (Number(c.TotalDeuda) > filtrosAplicados.value.deudaMax) return false
    }
    return true
  })
})

async function cargarClientes() {
  cargando.value = true
  try {
    const res  = await fetch('/php/obtener_clientes_credito.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({})
    })
    const data = await res.json()
    if (data.status === 1) {
      clientes.value = data.clientes
    } else {
      toast.error('Error al cargar clientes')
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    cargando.value = false
  }
}

async function verDetalle(c) {
  clienteDetalle.value  = c
  creditosDetalle.value = []
  cargandoDetalle.value = true
  try {
    const res  = await fetch('/php/obtener_creditos_cliente.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ clienteId: c.ClienteId })
    })
    const data = await res.json()
    if (data.status === 1) {
      creditosDetalle.value = data.creditos
    }
  } catch {
    toast.error('Error al cargar detalle')
  } finally {
    cargandoDetalle.value = false
  }
}

function irAPagar(c) {
  clienteDetalle.value = null
  router.push({ name: 'creditos-pagar', query: { clienteId: c.ClienteId, nombre: c.NombreCliente } })
}

function aplicarFiltros() {
  filtrosAplicados.value = { ...filtros.value }
}

function limpiarFiltros() {
  filtros.value = { cliente: '', deudaMin: null, deudaMax: null, fechaDesde: '', fechaHasta: '' }
  filtrosAplicados.value = { ...filtros.value }
}

onMounted(cargarClientes)
</script>

<style scoped>
.consultar-creditos { max-width: 1100px; margin: 0 auto; }

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
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

.btn-filtrar {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 16px; background: #fff; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 0.9rem; font-weight: 600; color: #1a2b3c;
  cursor: pointer; transition: all 0.2s;
}
.btn-filtrar svg { width: 16px; height: 16px; }
.btn-filtrar:hover { background: #f1f5f9; border-color: #2563eb; color: #2563eb; }

/* Filtros */
.filtros-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 1.25rem; margin-bottom: 1.25rem;
}
.filtros-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr) repeat(2, 1fr) auto;
  gap: 1rem;
  align-items: end;
}
.filtro-campo { display: flex; flex-direction: column; gap: 0.4rem; }
.filtro-campo label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.input {
  padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none;
}
.input:focus { border-color: #2563eb; background: #fff; }
.filtro-acciones { display: flex; gap: 8px; }

.btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; }
.btn--primary { background: #1a2b3c; color: #fff; }
.btn--primary:hover { background: #2563eb; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }

/* Grid clientes */
.clientes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.cliente-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 1.25rem; cursor: pointer; transition: all 0.2s;
}
.cliente-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-color: #2563eb; transform: translateY(-2px); }

.cliente-card__top { display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1rem; }
.cliente-avatar {
  width: 44px; height: 44px; background: #1a2b3c; color: #fff;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 700; flex-shrink: 0;
}
.cliente-nombre { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0 0 2px; }
.cliente-negocio { font-size: 0.8rem; color: #94a3b8; margin: 0; }

.cliente-card__montos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 0.875rem; }
.monto-item { display: flex; flex-direction: column; gap: 2px; }
.monto-label { font-size: 0.68rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
.monto-valor { font-size: 0.9rem; font-weight: 700; color: #1a2b3c; }
.monto-valor.rojo  { color: #ef4444; }
.monto-valor.verde { color: #16a34a; }

.barra-wrap { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 4px; }
.barra-progreso { height: 100%; background: #2563eb; border-radius: 999px; transition: width 0.3s; }
.barra-peligro { background: #ef4444; }
.barra-info { display: flex; justify-content: space-between; font-size: 0.72rem; color: #94a3b8; }

/* Empty state */
.empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 4rem 2rem; gap: 0.75rem;
  text-align: center; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
}
.empty-state__icono {
  width: 64px; height: 64px; background: #eff6ff; border-radius: 16px;
  display: flex; align-items: center; justify-content: center; color: #2563eb;
}
.empty-state__icono svg { width: 32px; height: 32px; }
.empty-state__titulo { font-size: 1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.empty-state__desc { font-size: 0.875rem; color: #94a3b8; margin: 0; max-width: 300px; line-height: 1.6; }

/* Estado carga */
.estado-carga { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 3rem; color: #64748b; }
.spinner {
  width: 28px; height: 28px; border: 3px solid #e2e8f0;
  border-top-color: #2563eb; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal {
  background: #fff; border-radius: 16px; width: 560px; max-width: 90vw;
  max-height: 85vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.15);
}
.modal__header {
  display: flex; align-items: center; gap: 1rem;
  padding: 1.25rem; border-bottom: 1px solid #f1f5f9; position: sticky; top: 0; background: #fff;
}
.modal-avatar { width: 44px; height: 44px; font-size: 1rem; }
.modal__titulo { font-size: 1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.modal__sub { font-size: 0.8rem; color: #94a3b8; margin: 0; }
.modal__close { background: none; border: none; font-size: 1rem; color: #94a3b8; cursor: pointer; margin-left: auto; }

.modal__resumen {
  display: flex; gap: 1.5rem; padding: 1rem 1.25rem;
  background: #f8fafc; border-bottom: 1px solid #f1f5f9;
}
.resumen-item { display: flex; flex-direction: column; gap: 2px; }
.resumen-label { font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
.resumen-valor { font-size: 1rem; font-weight: 700; color: #1a2b3c; }
.resumen-valor.rojo  { color: #ef4444; }
.resumen-valor.verde { color: #16a34a; }

.creditos-detalle { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }

.credito-item { background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; }
.credito-item__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.credito-badge { background: #eff6ff; color: #2563eb; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.credito-fecha { font-size: 0.75rem; color: #94a3b8; }
.credito-item__body { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 0.75rem; }
.credito-dato { display: flex; flex-direction: column; gap: 2px; }
.credito-dato__label { font-size: 0.68rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
.credito-dato__valor { font-size: 0.875rem; font-weight: 700; color: #1a2b3c; }
.credito-dato__valor.rojo  { color: #ef4444; }
.credito-dato__valor.verde { color: #16a34a; }
.credito-pct { font-size: 0.72rem; color: #94a3b8; margin: 4px 0 0; text-align: right; }

.modal__footer {
  display: flex; gap: 0.75rem; justify-content: flex-end;
  padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;
  position: sticky; bottom: 0; background: #fff;
}

/* Transiciones */
.filtros-enter-active, .filtros-leave-active { transition: opacity 0.2s, transform 0.2s; }
.filtros-enter-from, .filtros-leave-to { opacity: 0; transform: translateY(-8px); }
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>