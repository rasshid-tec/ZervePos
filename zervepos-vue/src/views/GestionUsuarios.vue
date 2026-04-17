<template>
  <div class="usuarios-container">

    <div class="page-header">
      <div>
        <h1 class="page-titulo">Gestión de Usuarios</h1>
        <p class="page-subtitulo">Administra los usuarios del sistema</p>
      </div>
      <button v-if="puedeCrearUsuarios" class="btn-nuevo" @click="mostrarModal = true">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo Usuario
      </button>
    </div>

    <!-- Controles -->
    <div class="controles">
      <div class="buscador-wrap">
        <svg class="buscador-icono" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar por nombre, usuario o rol..."
          class="input-busqueda"
        />
      </div>
      <select v-model="filtroEstado" class="input-select">
        <option value="">Todos los estados</option>
        <option value="1">Activos</option>
        <option value="0">Inactivos</option>
      </select>
    </div>

    <!-- Cargando -->
    <div v-if="cargando" class="estado-vacio">
      <span class="spinner"></span>
      <p>Cargando usuarios...</p>
    </div>

    <!-- Tabla -->
    <div v-else-if="usuariosFiltrados.length > 0" class="tabla-card">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Sucursal</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(u, idx) in usuariosFiltrados" :key="u.UsuariosId">
            <td class="td-num">{{ idx + 1 }}</td>
            <td class="td-nombre">{{ u.Nombre }} {{ u.Apellidos }}</td>
            <td>
              <span class="tag-usuario">{{ u.NombreUsuario }}</span>
            </td>
            <td>
              <span :class="['badge-rol', `rol-${u.Rol.toLowerCase()}`]">
                {{ u.Rol }}
              </span>
            </td>
            <td class="td-secondary">{{ u.NombreSucursal }}</td>
            <td>
              <span :class="['badge-estado', u.Estado === 1 ? 'activo' : 'inactivo']">
                {{ u.EstadoTexto }}
              </span>
            </td>
            <td class="td-acciones">
              <button class="btn-accion btn-editar" @click="editarUsuario(u)" title="Editar">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Sin resultados -->
    <div v-else class="estado-vacio">
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <p>{{ mensajeSinResultados }}</p>
    </div>

    <FormularioUsuarioModal
      :visible="mostrarModal"
      @cerrar="mostrarModal = false"
      @usuario-creado="alUsuarioCreado"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import FormularioUsuarioModal from '../components/FormularioUsuarioModal.vue';
import { useUsuarios }  from '../composables/useUsuarios';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const { usuarios, cargando, puedeCrearUsuarios, obtenerUsuarios } = useUsuarios();

const mostrarModal = ref(false);
const busqueda     = ref('');
const filtroEstado = ref('');

onMounted(async () => await obtenerUsuarios());

const usuariosFiltrados = computed(() => {
  let r = usuarios.value;
  if (busqueda.value) {
    const t = busqueda.value.toLowerCase();
    r = r.filter(u =>
      `${u.Nombre} ${u.Apellidos}`.toLowerCase().includes(t) ||
      (u.NombreUsuario || '').toLowerCase().includes(t) ||
      (u.Rol || '').toLowerCase().includes(t)
    );
  }
  if (filtroEstado.value) {
    r = r.filter(u => u.Estado.toString() === filtroEstado.value);
  }
  return r;
});

const mensajeSinResultados = computed(() =>
  busqueda.value || filtroEstado.value
    ? 'No se encontraron usuarios que coincidan con los filtros.'
    : 'No hay usuarios registrados.'
);

const editarUsuario   = (u) => console.log('Editar:', u);
const alUsuarioCreado = async () => {};
</script>

<style scoped>
.usuarios-container { padding: 32px; max-width: 1100px; }

.page-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
}
.page-titulo    { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0 0 6px; }
.page-subtitulo { color: #64748b; margin: 0; font-size: 14px; }

.btn-nuevo {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px; background: #2563eb; color: #fff;
  border: none; border-radius: 8px; font-size: 14px;
  font-weight: 600; cursor: pointer; white-space: nowrap;
  transition: background 0.15s;
}
.btn-nuevo:hover { background: #1d4ed8; }

/* Controles */
.controles {
  display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
}
.buscador-wrap { position: relative; flex: 1; min-width: 240px; }
.buscador-icono {
  position: absolute; left: 10px; top: 50%;
  transform: translateY(-50%); color: #94a3b8; pointer-events: none;
}
.input-busqueda {
  width: 100%; padding: 9px 12px 9px 34px;
  border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; outline: none; box-sizing: border-box;
}
.input-busqueda:focus { border-color: #2563eb; }
.input-select {
  padding: 9px 12px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; outline: none;
  background: #fff; cursor: pointer; color: #1e293b;
}
.input-select:focus { border-color: #2563eb; }

/* Tabla */
.tabla-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
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
.tabla tbody tr:hover { background: #f8fafc; }
.tabla tbody tr:last-child td { border-bottom: none; }

.td-num      { color: #94a3b8; font-size: 12px; width: 40px; }
.td-nombre   { font-weight: 600; }
.td-secondary { color: #64748b; }
.td-acciones { text-align: center; width: 60px; }

.tag-usuario {
  display: inline-block; background: #f1f5f9; color: #475569;
  padding: 3px 8px; border-radius: 6px; font-size: 12px;
  font-family: 'Courier New', monospace;
}

.badge-rol {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.rol-dueño          { background: #eff6ff; color: #2563eb; }
.rol-administrador  { background: #fff7ed; color: #f97316; }
.rol-cajero         { background: #f0fdf4; color: #16a34a; }

.badge-estado {
  display: inline-block; padding: 3px 10px;
  border-radius: 999px; font-size: 11px; font-weight: 700;
}
.badge-estado.activo   { background: #f0fdf4; color: #16a34a; }
.badge-estado.inactivo { background: #fef2f2; color: #dc2626; }

.btn-accion {
  width: 30px; height: 30px; padding: 0; border: none;
  border-radius: 6px; cursor: pointer; transition: all 0.15s;
  display: inline-flex; align-items: center; justify-content: center;
}
.btn-editar       { background: #eff6ff; color: #2563eb; }
.btn-editar:hover { background: #dbeafe; }

.estado-vacio {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 48px; gap: 12px;
  color: #94a3b8; font-size: 14px;
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px;
}
.spinner {
  width: 28px; height: 28px; border: 3px solid #e2e8f0;
  border-top-color: #2563eb; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .usuarios-container { padding: 16px; }
  .tabla th, .tabla td { padding: 10px 8px; font-size: 12px; }
}
</style>