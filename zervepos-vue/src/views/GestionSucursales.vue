<template>
  <div class="sucursales-container">

    <div class="page-header">
      <button class="btn-back" @click="$router.push('/app/inventario')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div>
        <h1 class="page-titulo">Gestión de Sucursales</h1>
        <p class="page-subtitulo">Consulta y edita la información de las sucursales</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icono" style="background:#f1f5f9">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
          <p class="stat-label">Total</p>
          <p class="stat-valor">{{ sucursales.length }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#f0fdf4">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <p class="stat-label">Activas</p>
          <p class="stat-valor verde">{{ sucursalesActivas }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono" style="background:#fef2f2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <p class="stat-label">Inactivas</p>
          <p class="stat-valor rojo">{{ sucursalesInactivas }}</p>
        </div>
      </div>
    </div>

    <!-- Tabla card -->
    <div class="tabla-card">
      <div class="tabla-header">
        <div>
          <h2 class="tabla-titulo">Sucursales</h2>
          <p class="tabla-subtitulo">Listado de todas las sucursales del sistema</p>
        </div>
        <div class="buscador-wrap">
          <svg class="buscador-icono" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="textoBusqueda"
            type="text"
            placeholder="Buscar sucursal..."
            class="input-busqueda"
          />
        </div>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla" v-if="!cargando && sucursalesFiltradas.length > 0">
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
              v-for="s in sucursalesFiltradas"
              :key="s.SucursalId"
              :class="{ 'fila-activa': sucursalSeleccionada?.SucursalId === s.SucursalId }"
              @click="seleccionarSucursal(s)"
            >
              <td class="td-id">{{ s.SucursalId }}</td>
              <td class="td-nombre">{{ s.NombreSucursal }}</td>
              <td class="td-secondary">{{ s.Direccion || '—' }}</td>
              <td class="td-secondary">{{ s.Ciudad || '—' }}</td>
              <td class="td-secondary">{{ s.Telefono || '—' }}</td>
              <td>
                <span :class="['badge', s.Estado ? 'badge-activa' : 'badge-inactiva']">
                  {{ s.Estado ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
              <td class="td-acciones" @click.stop>
                <button @click="abrirEditar(s)" class="btn-accion btn-editar" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button
                  @click="abrirEliminar(s)"
                  class="btn-accion btn-eliminar"
                  title="Eliminar"
                  :disabled="!s.Estado"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="cargando" class="estado-vacio">
          <span class="spinner"></span>
          <p>Cargando sucursales...</p>
        </div>

        <div v-if="!cargando && sucursalesFiltradas.length === 0" class="estado-vacio">
          <p>No hay sucursales que coincidan</p>
        </div>
      </div>
    </div>

    <!-- Modal editar -->
    <div v-if="mostrarModalEditar" class="modal-overlay" @click.self="cerrarEditar">
      <div class="modal">
        <div class="modal-header">
          <h3>Editar Sucursal</h3>
          <button @click="cerrarEditar" class="btn-x">✕</button>
        </div>
        <div class="modal-body">
          <div class="campo">
            <label>Nombre *</label>
            <input v-model="formEditar.NombreSucursal" type="text" class="input-text" placeholder="Nombre de la sucursal" />
          </div>
          <div class="campo">
            <label>Dirección</label>
            <input v-model="formEditar.Direccion" type="text" class="input-text" placeholder="Dirección" />
          </div>
          <div class="campo">
            <label>Ciudad</label>
            <input v-model="formEditar.Ciudad" type="text" class="input-text" placeholder="Ciudad" />
          </div>
          <div class="campo">
            <label>Teléfono</label>
            <input v-model="formEditar.Telefono" type="text" class="input-text" placeholder="Teléfono" />
          </div>
        </div>
        <div class="modal-footer">
          <button @click="cerrarEditar" type="button" class="btn-cancelar">Cancelar</button>
          <button @click="guardarEditar" type="button" class="btn-guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmacionPeligrosa ref="confirmacionRef" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';
import ConfirmacionPeligrosa from '../components/ConfirmacionPeligrosa.vue';

const toast           = useToast();
const confirmacionRef = ref(null);

const sucursales           = ref([]);
const textoBusqueda        = ref('');
const cargando             = ref(false);
const guardando            = ref(false);
const sucursalSeleccionada = ref(null);
const mostrarModalEditar   = ref(false);

const formEditar = ref({
  SucursalId: null, NombreSucursal: '', Direccion: '', Ciudad: '', Telefono: ''
});

const sucursalesFiltradas = computed(() => {
  if (!textoBusqueda.value.trim()) return sucursales.value;
  const b = textoBusqueda.value.toLowerCase();
  return sucursales.value.filter(s =>
    s.NombreSucursal.toLowerCase().includes(b) ||
    s.Direccion?.toLowerCase().includes(b) ||
    s.Ciudad?.toLowerCase().includes(b) ||
    s.Telefono?.includes(b)
  );
});

const sucursalesActivas   = computed(() => sucursales.value.filter(s => s.Estado).length);
const sucursalesInactivas = computed(() => sucursales.value.filter(s => !s.Estado).length);

async function obtenerSucursales() {
  cargando.value = true;
  try {
    const res    = await fetch('/php/obtener_todas_sucursales.php');
    const result = await res.json();
    if (result.status === 'error') throw new Error(result.message);
    sucursales.value = result.data || [];
  } catch (e) {
    toast.error(`Error al cargar sucursales: ${e.message}`);
  } finally {
    cargando.value = false;
  }
}

function seleccionarSucursal(s) { sucursalSeleccionada.value = s; }

function abrirEditar(s) {
  formEditar.value = {
    SucursalId:     s.SucursalId,
    NombreSucursal: s.NombreSucursal,
    Direccion:      s.Direccion || '',
    Ciudad:         s.Ciudad    || '',
    Telefono:       s.Telefono  || '',
  };
  mostrarModalEditar.value = true;
}

function cerrarEditar() { mostrarModalEditar.value = false; }

async function guardarEditar() {
  if (!formEditar.value.NombreSucursal.trim()) {
    toast.error('El nombre es requerido');
    return;
  }
  guardando.value = true;
  try {
    const res    = await fetch('/php/editar_sucursal.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formEditar.value),
    });
    const result = await res.json();
    if (result.status === 'error') throw new Error(result.message);
    const idx = sucursales.value.findIndex(s => s.SucursalId === formEditar.value.SucursalId);
    if (idx !== -1) sucursales.value[idx] = { ...sucursales.value[idx], ...formEditar.value };
    toast.success('Sucursal actualizada correctamente');
    cerrarEditar();
  } catch (e) {
    toast.error(`Error al editar: ${e.message}`);
  } finally {
    guardando.value = false;
  }
}

async function abrirEliminar(s) {
  const ok = await confirmacionRef.value.mostrar(
    'Eliminar Sucursal',
    `¿Estás seguro de eliminar "${s.NombreSucursal}"? Esta acción no se puede deshacer.`
  );
  if (!ok) return;
  try {
    const res    = await fetch('/php/eliminar_sucursal.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ SucursalId: s.SucursalId }),
    });
    const result = await res.json();
    if (result.status === 'error') throw new Error(result.message);
    const idx = sucursales.value.findIndex(x => x.SucursalId === s.SucursalId);
    if (idx !== -1) sucursales.value[idx].Estado = 0;
    toast.success('Sucursal eliminada correctamente');
  } catch (e) {
    toast.error(`Error al eliminar: ${e.message}`);
  }
}

onMounted(obtenerSucursales);
</script>

<style scoped>
.sucursales-container { padding: 32px; max-width: 1100px; }

.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
.btn-back {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  width: 36px; height: 36px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; color: #1e293b; flex-shrink: 0;
}
.btn-back:hover { background: #f1f5f9; }
.page-titulo    { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 13px; }

/* Stats */
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
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
.stat-valor { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.stat-valor.verde { color: #16a34a; }
.stat-valor.rojo  { color: #dc2626; }

/* Tabla card */
.tabla-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.tabla-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 16px;
}
.tabla-titulo    { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.tabla-subtitulo { font-size: 13px; color: #64748b; margin: 0; }

/* Buscador */
.buscador-wrap { position: relative; }
.buscador-icono {
  position: absolute; left: 10px; top: 50%;
  transform: translateY(-50%); color: #94a3b8; pointer-events: none;
}
.input-busqueda {
  padding: 8px 12px 8px 34px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; outline: none; width: 240px;
}
.input-busqueda:focus { border-color: #2563eb; }

/* Tabla */
.tabla-wrapper { overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; }
.tabla thead { background: #f8fafc; }
.tabla th {
  padding: 12px 16px; text-align: left;
  font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}
.tabla td {
  padding: 12px 16px; font-size: 13px; color: #1e293b;
  border-bottom: 1px solid #f1f5f9;
}
.tabla tbody tr { cursor: pointer; transition: background 0.15s; }
.tabla tbody tr:hover { background: #f8fafc; }
.fila-activa { background: #eff6ff !important; }

.td-id      { color: #2563eb; font-weight: 700; width: 60px; }
.td-nombre  { font-weight: 600; }
.td-secondary { color: #64748b; }
.td-acciones { text-align: center; width: 90px; }

.badge {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.badge-activa   { background: #f0fdf4; color: #16a34a; }
.badge-inactiva { background: #fef2f2; color: #dc2626; }

.btn-accion {
  width: 30px; height: 30px; padding: 0; border: none;
  border-radius: 6px; cursor: pointer; transition: all 0.15s;
  display: inline-flex; align-items: center; justify-content: center;
  margin: 0 2px;
}
.btn-editar           { background: #eff6ff; color: #2563eb; }
.btn-editar:hover     { background: #dbeafe; }
.btn-eliminar         { background: #fef2f2; color: #dc2626; }
.btn-eliminar:hover:not(:disabled) { background: #fecaca; }
.btn-accion:disabled  { opacity: 0.4; cursor: not-allowed; }

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

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal {
  background: #fff; border-radius: 12px; width: 100%; max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 20px; border-bottom: 1px solid #e2e8f0;
}
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
.btn-x { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 16px; }
.btn-x:hover { color: #1e293b; }
.modal-body { padding: 20px; }
.campo { margin-bottom: 14px; }
.campo label {
  display: block; font-size: 12px; font-weight: 700;
  color: #64748b; text-transform: uppercase; margin-bottom: 6px;
}
.input-text {
  width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;
}
.input-text:focus { border-color: #2563eb; }
.modal-footer {
  display: flex; gap: 8px; padding: 14px 20px;
  border-top: 1px solid #e2e8f0; background: #f8fafc;
}
.btn-cancelar, .btn-guardar {
  flex: 1; padding: 10px; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s;
}
.btn-cancelar { background: #f1f5f9; color: #1e293b; }
.btn-cancelar:hover { background: #e2e8f0; }
.btn-guardar  { background: #2563eb; color: #fff; }
.btn-guardar:hover:not(:disabled) { background: #1d4ed8; }
.btn-guardar:disabled { background: #94a3b8; cursor: not-allowed; }

@media (max-width: 768px) {
  .sucursales-container { padding: 16px; }
  .tabla th, .tabla td { padding: 10px 8px; font-size: 12px; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>