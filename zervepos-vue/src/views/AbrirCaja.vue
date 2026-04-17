<template>
  <div class="fondo">
    <div class="contenedor">

      <div class="encabezado">
        <span class="brand-icon" v-html="ICONS.bolt"></span>
        <h1 class="brand-name">ZervePOS</h1>
      </div>

      <div class="caja-card">
        <div class="caja-header">
          <div class="caja-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
          </div>
          <div>
            <h2 class="caja-titulo">Apertura de Caja</h2>
            <p class="caja-sucursal">{{ sucursalNombre || 'Sucursal Asignada' }}</p>
          </div>
        </div>

        <p class="caja-descripcion">
          No hay una caja abierta en esta sucursal. Ingresa el fondo inicial contando las denominaciones disponibles.
        </p>

        <div class="efectivo-section">
          <div class="efectivo-header">
            <div class="efectivo-icono">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
            </div>
            <div>
              <h3 class="efectivo-titulo">Efectivo</h3>
              <p class="efectivo-subtitulo">Fondo inicial de caja</p>
            </div>
          </div>

          <div class="tabla-denominaciones">
            <div class="tabla-header">
              <span>Denominación</span>
              <span>Cantidad</span>
              <span>Subtotal</span>
            </div>

            <div class="grupo-label">Billetes</div>
            <div
              v-for="den in billetes"
              :key="'b-' + den.valor"
              class="tabla-fila"
            >
              <div class="den-info">
                <span class="den-badge billete">${{ den.etiqueta }}</span>
                <span class="den-tipo">billete</span>
              </div>
              <input
                v-model.number="den.cantidad"
                type="number"
                min="0"
                class="input-cantidad"
                placeholder="0"
                @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
              />
              <span class="den-subtotal">
                {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
              </span>
            </div>

            <div class="grupo-label">Monedas</div>
            <div
              v-for="den in monedas"
              :key="'m-' + den.valor"
              class="tabla-fila"
            >
              <div class="den-info">
                <span class="den-badge moneda">${{ den.etiqueta }}</span>
                <span class="den-tipo">moneda</span>
              </div>
              <input
                v-model.number="den.cantidad"
                type="number"
                min="0"
                class="input-cantidad"
                placeholder="0"
                @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
              />
              <span class="den-subtotal">
                {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
              </span>
            </div>
          </div>

          <div class="total-efectivo">
            <span>Total en efectivo</span>
            <span class="total-valor">${{ totalEfectivo }}</span>
          </div>
        </div>

        <div v-if="errorMsg" class="error-msg">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ errorMsg }}
        </div>

        <button @click="abrirCaja" :disabled="abriendo" class="btn-abrir">
          <span v-if="!abriendo">Abrir Caja</span>
          <span v-else class="btn-loading">
            <span class="spinner"></span>
            Procesando...
          </span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter }        from 'vue-router'
import { useAuthStore }     from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'
import { ICONS }            from '../utils/icons'

const router        = useRouter()
const auth          = useAuthStore()
const sucursalStore = useSucursalStore()
const abriendo      = ref(false)
const errorMsg      = ref('')

const sucursalNombre = computed(() =>
  sucursalStore.sucursalActiva?.NombreSucursal || sessionStorage.getItem('sucursalNombre') || ''
)

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

const totalEfectivo = computed(() => {
  const tb = billetes.value.reduce((s, d) => s + d.valor * (d.cantidad || 0), 0)
  const tm = monedas.value.reduce((s, d)  => s + d.valor * (d.cantidad || 0), 0)
  return (tb + tm).toFixed(2)
})

const abrirCaja = async () => {
  errorMsg.value = ''
  abriendo.value = true

  const sId = sucursalStore.sucursalActiva?.SucursalId || Number(sessionStorage.getItem('sucursalId'))
  const eId = Number(auth.empleadoId) || Number(sessionStorage.getItem('empleadoId'))

  if (!sId || !eId) {
    errorMsg.value = 'No se pudo identificar la sucursal o el empleado. Por favor, re-inicie sesión.'
    abriendo.value = false
    return
  }

  try {
    const response = await fetch('/php/abrir_caja.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        sucursalId:   sId,
        empleadoId:   eId,
        montoInicial: parseFloat(totalEfectivo.value)
      })
    })
    const result = await response.json()
    if (result.status === 'error' || result.success === false) {
      throw new Error(result.message || result.error || 'Error al procesar la apertura')
    }
    sessionStorage.setItem('cajaId',      result.cajaId)
    sessionStorage.setItem('cajaAbierta', 'true')
    router.push({ name: 'dashboard' })
  } catch (error) {
    errorMsg.value = error.message
  } finally {
    abriendo.value = false
  }
}
</script>

<style scoped>
.fondo {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f2535 0%, #1a3a4a 60%, #0f2535 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
}

.contenedor {
  width: 100%;
  max-width: 680px;
}

.encabezado {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 32px;
}
.brand-icon {
  color: #f97316;
  display: flex;
  align-items: center;
}
.brand-name {
  color: #fff;
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0;
}

.caja-card {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}

.caja-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}
.caja-header-icon {
  width: 52px; height: 52px;
  background: #fff7ed;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.caja-titulo   { margin: 0 0 4px; font-size: 1.25rem; font-weight: 700; color: #1a2b3c; }
.caja-sucursal { margin: 0; font-size: 0.875rem; color: #f97316; font-weight: 600; }

.caja-descripcion {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1.5rem;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 3px solid #f97316;
  line-height: 1.5;
}

.efectivo-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}
.efectivo-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}
.efectivo-icono {
  width: 40px; height: 40px;
  background: #f0fdf4;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.efectivo-titulo   { margin: 0 0 2px; font-size: 1rem; font-weight: 700; color: #1a2b3c; }
.efectivo-subtitulo { margin: 0; font-size: 0.8rem; color: #64748b; }

.tabla-denominaciones { margin-bottom: 1rem; }

.tabla-header {
  display: grid;
  grid-template-columns: 1fr 140px 120px;
  padding: 0.5rem 0.75rem;
  font-size: 0.72rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 0.5rem;
}

.grupo-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.75rem 0.75rem 0.25rem;
}

.tabla-fila {
  display: grid;
  grid-template-columns: 1fr 140px 120px;
  align-items: center;
  padding: 0.6rem 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  transition: background 0.15s;
  border-radius: 6px;
}
.tabla-fila:hover { background: #f1f5f9; }

.den-info { display: flex; align-items: center; gap: 0.75rem; }
.den-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.8rem;
  min-width: 56px;
  text-align: center;
}
.den-badge.billete { background: #fff4ed; color: #f97316; }
.den-badge.moneda  { background: #f0fdf4; color: #16a34a; }
.den-tipo { font-size: 0.8rem; color: #94a3b8; }

.input-cantidad {
  width: 100px;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  text-align: center;
  background: #fff;
  color: #1a2b3c;
  outline: none;
  transition: all 0.2s;
}
.input-cantidad:focus {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249,115,22,0.1);
}
.input-cantidad::-webkit-inner-spin-button,
.input-cantidad::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.den-subtotal {
  text-align: right;
  font-weight: 600;
  font-size: 0.875rem;
  color: #1a2b3c;
}

.total-efectivo {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0.75rem 0;
  border-top: 2px solid #e2e8f0;
  margin-top: 0.5rem;
  font-weight: 600;
  font-size: 0.95rem;
  color: #1a2b3c;
}
.total-valor { font-size: 1.25rem; font-weight: 700; color: #f97316; }

.error-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #dc2626;
  font-size: 0.875rem;
  margin: 0 0 1rem;
  padding: 0.75rem 1rem;
  background: #fef2f2;
  border-radius: 8px;
  border-left: 3px solid #dc2626;
}

.btn-abrir {
  width: 100%;
  padding: 14px;
  background: #f97316;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-abrir:hover:not(:disabled) {
  background: #ea580c;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(249,115,22,0.3);
}
.btn-abrir:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>