<template>
  <div class="fondo">
    <div class="contenedor">

      <!-- ENCABEZADO -->
      <div class="encabezado">
        <span class="brand-icon">⚡</span>
        <h1 class="brand-name">ZervePOS</h1>
      </div>

      <div class="caja-card">
        <div class="caja-header">
          <div class="caja-header-icon">🏪</div>
          <div>
            <h2 class="caja-titulo">Apertura de Caja</h2>
            <p class="caja-sucursal">{{ sucursalNombre }}</p>
          </div>
        </div>

        <p class="caja-descripcion">
          No hay una caja abierta en esta sucursal. Ingresa el fondo inicial contando las denominaciones disponibles.
        </p>

        <!-- SECCIÓN EFECTIVO -->
        <div class="efectivo-section">
          <div class="efectivo-header">
            <span class="efectivo-icon">💵</span>
            <div>
              <h3 class="efectivo-titulo">Efectivo</h3>
              <p class="efectivo-subtitulo">Fondo inicial de caja</p>
            </div>
          </div>

          <!-- TABLA DENOMINACIONES -->
          <div class="tabla-denominaciones">
            <div class="tabla-header">
              <span>DENOMINACIÓN</span>
              <span>CANTIDAD</span>
              <span>SUBTOTAL</span>
            </div>

            <!-- BILLETES -->
            <div class="grupo-label">Billetes</div>
            <div
              v-for="den in billetes"
              :key="den.valor"
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
                @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
              />
              <span class="den-subtotal">
                {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
              </span>
            </div>

            <!-- MONEDAS -->
            <div class="grupo-label">Monedas</div>
            <div
              v-for="den in monedas"
              :key="den.valor"
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
                @input="den.cantidad = Math.max(0, parseInt(den.cantidad) || 0)"
              />
              <span class="den-subtotal">
                {{ den.cantidad > 0 ? '$' + (den.valor * den.cantidad).toFixed(2) : '—' }}
              </span>
            </div>
          </div>

          <!-- TOTAL -->
          <div class="total-efectivo">
            <span>Total en efectivo</span>
            <span class="total-valor">${{ totalEfectivo }}</span>
          </div>
        </div>

        <!-- ERROR -->
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

        <!-- BOTÓN ABRIR -->
        <button
          @click="abrirCaja"
          :disabled="abriendo"
          class="btn-abrir"
        >
          <span v-if="!abriendo">Abrir Caja →</span>
          <span v-else>Abriendo...</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed }    from 'vue'
import { useRouter }        from 'vue-router'
import { useAuthStore }     from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'

const router        = useRouter()
const auth          = useAuthStore()
const sucursalStore = useSucursalStore()
const abriendo      = ref(false)
const errorMsg      = ref('')

const sucursalNombre = computed(() => sucursalStore.sucursalActiva?.NombreSucursal || '')

// ── Denominaciones ────────────────────────────────────────────
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

// ── Total ─────────────────────────────────────────────────────
const totalEfectivo = computed(() => {
  const tb = billetes.value.reduce((s, d) => s + d.valor * (d.cantidad || 0), 0)
  const tm = monedas.value.reduce((s, d)  => s + d.valor * (d.cantidad || 0), 0)
  return (tb + tm).toFixed(2)
})

// ── Abrir caja ────────────────────────────────────────────────
const abrirCaja = async () => {
  errorMsg.value = ''
  abriendo.value = true

  try {
    const payload = {
      sucursalId:   sucursalStore.sucursalActiva?.SucursalId || Number(sessionStorage.getItem('sucursalId')),
      empleadoId:   Number(auth.empleadoId) || Number(sessionStorage.getItem('empleadoId')),
      montoInicial: parseFloat(totalEfectivo.value)
    }

    const response = await fetch('/php/abrir_caja.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload)
    })

    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`)

    const result = await response.json()

    if (result.status === 'error') {
      throw new Error(result.message || 'Error desconocido')
    }

    // Guardar cajaId para usarlo en ventas
    sessionStorage.setItem('cajaId', result.cajaId)

    router.push('/app/dashboard')

  } catch (error) {
    errorMsg.value = error.message || 'Error al abrir caja'
    console.error('Error al abrir caja:', error)
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
.brand-icon { font-size: 1.8rem; }
.brand-name { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0; }

.caja-card {
  background: white;
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
.caja-header-icon { font-size: 2rem; }
.caja-titulo { margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 700; color: #1a2b3c; }
.caja-sucursal { margin: 0; font-size: 0.875rem; color: #64748b; font-weight: 500; }

.caja-descripcion {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1.5rem 0;
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
.efectivo-icon { font-size: 1.5rem; }
.efectivo-titulo { margin: 0 0 0.2rem 0; font-size: 1rem; font-weight: 700; color: #1a2b3c; }
.efectivo-subtitulo { margin: 0; font-size: 0.8rem; color: #64748b; }

.tabla-denominaciones { margin-bottom: 1rem; }

.tabla-header {
  display: grid;
  grid-template-columns: 1fr 140px 120px;
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 0.5rem;
}

.grupo-label {
  font-size: 0.75rem;
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
}
.tabla-fila:hover { background: #f1f5f9; border-radius: 6px; }

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
  background: white;
  transition: all 0.2s;
  color: #1a2b3c;
}
.input-cantidad:focus {
  outline: none;
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249,115,22,0.1);
}

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
  color: #ef4444;
  font-size: 0.875rem;
  margin: 0 0 1rem;
  padding: 0.75rem 1rem;
  background: #fef2f2;
  border-radius: 8px;
  border-left: 3px solid #ef4444;
}

.btn-abrir {
  width: 100%;
  padding: 1rem;
  background: #f97316;
  color: white;
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
</style>