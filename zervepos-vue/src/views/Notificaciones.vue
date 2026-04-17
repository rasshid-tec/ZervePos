<template>
  <div class="notificaciones-container">

    <div class="page-header">
      <h1 class="page-titulo">Notificaciones</h1>
      <p class="page-subtitulo">Alertas de bajo stock y créditos sin actividad</p>
    </div>

    <!-- Resumen -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icono" style="background:#fff7ed">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
          <p class="stat-label">Bajo Stock</p>
          <p class="stat-valor naranja">{{ productos.length }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#fef2f2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
          <p class="stat-label">Créditos sin abono</p>
          <p class="stat-valor rojo">{{ creditos.length }}</p>
        </div>
      </div>
    </div>

    <!-- Bajo stock -->
    <div class="seccion-card">
      <div class="seccion-header">
        <div class="seccion-icono" style="background:#fff7ed">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
          <h2 class="seccion-titulo">Productos con Bajo Stock</h2>
          <p class="seccion-subtitulo">Productos por debajo de su stock mínimo</p>
        </div>
      </div>

      <div v-if="cargandoStock" class="estado-vacio">
        <span class="spinner"></span>
        <p>Cargando...</p>
      </div>

      <div v-else-if="productos.length === 0" class="estado-vacio">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <p>No hay productos con bajo stock</p>
      </div>

      <div v-else class="tabla-wrapper">
        <table class="tabla">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Sucursal</th>
              <th>Stock Actual</th>
              <th>Stock Mínimo</th>
              <th>Diferencia</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in productos" :key="`${p.ProductoId}-${p.SucursalId}`">
              <td class="td-sku">{{ p.SKU }}</td>
              <td>
                <span class="td-nombre">{{ p.NombreProducto }}</span>
                <span v-if="p.Marca" class="td-marca">{{ p.Marca }}</span>
              </td>
              <td class="td-secondary">{{ p.NombreSucursal }}</td>
              <td>
                <span :class="['badge-stock', p.StockActual === 0 ? 'agotado' : 'bajo']">
                  {{ p.StockActual }} uds
                </span>
              </td>
              <td class="td-secondary">{{ p.StockMinimo }} uds</td>
              <td class="td-diferencia">{{ p.StockActual - p.StockMinimo }} uds</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Créditos sin abono -->
    <div class="seccion-card">
      <div class="seccion-header">
        <div class="seccion-icono" style="background:#fef2f2">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
          <h2 class="seccion-titulo">Créditos sin Abono</h2>
          <p class="seccion-subtitulo">Créditos activos con más de 30 días desde su creación</p>
        </div>
      </div>

      <div v-if="cargandoCreditos" class="estado-vacio">
        <span class="spinner"></span>
        <p>Cargando...</p>
      </div>

      <div v-else-if="creditos.length === 0" class="estado-vacio">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <p>No hay créditos sin actividad</p>
      </div>

      <div v-else class="tabla-wrapper">
        <table class="tabla">
          <thead>
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>Sucursal</th>
              <th>Fecha Crédito</th>
              <th>Días</th>
              <th>Monto Original</th>
              <th>Saldo Pendiente</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in creditos" :key="c.CreditoId">
              <td class="td-sku">{{ c.CreditoId }}</td>
              <td>
                <span class="td-nombre">{{ c.NombreCliente }}</span>
                <span v-if="c.Negocio" class="td-marca">{{ c.Negocio }}</span>
              </td>
              <td class="td-secondary">{{ c.NombreSucursal }}</td>
              <td class="td-secondary">{{ c.FechaCredito }}</td>
              <td>
                <span :class="['badge-dias', c.DiasTranscurridos >= 60 ? 'critico' : 'advertencia']">
                  {{ c.DiasTranscurridos }} días
                </span>
              </td>
              <td class="td-secondary">${{ formato(c.MontoTotal) }}</td>
              <td class="td-saldo">${{ formato(c.SaldoActual) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore }   from '../stores/auth';
import { useToast }       from '../composables/useToast';
import { normalizeRole }  from '../utils/roles';

const auth  = useAuthStore();
const toast = useToast();

const productos        = ref([]);
const creditos         = ref([]);
const cargandoStock    = ref(true);
const cargandoCreditos = ref(true);

const rolNormalizado = normalizeRole(auth.rol);
const formato = (n) => Number(n || 0).toFixed(2);

async function cargarBajoStock() {
  cargandoStock.value = true;
  try {
    const res  = await fetch(`/php/notificaciones/bajo_stock.php?empleadoId=${auth.empleadoId}&rol=${rolNormalizado}`);
    const data = await res.json();
    if (data.success) {
      productos.value = data.productos;
    } else {
      toast.error('Error al cargar bajo stock');
    }
  } catch (e) {
    toast.error('Error de conexión');
    console.error(e);
  } finally {
    cargandoStock.value = false;
  }
}

async function cargarCreditosVencidos() {
  cargandoCreditos.value = true;
  try {
    const res  = await fetch(`/php/notificaciones/creditos_vencidos.php?empleadoId=${auth.empleadoId}&rol=${rolNormalizado}`);
    const data = await res.json();
    if (data.success) {
      creditos.value = data.creditos;
    } else {
      toast.error('Error al cargar créditos');
    }
  } catch (e) {
    toast.error('Error de conexión');
    console.error(e);
  } finally {
    cargandoCreditos.value = false;
  }
}

onMounted(() => {
  cargarBajoStock();
  cargarCreditosVencidos();
});
</script>

<style scoped>
.notificaciones-container { padding: 32px; max-width: 1100px; }

.page-header { margin-bottom: 28px; }
.page-titulo    { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0 0 6px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 14px; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 24px;
}
.stat-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 16px 20px; display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.stat-icono {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-label { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin: 0 0 4px; }
.stat-valor { font-size: 24px; font-weight: 700; margin: 0; }
.stat-valor.naranja { color: #f97316; }
.stat-valor.rojo    { color: #dc2626; }

.seccion-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  margin-bottom: 24px;
}
.seccion-header {
  display: flex; align-items: center; gap: 14px;
  padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
}
.seccion-icono {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.seccion-titulo    { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
.seccion-subtitulo { font-size: 12px; color: #94a3b8; margin: 0; }

.tabla-wrapper { overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; }
.tabla thead { background: #f8fafc; }
.tabla th {
  padding: 11px 16px; text-align: left;
  font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}
.tabla td {
  padding: 12px 16px; font-size: 13px; color: #1e293b;
  border-bottom: 1px solid #f1f5f9;
}
.tabla tbody tr:hover { background: #f8fafc; }

.td-sku       { color: #2563eb; font-weight: 700; }
.td-nombre    { display: block; font-weight: 600; color: #1e293b; }
.td-marca     { display: block; font-size: 11px; color: #94a3b8; margin-top: 2px; }
.td-secondary { color: #64748b; }
.td-diferencia { color: #dc2626; font-weight: 700; }
.td-saldo     { color: #dc2626; font-weight: 700; }

.badge-stock {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.badge-stock.agotado { background: #fef2f2; color: #dc2626; }
.badge-stock.bajo    { background: #fff7ed; color: #f97316; }

.badge-dias {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.badge-dias.advertencia { background: #fff7ed; color: #f97316; }
.badge-dias.critico     { background: #fef2f2; color: #dc2626; }

.estado-vacio {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 48px; gap: 12px;
  color: #94a3b8; font-size: 14px;
}
.spinner {
  width: 28px; height: 28px; border: 3px solid #e2e8f0;
  border-top-color: #2563eb; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .notificaciones-container { padding: 16px; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>