<template>
  <div class="fondo">
    <div class="contenedor">

      <div class="encabezado">
        <span class="brand-icon" v-html="ICONS.bolt"></span>
        <h1 class="brand-name">ZervePOS</h1>
      </div>

      <div v-if="cargando" class="estado">
        <span class="spinner"></span>
        <p>Cargando resumen de caja...</p>
      </div>

      <template v-else>
        <div class="caja-card">

          <div class="caja-header">
            <div class="caja-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            </div>
            <div>
              <h2 class="caja-titulo">Cierre de Caja</h2>
              <p class="caja-sucursal">{{ resumen.general?.NombreSucursal }}</p>
              <p class="caja-cajero">Cajero: {{ resumen.general?.Cajero }}</p>
            </div>
          </div>

          <!-- Resumen del día -->
          <div class="resumen-section">
            <h3 class="section-titulo">Resumen del día</h3>

            <div class="resumen-grid">
              <div class="resumen-item">
                <span class="resumen-label">Monto inicial</span>
                <span class="resumen-valor">${{ formato(resumen.general?.MontoInicial) }}</span>
              </div>
              <div class="resumen-item">
                <span class="resumen-label">Total ventas</span>
                <span class="resumen-valor verde">${{ formato(resumen.totales?.MontoTotalVentas) }}</span>
              </div>
              <div class="resumen-item">
                <span class="resumen-label">Núm. ventas</span>
                <span class="resumen-valor">{{ resumen.totales?.TotalVentas || 0 }}</span>
              </div>
            </div>

            <div class="metodos-grid">
              <div class="metodo-item">
                <div class="metodo-icono" style="background:#f0fdf4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <span class="metodo-label">Efectivo</span>
                <span class="metodo-valor">${{ formato(resumen.totales?.TotalEfectivo) }}</span>
              </div>
              <div class="metodo-item">
                <div class="metodo-icono" style="background:#eff6ff">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <span class="metodo-label">Tarjeta</span>
                <span class="metodo-valor">${{ formato(resumen.totales?.TotalTarjeta) }}</span>
              </div>
              <div class="metodo-item">
                <div class="metodo-icono" style="background:#fdf4ff">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 11 21 7 17 3"/><line x1="21" y1="7" x2="9" y2="7"/><polyline points="7 13 3 17 7 21"/><line x1="3" y1="17" x2="15" y2="17"/></svg>
                </div>
                <span class="metodo-label">Transferencia</span>
                <span class="metodo-valor">${{ formato(resumen.totales?.TotalTransferencia) }}</span>
              </div>
              <div class="metodo-item">
                <div class="metodo-icono" style="background:#fff7ed">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <span class="metodo-label">Crédito</span>
                <span class="metodo-valor">${{ formato(resumen.totales?.TotalCredito) }}</span>
              </div>
            </div>

            <div v-if="resumen.movimientos?.length" class="movimientos">
              <h4 class="movimientos-titulo">Movimientos de caja</h4>
              <div
                v-for="m in resumen.movimientos"
                :key="m.MovimientoId"
                class="movimiento-row"
                :class="m.TipoMovimiento === 'Entrada' ? 'entrada' : 'salida'"
              >
                <div class="mov-icono">
                  <svg v-if="m.TipoMovimiento === 'Entrada'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
                </div>
                <span class="mov-tipo">{{ m.TipoMovimiento }}</span>
                <span class="mov-motivo">{{ m.Motivo || '—' }}</span>
                <span class="mov-monto">${{ formato(m.Monto) }}</span>
              </div>
            </div>
          </div>

          <!-- Conteo de efectivo -->
          <div class="efectivo-section">
            <div class="efectivo-header">
              <div class="efectivo-icono">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
              </div>
              <div>
                <h3 class="efectivo-titulo">Conteo de efectivo</h3>
                <p class="efectivo-subtitulo">Cuenta el dinero físico en caja</p>
              </div>
            </div>

            <div class="tabla-denominaciones">
              <div class="tabla-header">
                <span>Denominación</span>
                <span>Cantidad</span>
                <span>Subtotal</span>
              </div>

              <div class="grupo-label">Billetes</div>
              <div v-for="den in billetes" :key="den.valor" class="tabla-fila">
                <div class="den-info">
                  <span class="den-badge billete">${{ den.etiqueta }}</span>
                  <span class="den-tipo">billete</span>
                </div>
                <input
                  v-model.number="den.cantidad"
                  type="number" min="0"
                  class="input-cantidad"
                  @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
                />
                <span class="den-subtotal">
                  {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
                </span>
              </div>

              <div class="grupo-label">Monedas</div>
              <div v-for="den in monedas" :key="den.valor" class="tabla-fila">
                <div class="den-info">
                  <span class="den-badge moneda">${{ den.etiqueta }}</span>
                  <span class="den-tipo">moneda</span>
                </div>
                <input
                  v-model.number="den.cantidad"
                  type="number" min="0"
                  class="input-cantidad"
                  @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
                />
                <span class="den-subtotal">
                  {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
                </span>
              </div>
            </div>

            <div class="total-efectivo">
              <span>Total contado</span>
              <span class="total-valor">${{ totalContado }}</span>
            </div>
          </div>

          <!-- Diferencia -->
          <div class="diferencia-section" :class="diferenciaClass">
            <div class="diferencia-row">
              <span>Efectivo esperado</span>
              <strong>${{ formato(efectivoEsperado) }}</strong>
            </div>
            <div class="diferencia-row">
              <span>Total contado</span>
              <strong>${{ totalContado }}</strong>
            </div>
            <div class="diferencia-row diferencia-total">
              <div class="diferencia-label">
                <svg v-if="diferencia >= 0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>{{ diferencia >= 0 ? 'Sobrante' : 'Faltante' }}</span>
              </div>
              <strong>${{ formato(Math.abs(diferencia)) }}</strong>
            </div>
          </div>

          <div v-if="errorMsg" class="error-msg">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ errorMsg }}
          </div>

          <div class="acciones">
            <button class="btn btn-cancelar" @click="$router.push('/app/ventas')">
              Cancelar
            </button>
            <button class="btn btn-cerrar" :disabled="cerrando" @click="cerrarCaja">
              <span v-if="!cerrando">Cerrar Caja</span>
              <span v-else class="btn-loading">
                <span class="spinner-btn"></span>
                Cerrando...
              </span>
            </button>
          </div>

        </div>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter }                from 'vue-router'
import { ICONS }                    from '../utils/icons'

const router   = useRouter()
const cargando = ref(true)
const cerrando = ref(false)
const errorMsg = ref('')
const cajaId   = ref(Number(sessionStorage.getItem('cajaId') || 0))

const resumen = ref({
  general:     null,
  metodos:     [],
  movimientos: [],
  totales:     null,
})

const billetes = ref([
  { valor: 1000, etiqueta: '1000', cantidad: 0 },
  { valor: 500,  etiqueta: '500',  cantidad: 0 },
  { valor: 200,  etiqueta: '200',  cantidad: 0 },
  { valor: 100,  etiqueta: '100',  cantidad: 0 },
  { valor: 50,   etiqueta: '50',   cantidad: 0 },
  { valor: 20,   etiqueta: '20',   cantidad: 0 },
])

const monedas = ref([
  { valor: 10,   etiqueta: '10',   cantidad: 0 },
  { valor: 5,    etiqueta: '5',    cantidad: 0 },
  { valor: 2,    etiqueta: '2',    cantidad: 0 },
  { valor: 1,    etiqueta: '1',    cantidad: 0 },
  { valor: 0.50, etiqueta: '0.50', cantidad: 0 },
])

const formato = (n) => Number(n || 0).toFixed(2)

const totalContado = computed(() => {
  const tb = billetes.value.reduce((s, d) => s + d.valor * (d.cantidad || 0), 0)
  const tm = monedas.value.reduce((s, d)  => s + d.valor * (d.cantidad || 0), 0)
  return (tb + tm).toFixed(2)
})

const efectivoEsperado = computed(() => {
  const inicial  = Number(resumen.value.general?.MontoInicial  || 0)
  const efectivo = Number(resumen.value.totales?.TotalEfectivo || 0)
  const entradas = resumen.value.movimientos
    .filter(m => m.TipoMovimiento === 'Entrada')
    .reduce((s, m) => s + Number(m.Monto), 0)
  const salidas  = resumen.value.movimientos
    .filter(m => m.TipoMovimiento === 'Salida')
    .reduce((s, m) => s + Number(m.Monto), 0)
  return inicial + efectivo + entradas - salidas
})

const diferencia = computed(() => Number(totalContado.value) - efectivoEsperado.value)

const diferenciaClass = computed(() => {
  if (diferencia.value > 0) return 'diferencia--sobrante'
  if (diferencia.value < 0) return 'diferencia--faltante'
  return 'diferencia--exacto'
})

async function cargarResumen() {
  cargando.value = true
  try {
    const res  = await fetch('/php/resumen_caja.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ cajaId: cajaId.value })
    })
    const data = await res.json()
    if (data.status === 1) {
      resumen.value = {
        general:     data.general,
        metodos:     data.metodos     || [],
        movimientos: data.movimientos || [],
        totales:     data.totales,
      }
    } else {
      errorMsg.value = 'Error al cargar resumen de caja'
    }
  } catch {
    errorMsg.value = 'Error de conexión'
  } finally {
    cargando.value = false
  }
}

async function cerrarCaja() {
  errorMsg.value = ''
  cerrando.value = true
  try {
    const res  = await fetch('/php/cerrar_caja.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        cajaId:     cajaId.value,
        montoFinal: parseFloat(totalContado.value)
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      sessionStorage.removeItem('cajaId')
      router.push('/seleccionar-sucursal')
    } else {
      errorMsg.value = data.mensaje || 'Error al cerrar caja'
    }
  } catch {
    errorMsg.value = 'Error de conexión'
  } finally {
    cerrando.value = false
  }
}

onMounted(cargarResumen)
</script>

<style scoped>
.fondo {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f2535 0%, #1a3a4a 60%, #0f2535 100%);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 32px 16px;
}
.contenedor { width: 100%; max-width: 720px; }

.encabezado {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; margin-bottom: 32px;
}
.brand-icon { color: #f97316; display: flex; align-items: center; }
.brand-name { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0; }

.estado {
  display: flex; flex-direction: column; align-items: center;
  gap: 12px; color: rgba(255,255,255,0.6); padding: 60px 0;
}

.caja-card {
  background: #fff; border-radius: 16px; padding: 2rem;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}

.caja-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.caja-header-icon {
  width: 52px; height: 52px; background: #fef2f2;
  border-radius: 12px; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.caja-titulo   { margin: 0 0 4px; font-size: 1.25rem; font-weight: 700; color: #1a2b3c; }
.caja-sucursal { margin: 0; font-size: 0.875rem; color: #64748b; font-weight: 500; }
.caja-cajero   { margin: 0; font-size: 0.8rem; color: #94a3b8; }

/* Resumen */
.resumen-section {
  background: #f8fafc; border-radius: 12px;
  padding: 1.25rem; margin-bottom: 1.5rem;
}
.section-titulo { margin: 0 0 1rem; font-size: 0.95rem; font-weight: 700; color: #1a2b3c; }

.resumen-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 12px; margin-bottom: 1rem;
}
.resumen-item {
  background: #fff; border-radius: 8px; padding: 0.75rem;
  border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px;
}
.resumen-label { font-size: 0.72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; }
.resumen-valor { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; }
.resumen-valor.verde { color: #16a34a; }

.metodos-grid {
  display: grid; grid-template-columns: repeat(2, 1fr);
  gap: 8px; margin-bottom: 1rem;
}
.metodo-item {
  display: flex; align-items: center; gap: 10px;
  background: #fff; border-radius: 8px;
  padding: 0.6rem 0.875rem; border: 1px solid #e2e8f0;
}
.metodo-icono {
  width: 28px; height: 28px; border-radius: 6px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.metodo-label { flex: 1; font-size: 0.85rem; color: #64748b; }
.metodo-valor { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; }

.movimientos { margin-top: 0.75rem; }
.movimientos-titulo {
  font-size: 0.72rem; font-weight: 700; color: #64748b;
  text-transform: uppercase; margin: 0 0 0.5rem;
}
.movimiento-row {
  display: flex; align-items: center; gap: 10px;
  padding: 0.5rem 0.75rem; border-radius: 6px;
  margin-bottom: 4px; font-size: 0.85rem;
}
.movimiento-row.entrada { background: #f0fdf4; }
.movimiento-row.salida  { background: #fef2f2; }
.mov-icono { display: flex; align-items: center; flex-shrink: 0; }
.mov-tipo { font-weight: 600; min-width: 70px; }
.movimiento-row.entrada .mov-tipo { color: #16a34a; }
.movimiento-row.salida  .mov-tipo { color: #dc2626; }
.mov-motivo { flex: 1; color: #64748b; }
.mov-monto { font-weight: 700; color: #1a2b3c; }

/* Efectivo */
.efectivo-section {
  background: #f8fafc; border-radius: 12px;
  padding: 1.5rem; margin-bottom: 1.5rem;
}
.efectivo-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; }
.efectivo-icono {
  width: 40px; height: 40px; background: #f0fdf4;
  border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.efectivo-titulo   { margin: 0 0 2px; font-size: 1rem; font-weight: 700; color: #1a2b3c; }
.efectivo-subtitulo { margin: 0; font-size: 0.8rem; color: #64748b; }

.tabla-denominaciones { margin-bottom: 1rem; }
.tabla-header {
  display: grid; grid-template-columns: 1fr 140px 120px;
  padding: 0.5rem 0.75rem; font-size: 0.72rem; font-weight: 700;
  color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 2px solid #e2e8f0; margin-bottom: 0.5rem;
}
.grupo-label {
  font-size: 0.72rem; font-weight: 700; color: #94a3b8;
  text-transform: uppercase; letter-spacing: 0.05em;
  padding: 0.75rem 0.75rem 0.25rem;
}
.tabla-fila {
  display: grid; grid-template-columns: 1fr 140px 120px;
  align-items: center; padding: 0.6rem 0.75rem;
  border-bottom: 1px solid #e2e8f0; transition: background 0.15s; border-radius: 6px;
}
.tabla-fila:hover { background: #f1f5f9; }
.den-info { display: flex; align-items: center; gap: 0.75rem; }
.den-badge {
  display: inline-block; padding: 0.25rem 0.6rem; border-radius: 6px;
  font-weight: 700; font-size: 0.8rem; min-width: 56px; text-align: center;
}
.den-badge.billete { background: #fff4ed; color: #f97316; }
.den-badge.moneda  { background: #f0fdf4; color: #16a34a; }
.den-tipo { font-size: 0.8rem; color: #94a3b8; }
.input-cantidad {
  width: 100px; padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; text-align: center; background: #fff;
  color: #1a2b3c; outline: none; transition: all 0.2s;
}
.input-cantidad:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
.input-cantidad::-webkit-inner-spin-button,
.input-cantidad::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.den-subtotal { text-align: right; font-weight: 600; font-size: 0.875rem; color: #1a2b3c; }
.total-efectivo {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 0.75rem 0; border-top: 2px solid #e2e8f0;
  margin-top: 0.5rem; font-weight: 600; font-size: 0.95rem; color: #1a2b3c;
}
.total-valor { font-size: 1.25rem; font-weight: 700; color: #f97316; }

/* Diferencia */
.diferencia-section {
  border-radius: 12px; padding: 1rem 1.25rem;
  margin-bottom: 1.5rem; border: 1px solid transparent;
}
.diferencia--exacto   { background: #f0fdf4; border-color: #bbf7d0; }
.diferencia--sobrante { background: #eff6ff; border-color: #bfdbfe; }
.diferencia--faltante { background: #fef2f2; border-color: #fecaca; }
.diferencia-row {
  display: flex; justify-content: space-between;
  align-items: center; font-size: 0.9rem;
  padding: 4px 0; color: #1a2b3c;
}
.diferencia-total {
  border-top: 1px dashed #e2e8f0;
  margin-top: 8px; padding-top: 8px;
  font-size: 1rem; font-weight: 700;
}
.diferencia-label { display: flex; align-items: center; gap: 6px; }

/* Error */
.error-msg {
  display: flex; align-items: center; gap: 8px;
  color: #dc2626; font-size: 0.875rem; margin: 0 0 1rem;
  padding: 0.75rem 1rem; background: #fef2f2;
  border-radius: 8px; border-left: 3px solid #dc2626;
}

/* Acciones */
.acciones { display: flex; gap: 0.75rem; justify-content: flex-end; }
.btn {
  padding: 0.75rem 1.5rem; border-radius: 8px;
  font-size: 0.95rem; font-weight: 600;
  cursor: pointer; border: none; transition: all 0.2s;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancelar { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn-cancelar:hover { background: #f1f5f9; }
.btn-cerrar { background: #dc2626; color: #fff; }
.btn-cerrar:hover:not(:disabled) { background: #b91c1c; }
.btn-loading { display: flex; align-items: center; gap: 8px; }

.spinner {
  width: 28px; height: 28px;
  border: 3px solid rgba(255,255,255,0.2);
  border-top-color: #4fc3f7;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
.spinner-btn {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>