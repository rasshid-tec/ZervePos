<template>
  <div class="exito-container">

    <!-- ENCABEZADO -->
    <div class="exito-header no-print">
      <router-link to="/app/inventario/entradas" class="btn-volver">← Nueva Entrada</router-link>
      <router-link to="/app/inventario" class="btn-volver">🏠 Inventario</router-link>
      <button @click="imprimir" class="btn-imprimir">🖨️ Imprimir Recibo</button>
    </div>

    <!-- ESTADO CARGANDO -->
    <div v-if="cargando" class="estado-cargando">
      <p>Cargando recibo...</p>
    </div>

    <!-- ERROR -->
    <div v-else-if="error" class="estado-error no-print">
      <p>{{ error }}</p>
      <router-link to="/app/inventario/entradas" class="btn-volver">← Regresar</router-link>
    </div>

    <!-- RECIBO -->
    <div v-else-if="datos" class="recibo" id="recibo">

      <!-- Encabezado del recibo -->
      <div class="recibo-header">
        <div class="recibo-empresa">ZervePOS</div>
        <div class="recibo-titulo">RECIBO DE ENTRADA</div>
        <div class="recibo-folio"># {{ datos.entrada.EntradaId }}</div>
      </div>

      <div class="recibo-divider"></div>

      <!-- Info general -->
      <div class="recibo-info">
        <div class="recibo-fila">
          <span class="recibo-label">Sucursal:</span>
          <span class="recibo-valor">{{ datos.entrada.NombreSucursal }}</span>
        </div>
        <div class="recibo-fila">
          <span class="recibo-label">Empleado:</span>
          <span class="recibo-valor">{{ datos.entrada.NombreEmpleado }}</span>
        </div>
        <div class="recibo-fila">
          <span class="recibo-label">Fecha:</span>
          <span class="recibo-valor">{{ datos.entrada.Fecha }}</span>
        </div>
        <div class="recibo-fila">
          <span class="recibo-label">Tipo:</span>
          <span class="recibo-valor">{{ datos.entrada.Tipo }}</span>
        </div>
        <div v-if="datos.entrada.Descripcion" class="recibo-fila">
          <span class="recibo-label">Descripción:</span>
          <span class="recibo-valor">{{ datos.entrada.Descripcion }}</span>
        </div>
      </div>

      <!-- Datos de factura (si aplica) -->
      <div v-if="datos.entrada.FacturaId" class="recibo-factura">
        <div class="recibo-divider"></div>
        <div class="recibo-subtitulo">🧾 Datos de Factura</div>
        <div class="recibo-fila">
          <span class="recibo-label">Proveedor:</span>
          <span class="recibo-valor">{{ datos.entrada.Proveedor }}</span>
        </div>
        <div class="recibo-fila">
          <span class="recibo-label">RFC:</span>
          <span class="recibo-valor">{{ datos.entrada.RFC }}</span>
        </div>
        <div class="recibo-fila">
          <span class="recibo-label">Fecha Factura:</span>
          <span class="recibo-valor">{{ datos.entrada.FechaFactura }}</span>
        </div>
      </div>

      <div class="recibo-divider"></div>

      <!-- Productos -->
      <div class="recibo-subtitulo">Productos Ingresados</div>
      <table class="recibo-tabla">
        <thead>
          <tr>
            <th class="col-producto">Producto</th>
            <th class="col-cantidad">Cant.</th>
            <th class="col-precio">P. Compra</th>
            <th class="col-subtotal">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(det, i) in datos.detalles" :key="i">
            <td class="col-producto">{{ det.NombreProducto }}</td>
            <td class="col-cantidad">{{ det.Cantidad }}</td>
            <td class="col-precio">${{ parseFloat(det.PrecioCompra).toFixed(2) }}</td>
            <td class="col-subtotal">${{ parseFloat(det.Subtotal).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="recibo-divider"></div>

      <!-- Total -->
      <div class="recibo-total">
        <span>Total:</span>
        <span>${{ totalCalculado }}</span>
      </div>

      <!-- Pie -->
      <div class="recibo-pie">
        <p>Documento generado por ZervePOS</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route    = useRoute();
const cargando = ref(true);
const error    = ref(null);
const datos    = ref(null);

const totalCalculado = computed(() => {
  if (!datos.value?.detalles) return '0.00';
  return datos.value.detalles
    .reduce((sum, d) => sum + parseFloat(d.Subtotal || 0), 0)
    .toFixed(2);
});

const obtenerDatos = async () => {
  const entradaId = route.query.id;
  if (!entradaId) {
    error.value = 'No se proporcionó un ID de entrada.';
    cargando.value = false;
    return;
  }

  try {
    const response = await fetch(`/php/obtener_detalle_entrada.php?entradaId=${entradaId}`);
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

    const result = await response.json();
    if (result.status === 'error') throw new Error(result.message || 'Error desconocido');

    datos.value = result.data;
  } catch (e) {
    error.value = `Error al cargar el recibo: ${e.message}`;
  } finally {
    cargando.value = false;
  }
};

const imprimir = () => {
  window.print();
};

onMounted(() => {
  obtenerDatos();
});
</script>

<style scoped>
/* ── PÁGINA ── */
.exito-container {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ── ENCABEZADO ── */
.exito-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
  width: 100%;
  max-width: 620px;
}

.btn-volver {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #f0f0f0;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  text-decoration: none;
  color: #333;
  font-size: 0.875rem;
  transition: background 0.2s;
}
.btn-volver:hover { background: #e0e0e0; }

.btn-imprimir {
  margin-left: auto;
  padding: 0.5rem 1.25rem;
  background: #0066cc;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-imprimir:hover { background: #0052a3; }

/* ── ESTADOS ── */
.estado-cargando,
.estado-error {
  text-align: center;
  padding: 3rem;
  color: #666;
}
.estado-error { color: #dc2626; }

/* ── RECIBO ── */
.recibo {
  background: white;
  width: 100%;
  max-width: 620px;
  padding: 2rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  font-size: 0.9rem;
  color: #333;
}

.recibo-header {
  text-align: center;
  margin-bottom: 1rem;
}
.recibo-empresa {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0066cc;
  letter-spacing: 1px;
}
.recibo-titulo {
  font-size: 0.85rem;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-top: 0.25rem;
}
.recibo-folio {
  font-size: 1.1rem;
  font-weight: 700;
  color: #333;
  margin-top: 0.5rem;
}

.recibo-divider {
  height: 1px;
  background: #e0e0e0;
  margin: 1rem 0;
}

.recibo-subtitulo {
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #666;
  margin-bottom: 0.75rem;
}

.recibo-info,
.recibo-factura { display: flex; flex-direction: column; gap: 0.4rem; }

.recibo-fila {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}
.recibo-label {
  font-size: 0.8rem;
  color: #888;
  flex-shrink: 0;
}
.recibo-valor {
  font-weight: 500;
  text-align: right;
}

/* ── TABLA ── */
.recibo-tabla {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
}
.recibo-tabla thead tr {
  border-bottom: 2px solid #333;
}
.recibo-tabla th {
  padding: 0.5rem 0.25rem;
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #555;
}
.recibo-tabla tbody tr {
  border-bottom: 1px solid #f0f0f0;
}
.recibo-tabla td {
  padding: 0.5rem 0.25rem;
}
.col-producto  { text-align: left;   width: 45%; }
.col-cantidad  { text-align: center; width: 10%; }
.col-precio    { text-align: right;  width: 20%; }
.col-subtotal  { text-align: right;  width: 25%; font-weight: 600; }

/* ── TOTAL ── */
.recibo-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: #0066cc;
  padding: 0.5rem 0;
}

/* ── PIE ── */
.recibo-pie {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.75rem;
  color: #aaa;
}

/* ── IMPRESIÓN ── */
@media print {
  .no-print { display: none !important; }

  .exito-container {
    padding: 0;
    background: white;
  }

  .recibo {
    box-shadow: none;
    border: none;
    max-width: 100%;
    padding: 1rem;
  }
}
</style>
<style>
@media print {
  .layout-header,
  .layout-body > header,
  nav,
  aside {
    display: none !important;
  }

  .layout-body {
    margin-left: 0 !important;
  }

  .layout-main {
    padding: 0 !important;
  }

  .exito-header {
    display: none !important;
  }

  .exito-container {
    padding: 0 !important;
    background: white !important;
  }

  .recibo {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    padding: 1rem !important;
  }
}
</style>