<template>
  <div class="gestion-usuarios">
    <div class="encabezado">
      <h1>Gestión de Usuarios</h1>
      <button 
        v-if="puedeCrearUsuarios"
        class="btn-agregar" 
        @click="mostrarModal = true"
      >
        + Nuevo Usuario
      </button>
    </div>

    <div class="barra-herramientas">
      <input 
        v-model="busqueda" 
        type="text" 
        class="campo-busqueda"
        placeholder="Buscar por nombre, usuario o rol..."
      >
      <div class="filtros">
        <select v-model="filtroEstado" class="select-filtro">
          <option value="">Todos los estados</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="cargando" class="estado-carga">
      <p>Cargando usuarios...</p>
    </div>

    <!-- Tabla de usuarios -->
    <div v-else-if="usuariosFiltrados.length > 0" class="tabla-contenedor">
      <table class="tabla-usuarios">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre Completo</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Sucursal</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(usuario, idx) in usuariosFiltrados" :key="usuario.UsuariosId">
            <td>{{ idx + 1 }}</td>
            <td class="nombre-celda">
              {{ usuario.Nombre }} {{ usuario.Apellidos }}
            </td>
            <td>
              <span class="tag-usuario">{{ usuario.NombreUsuario }}</span>
            </td>
            <td>
              <span :class="['badge', `badge-${usuario.Rol.toLowerCase()}`]">
                {{ usuario.Rol }}
              </span>
            </td>
            <td>{{ usuario.NombreSucursal }}</td>
            <td>
              <span :class="['badge-estado', usuario.Estado === 1 ? 'activo' : 'inactivo']">
                {{ usuario.EstadoTexto }}
              </span>
            </td>
            <td class="celda-acciones">
              <button class="btn-accion btn-editar" @click="editarUsuario(usuario)">
                Editar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Sin resultados -->
    <div v-else class="sin-resultados">
      <p>{{ mensajeSinResultados }}</p>
    </div>

    <!-- Modal de crear usuario -->
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
import { useUsuarios } from '../composables/useUsuarios';
import { useAuth } from '../stores/auth';

const authStore = useAuth();
const {
  usuarios,
  cargando,
  puedeCrearUsuarios,
  obtenerUsuarios
} = useUsuarios();

const mostrarModal = ref(false);
const busqueda = ref('');
const filtroEstado = ref('');

// Cargar datos al montar el componente
onMounted(async () => {
  await obtenerUsuarios();
});

// Computed: Usuarios filtrados por búsqueda y estado
const usuariosFiltrados = computed(() => {
  let resultado = usuarios.value;

  // Filtrar por búsqueda
  if (busqueda.value) {
    const texto = busqueda.value.toLowerCase();
    resultado = resultado.filter(u => {
      const nombreCompleto = `${u.Nombre} ${u.Apellidos}`.toLowerCase();
      const usuario = (u.NombreUsuario || '').toLowerCase();
      const rol = (u.Rol || '').toLowerCase();
      
      return nombreCompleto.includes(texto) || 
             usuario.includes(texto) || 
             rol.includes(texto);
    });
  }

  // Filtrar por estado
  if (filtroEstado.value) {
    resultado = resultado.filter(u => 
      u.Estado.toString() === filtroEstado.value
    );
  }

  return resultado;
});

// Computed: Mensaje cuando no hay resultados
const mensajeSinResultados = computed(() => {
  if (busqueda.value || filtroEstado.value) {
    return 'No se encontraron usuarios que coincidan con los filtros.';
  }
  return 'No hay usuarios registrados.';
});

// Métodos
const editarUsuario = (usuario) => {
  console.log('Editar usuario:', usuario);
  // TODO: Implementar edición en el futuro
};

const alUsuarioCreado = async (datos) => {
  console.log('Usuario creado:', datos);
  // La lista se recarga automáticamente en el composable
};
</script>

<style scoped>
.gestion-usuarios {
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.encabezado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.encabezado h1 {
  margin: 0;
  font-size: 2rem;
  color: #333;
}

.btn-agregar {
  padding: 10px 20px;
  background-color: #27ae60;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-agregar:hover {
  background-color: #229954;
}

.barra-herramientas {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.campo-busqueda {
  flex: 1;
  min-width: 250px;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.campo-busqueda:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.filtros {
  display: flex;
  gap: 8px;
}

.select-filtro {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  background-color: white;
  cursor: pointer;
}

.estado-carga,
.sin-resultados {
  text-align: center;
  padding: 40px 20px;
  background: white;
  border-radius: 8px;
  color: #666;
}

.tabla-contenedor {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tabla-usuarios {
  width: 100%;
  border-collapse: collapse;
}

.tabla-usuarios thead {
  background-color: #f5f5f5;
  border-bottom: 2px solid #e0e0e0;
}

.tabla-usuarios th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.tabla-usuarios td {
  padding: 12px 16px;
  border-bottom: 1px solid #e8e8e8;
  color: #555;
}

.tabla-usuarios tbody tr:hover {
  background-color: #fafafa;
}

.nombre-celda {
  font-weight: 500;
  color: #333;
}

.tag-usuario {
  display: inline-block;
  background-color: #ecf0f1;
  color: #34495e;
  padding: 4px 8px;
  border-radius: 3px;
  font-size: 0.85rem;
  font-family: 'Courier New', monospace;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 3px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.badge-dueño {
  background-color: #d6eaf8;
  color: #084594;
}

.badge-administrador {
  background-color: #fef5e7;
  color: #c0932b;
}

.badge-cajero {
  background-color: #e8f8f5;
  color: #0e6251;
}

.badge-estado {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 3px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-estado.activo {
  background-color: #d5f4e6;
  color: #27ae60;
}

.badge-estado.inactivo {
  background-color: #fadbd8;
  color: #e74c3c;
}

.celda-acciones {
  text-align: center;
}

.btn-accion {
  padding: 6px 12px;
  border: none;
  border-radius: 3px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-editar {
  background-color: #3498db;
  color: white;
}

.btn-editar:hover {
  background-color: #2980b9;
}

@media (max-width: 768px) {
  .tabla-contenedor {
    overflow-x: auto;
  }

  .tabla-usuarios {
    font-size: 0.85rem;
  }

  .tabla-usuarios th,
  .tabla-usuarios td {
    padding: 8px 10px;
  }

  .encabezado {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .barra-herramientas {
    flex-direction: column;
  }

  .campo-busqueda {
    min-width: 100%;
  }
}
</style>
