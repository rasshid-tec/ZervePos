<template>
  <div class="sucursales-container">
    <div class="sucursales-header">
      <router-link to="/app/inventario" class="btn-volver">← Volver</router-link>
      <h1>Gestión de Sucursales</h1>
      <p>Ve y gestiona la información de todas las sucursales</p>
    </div>

    <div class="sucursales-content">
      <!-- BUSCADOR -->
      <div class="buscador-section">
        <div class="input-busqueda-wrapper">
          <input
            v-model="textoBusqueda"
            type="text"
            placeholder="Buscar sucursal..."
            class="input-busqueda"
          />
          <span class="icono">🔍</span>
        </div>
      </div>

      <!-- TABLA DE SUCURSALES -->
      <div class="tabla-wrapper">
        <table class="tabla-sucursales" v-if="!cargando && sucursalesFiltradas.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>Ciudad</th>
              <th>Teléfono</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="sucursal in sucursalesFiltradas"
              :key="sucursal.SucursalId"
              :class="{ 'fila-seleccionada': sucursalSeleccionada?.SucursalId === sucursal.SucursalId }"
              @click="seleccionarSucursal(sucursal)"
            >
              <td class="id">{{ sucursal.SucursalId }}</td>
              <td class="nombre">{{ sucursal.NombreSucursal }}</td>
              <td class="direccion">{{ sucursal.Direccion || 'N/A' }}</td>
              <td class="ciudad">{{ sucursal.Ciudad || 'N/A' }}</td>
              <td class="telefono">{{ sucursal.Telefono || 'N/A' }}</td>
              <td class="estado">
                <span :class="['badge-estado', sucursal.Estado ? 'activa' : 'inactiva']">
                  {{ sucursal.Estado ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
              <td class="acciones" @click.stop>
                <button @click="abrirEditar(sucursal)" class="btn-accion editar" title="Editar">✏️</button>
                <button
                  @click="abrirEliminar(sucursal)"
                  class="btn-accion eliminar"
                  title="Eliminar"
                  :disabled="!sucursal.Estado"
                >🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="cargando" class="estado-carga">
          <p>Cargando sucursales...</p>
        </div>

        <div v-if="!cargando && sucursalesFiltradas.length === 0" class="sin-resultados">
          <p>No hay sucursales que coincidan con tu búsqueda</p>
        </div>
      </div>

      <!-- ESTADÍSTICAS -->
      <div v-if="!cargando && sucursales.length > 0" class="estadisticas-section">
        <div class="estadistica-card">
          <span class="label">Total Sucursales</span>
          <span class="valor">{{ sucursales.length }}</span>
        </div>
        <div class="estadistica-card activas">
          <span class="label">Activas</span>
          <span class="valor">{{ sucursalesActivas }}</span>
        </div>
        <div class="estadistica-card inactivas">
          <span class="label">Inactivas</span>
          <span class="valor">{{ sucursalesInactivas }}</span>
        </div>
      </div>
    </div>

    <!-- ── MODAL EDITAR ── -->
    <div v-if="mostrarModalEditar" class="modal-overlay" @click.self="cerrarEditar">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Editar Sucursal</h3>
          <button @click="cerrarEditar" class="btn-cerrar-modal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre *</label>
            <input v-model="formEditar.NombreSucursal" type="text" class="input-text" placeholder="Nombre de la sucursal" />
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input v-model="formEditar.Direccion" type="text" class="input-text" placeholder="Dirección" />
          </div>
          <div class="form-group">
            <label>Ciudad</label>
            <input v-model="formEditar.Ciudad" type="text" class="input-text" placeholder="Ciudad" />
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="formEditar.Telefono" type="text" class="input-text" placeholder="Teléfono" />
          </div>
        </div>
        <div class="modal-footer">
          <button @click="cerrarEditar" type="button" class="btn-cancelar">Cancelar</button>
          <button @click="guardarEditar" type="button" class="btn-guardar" :disabled="guardando">
            <span v-if="!guardando">Guardar</span>
            <span v-else>Guardando...</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ── CONFIRMACION PELIGROSA ── -->
    <ConfirmacionPeligrosa ref="confirmacionRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';
import ConfirmacionPeligrosa from '../components/ConfirmacionPeligrosa.vue';

const toast           = useToast();
const confirmacionRef = ref(null);

// ==================== ESTADO ====================
const sucursales           = ref([]);
const textoBusqueda        = ref('');
const cargando             = ref(false);
const guardando            = ref(false);
const sucursalSeleccionada = ref(null);
const mostrarModalEditar   = ref(false);

const formEditar = ref({
  SucursalId:     null,
  NombreSucursal: '',
  Direccion:      '',
  Ciudad:         '',
  Telefono:       ''
});

// ==================== COMPUTED ====================
const sucursalesFiltradas = computed(() => {
  if (!textoBusqueda.value.trim()) return sucursales.value;
  const busqueda = textoBusqueda.value.toLowerCase();
  return sucursales.value.filter(s =>
    s.NombreSucursal.toLowerCase().includes(busqueda) ||
    s.Direccion?.toLowerCase().includes(busqueda) ||
    s.Ciudad?.toLowerCase().includes(busqueda) ||
    s.Telefono?.includes(busqueda)
  );
});

const sucursalesActivas   = computed(() => sucursales.value.filter(s => s.Estado).length);
const sucursalesInactivas = computed(() => sucursales.value.filter(s => !s.Estado).length);

// ==================== MÉTODOS ====================

const obtenerSucursales = async () => {
  cargando.value = true;
  try {
    const response = await fetch('/php/obtener_todas_sucursales.php');
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    const result = await response.json();
    if (result.status === 'error') throw new Error(result.message);
    sucursales.value = result.data || [];
    toast.success('Sucursales cargadas correctamente');
  } catch (error) {
    toast.error(`Error al cargar sucursales: ${error.message}`);
  } finally {
    cargando.value = false;
  }
};

const seleccionarSucursal = (sucursal) => {
  sucursalSeleccionada.value = sucursal;
};

// ── EDITAR ──
const abrirEditar = (sucursal) => {
  formEditar.value = {
    SucursalId:     sucursal.SucursalId,
    NombreSucursal: sucursal.NombreSucursal,
    Direccion:      sucursal.Direccion || '',
    Ciudad:         sucursal.Ciudad    || '',
    Telefono:       sucursal.Telefono  || ''
  };
  mostrarModalEditar.value = true;
};

const cerrarEditar = () => {
  mostrarModalEditar.value = false;
};

const guardarEditar = async () => {
  if (!formEditar.value.NombreSucursal.trim()) {
    toast.error('El nombre de la sucursal es requerido');
    return;
  }

  guardando.value = true;
  try {
    const response = await fetch('/php/editar_sucursal.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(formEditar.value)
    });
    const result = await response.json();
    if (result.status === 'error') throw new Error(result.message);

    const idx = sucursales.value.findIndex(s => s.SucursalId === formEditar.value.SucursalId);
    if (idx !== -1) {
      sucursales.value[idx] = { ...sucursales.value[idx], ...formEditar.value };
    }

    toast.success('Sucursal actualizada correctamente');
    cerrarEditar();
  } catch (error) {
    toast.error(`Error al editar: ${error.message}`);
  } finally {
    guardando.value = false;
  }
};

// ── ELIMINAR ──
const abrirEliminar = async (sucursal) => {
  const confirmado = await confirmacionRef.value.mostrar(
    'Eliminar Sucursal',
    `¿Estás seguro de que deseas eliminar "${sucursal.NombreSucursal}"? Esta acción no se puede deshacer.`
  );

  if (!confirmado) return;

  try {
    const response = await fetch('/php/eliminar_sucursal.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ SucursalId: sucursal.SucursalId })
    });
    const result = await response.json();
    if (result.status === 'error') throw new Error(result.message);

    const idx = sucursales.value.findIndex(s => s.SucursalId === sucursal.SucursalId);
    if (idx !== -1) sucursales.value[idx].Estado = 0;

    toast.success('Sucursal eliminada correctamente');
  } catch (error) {
    toast.error(`Error al eliminar: ${error.message}`);
  }
};

// ==================== CICLO DE VIDA ====================
onMounted(() => obtenerSucursales());
</script>

<style scoped>
:root {
  --color-bg: #f5f5f5;
  --color-bg-card: #ffffff;
  --color-border: #e0e0e0;
  --color-text: #333333;
  --color-text-secondary: #666666;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  --color-primary: #0066cc;
}

.sucursales-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

.sucursales-header { margin-bottom: 2rem; }

.btn-volver {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #f0f0f0;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  text-decoration: none;
  color: var(--color-text);
  font-size: 0.875rem;
  margin-bottom: 1rem;
  transition: all 0.2s ease;
}
.btn-volver:hover { background: var(--color-border); }

.sucursales-header h1 { margin: 0 0 0.5rem 0; font-size: 2rem; }
.sucursales-header p  { margin: 0; color: var(--color-text-secondary); }

/* BUSCADOR */
.buscador-section { margin-bottom: 2rem; }
.input-busqueda-wrapper { position: relative; max-width: 400px; }
.input-busqueda {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.input-busqueda:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
.input-busqueda-wrapper .icono {
  position: absolute;
  right: 0.75rem; top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

/* TABLA */
.tabla-wrapper {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  overflow: hidden;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.tabla-sucursales { width: 100%; border-collapse: collapse; }
.tabla-sucursales thead {
  background: #f5f5f5;
  border-bottom: 2px solid var(--color-border);
}
.tabla-sucursales th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.tabla-sucursales td {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
  font-size: 0.875rem;
}
.tabla-sucursales tbody tr {
  cursor: pointer;
  transition: all 0.2s ease;
}
.tabla-sucursales tbody tr:hover { background: #f0f6ff; }
.fila-seleccionada { background: rgba(0,102,204,0.06) !important; }

.id     { color: var(--color-primary); font-weight: 600; width: 60px; }
.nombre { font-weight: 500; }
.direccion, .ciudad, .telefono { color: var(--color-text-secondary); }
.estado { text-align: center; }

.badge-estado {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
}
.badge-estado.activa   { background: #d1fae5; color: #065f46; }
.badge-estado.inactiva { background: #fee2e2; color: #991b1b; }

/* ACCIONES */
.acciones { text-align: center; width: 100px; }
.btn-accion {
  width: 32px; height: 32px;
  border: none; border-radius: 4px;
  cursor: pointer; font-size: 0.9rem;
  transition: all 0.2s;
  margin: 0 2px;
}
.btn-accion.editar            { background: #dbeafe; }
.btn-accion.editar:hover      { background: #bfdbfe; }
.btn-accion.eliminar          { background: #fee2e2; }
.btn-accion.eliminar:hover:not(:disabled) { background: #fecaca; }
.btn-accion:disabled          { opacity: 0.4; cursor: not-allowed; }

/* ESTADOS */
.estado-carga, .sin-resultados {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}
.estado-carga {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}
.estado-carga p::after {
  content: '';
  display: inline-block;
  margin-left: 0.5rem;
  width: 1rem; height: 1rem;
  border: 2px solid var(--color-text-secondary);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ESTADÍSTICAS */
.estadisticas-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}
.estadistica-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.estadistica-card.activas   { border-left: 4px solid var(--color-success); }
.estadistica-card.inactivas { border-left: 4px solid var(--color-danger); }
.estadistica-card .label {
  display: block;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  font-weight: 600;
}
.estadistica-card .valor { display: block; font-size: 2rem; font-weight: 700; }

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  border-radius: 10px;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
}
.modal-header h3 { margin: 0; font-size: 1rem; }
.btn-cerrar-modal {
  background: none; border: none;
  font-size: 1.1rem; cursor: pointer;
  color: var(--color-text-secondary);
  transition: color 0.2s;
}
.btn-cerrar-modal:hover { color: var(--color-text); }
.modal-body { padding: 1.5rem; }
.modal-body .form-group { margin-bottom: 1rem; }
.modal-body .form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-size: 0.875rem;
  font-weight: 500;
}
.input-text {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.875rem;
  box-sizing: border-box;
  transition: all 0.2s;
}
.input-text:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
.modal-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border);
  background: #fafafa;
}
.btn-cancelar, .btn-guardar {
  flex: 1; padding: 0.75rem;
  border: none; border-radius: 6px;
  font-weight: 600; font-size: 0.875rem;
  cursor: pointer; transition: all 0.2s;
}
.btn-cancelar { background: #f0f0f0; color: var(--color-text); border: 1px solid var(--color-border); }
.btn-cancelar:hover { background: var(--color-border); }
.btn-guardar { background: var(--color-primary); color: white; }
.btn-guardar:hover:not(:disabled) { background: #0052a3; }
.btn-guardar:disabled { opacity: 0.5; cursor: not-allowed; }

/* RESPONSIVE */
@media (max-width: 768px) {
  .sucursales-container { padding: 1rem; }
  .sucursales-header h1 { font-size: 1.5rem; }
  .tabla-sucursales th, .tabla-sucursales td { padding: 0.75rem 0.5rem; font-size: 0.75rem; }
  .estadisticas-section { grid-template-columns: 1fr; }
}
</style>