<template>
  <div 
    v-if="visible" 
    class="modal-overlay" 
    @click="cerrarSiBackground"
  >
    <div class="modal-contenedor">
      <div class="modal-header">
        <h2>Crear Nuevo Usuario</h2>
        <button class="btn-cerrar" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        
        <!-- SECCIÓN 1: Datos del Empleado -->
        <div class="seccion-form">
          <h3>Datos del Empleado</h3>
          
          <div class="fila-inputs">
            <div class="campo">
              <label for="nombre">Nombre <span class="requerido">*</span></label>
              <input 
                id="nombre"
                v-model="formulario.nombre" 
                type="text" 
                placeholder="Nombre"
                required
              >
            </div>
            <div class="campo">
              <label for="apellidos">Apellidos <span class="requerido">*</span></label>
              <input 
                id="apellidos"
                v-model="formulario.apellidos" 
                type="text" 
                placeholder="Apellidos"
                required
              >
            </div>
          </div>

          <div class="fila-inputs">
            <div class="campo">
              <label for="fechaNacimiento">Fecha de Nacimiento <span class="requerido">*</span></label>
              <input 
                id="fechaNacimiento"
                v-model="formulario.fechaNacimiento" 
                type="date"
                required
              >
            </div>
            <div class="campo">
              <label for="telefono">Teléfono</label>
              <input 
                id="telefono"
                v-model="formulario.telefono" 
                type="tel" 
                placeholder="10 dígitos"
              >
            </div>
          </div>

          <div class="campo-ancho">
            <label for="direccion">Domicilio</label>
            <input 
              id="direccion"
              v-model="formulario.direccion" 
              type="text" 
              placeholder="Calle y número"
            >
          </div>

          <div class="fila-inputs">
            <div class="campo">
              <label for="ciudad">Ciudad</label>
              <input 
                id="ciudad"
                v-model="formulario.ciudad" 
                type="text" 
                placeholder="Ciudad"
              >
            </div>
            <div class="campo">
              <label for="codigoPostal">Código Postal</label>
              <input 
                id="codigoPostal"
                v-model="formulario.codigoPostal" 
                type="text" 
                placeholder="CP"
              >
            </div>
          </div>

          <div class="fila-inputs">
            <div class="campo">
              <label for="rolNuevo">Rol <span class="requerido">*</span></label>
              <select 
                id="rolNuevo"
                v-model="formulario.rol" 
                @change="alCambiarRol"
                required
              >
                <option value="">-- Selecciona un rol --</option>
                <option 
                  v-for="rol in rolesPermitidos" 
                  :key="rol"
                  :value="rol"
                >
                  {{ rol }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- SECCIÓN 2: Asignación de Sucursales (si corresponde) -->
        <div v-if="mostrarSucursales" class="seccion-form">
          <h3>{{ labelSucursales }}</h3>
          
          <div v-if="formulario.rol === 'Cajero'" class="campo">
            <label for="sucursalCajero">Sucursal <span class="requerido">*</span></label>
            <select 
              id="sucursalCajero"
              v-model.number="formulario.sucursales" 
              required
            >
              <option :value="null">-- Selecciona una sucursal --</option>
              <option 
                v-for="suc in sucursalesDisponibles" 
                :key="suc.SucursalId"
                :value="suc.SucursalId"
              >
                {{ suc.NombreSucursal }}
              </option>
            </select>
          </div>

          <div v-else-if="formulario.rol === 'Administrador'" class="campo">
            <label>Sucursales <span class="requerido">*</span></label>
            <div class="checkbox-group">
              <label 
                v-for="suc in sucursalesDisponibles" 
                :key="suc.SucursalId"
                class="checkbox-label"
              >
                <input 
                  type="checkbox" 
                  :value="suc.SucursalId"
                  v-model.number="formulario.sucursales"
                >
                {{ suc.NombreSucursal }}
              </label>
            </div>
            <small v-if="formulario.sucursales.length === 0" class="error-text">
              Selecciona al menos una sucursal
            </small>
          </div>
        </div>

        <!-- SECCIÓN 3: Datos de Acceso -->
        <div class="seccion-form">
          <h3>Datos de Acceso</h3>
          
          <div class="fila-inputs">
            <div class="campo">
              <label for="nombreUsuario">Usuario <span class="requerido">*</span></label>
              <input 
                id="nombreUsuario"
                v-model="formulario.nombreUsuario" 
                type="text" 
                placeholder="Nombre de usuario"
                required
              >
            </div>
            <div class="campo">
              <label for="contrasena">Contraseña <span class="requerido">*</span></label>
              <input 
                id="contrasena"
                v-model="formulario.contrasena" 
                type="password" 
                placeholder="Contraseña"
                required
              >
            </div>
          </div>
        </div>

        <!-- BOTONES -->
        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">
            Cancelar
          </button>
          <button type="submit" class="btn-crear" :disabled="cargando">
            {{ cargando ? 'Creando...' : 'Crear Usuario' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useUsuarios } from '../composables/useUsuarios';
import { useAuthStore  } from '../stores/auth';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['cerrar', 'usuario-creado']);

const authStore = useAuthStore ();
const usuariosComposable = useUsuarios();
const rolesPermitidos = usuariosComposable.rolesPermitidos;
const sucursalesDisponibles = usuariosComposable.sucursalesDisponibles;
const cargando = usuariosComposable.cargando;
const crearUsuario = usuariosComposable.crearUsuario;

const formulario = ref({
  nombre: '',
  apellidos: '',
  fechaNacimiento: '',
  direccion: '',
  ciudad: '',
  codigoPostal: '',
  telefono: '',
  rol: '',
  nombreUsuario: '',
  contrasena: '',
  sucursales: authStore.rol === 'Administrador' ? [] : null
});

// Computed: mostrar sección de sucursales
const mostrarSucursales = computed(() => 
  formulario.value.rol !== 'Dueño' && formulario.value.rol !== ''
);

// Computed: label dinámico para sucursales
const labelSucursales = computed(() => {
  if (formulario.value.rol === 'Cajero') return 'Sucursal del Cajero';
  if (formulario.value.rol === 'Administrador') return 'Sucursales a Administrar';
  return 'Sucursales';
});

// Watch: Cuando cambia el rol, cargar sucursales disponibles
const alCambiarRol = async () => {
  // Reiniciar array de sucursales
  if (formulario.value.rol === 'Administrador') {
    formulario.value.sucursales = [];
  } else if (formulario.value.rol === 'Cajero') {
    formulario.value.sucursales = null;
  } else {
    formulario.value.sucursales = null;
  }
  
  // Cargar sucursales si es Administrador o Cajero
  if (formulario.value.rol !== 'Dueño') {
    await cargarSucursales();
  }
};

// Cargar datos iniciales cuando se abre el modal
watch(() => props.visible, async (nuevoValor) => {
  if (nuevoValor) {
    await cargarDatos();
  }
});

const cargarDatos = async () => {
  console.log('Antes de cargar roles:', rolesPermitidos.value);
  await usuariosComposable.obtenerRolesPermitidos();
  console.log('Después de cargar roles:', rolesPermitidos.value);
  await usuariosComposable.obtenerSucursalesDisponibles();
};

const cargarRolesPermitidos = async () => {
  const { rolesPermitidos: roles } = useUsuarios();
  await roles; // Esperamos que se cargue
};

const cargarSucursales = async () => {
  await useUsuarios().obtenerSucursalesDisponibles();
};

const guardar = async () => {
  // Validaciones
  if (formulario.value.rol === 'Administrador' && formulario.value.sucursales.length === 0) {
    alert('El Administrador debe tener al menos una sucursal asignada');
    return;
  }

  if (formulario.value.rol === 'Cajero' && formulario.value.sucursales === null) {
    alert('El Cajero debe tener una sucursal asignada');
    return;
  }

  // Preparar datos para enviar
  const datosUsuario = {
    nombre: formulario.value.nombre,
    apellidos: formulario.value.apellidos,
    fechaNacimiento: formulario.value.fechaNacimiento,
    direccion: formulario.value.direccion,
    ciudad: formulario.value.ciudad,
    codigoPostal: formulario.value.codigoPostal,
    telefono: formulario.value.telefono,
    rol: formulario.value.rol,
    nombreUsuario: formulario.value.nombreUsuario,
    contrasena: formulario.value.contrasena,
    sucursales: formulario.value.rol === 'Cajero' 
      ? [formulario.value.sucursales]
      : formulario.value.sucursales
  };

  const resultado = await crearUsuario(datosUsuario);

  if (resultado.success) {
    emit('usuario-creado', { empleadoId: resultado.empleadoId });
    cerrar();
  }else {
  console.error('Error:', resultado.message);  // ✅ Agrega esto para ver el error
}
};

const cerrar = () => {
  limpiarFormulario();
  emit('cerrar');
};

const cerrarSiBackground = (event) => {
  if (event.target.classList.contains('modal-overlay')) {
    cerrar();
  }
};

const limpiarFormulario = () => {
  formulario.value = {
    nombre: '',
    apellidos: '',
    fechaNacimiento: '',
    direccion: '',
    ciudad: '',
    codigoPostal: '',
    telefono: '',
    rol: '',
    nombreUsuario: '',
    contrasena: '',
    sucursales: authStore.rol === 'Administrador' ? [] : null
  };
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-contenedor {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #e5e5e5;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.btn-cerrar {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
  transition: color 0.2s;
}

.btn-cerrar:hover {
  color: #333;
}

.modal-form {
  padding: 20px;
}

.seccion-form {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.seccion-form:last-of-type {
  border-bottom: none;
}

.seccion-form h3 {
  margin: 0 0 16px 0;
  font-size: 1rem;
  color: #555;
  font-weight: 600;
}

.fila-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.campo {
  display: flex;
  flex-direction: column;
}

.campo-ancho {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
}

.campo label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 6px;
}

.requerido {
  color: #e74c3c;
}

.campo input,
.campo select {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
  font-family: inherit;
}

.campo input:focus,
.campo select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
  cursor: pointer;
  user-select: none;
}

.checkbox-label input[type="checkbox"] {
  margin-right: 8px;
  cursor: pointer;
  width: 18px;
  height: 18px;
}

.error-text {
  color: #e74c3c;
  font-size: 0.8rem;
  margin-top: 6px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e5e5e5;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancelar,
.btn-crear {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancelar {
  background-color: #ecf0f1;
  color: #333;
}

.btn-cancelar:hover {
  background-color: #d5dbdb;
}

.btn-crear {
  background-color: #27ae60;
  color: white;
}

.btn-crear:hover:not(:disabled) {
  background-color: #229954;
}

.btn-crear:disabled {
  background-color: #bdc3c7;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .modal-contenedor {
    width: 95%;
    max-height: 95vh;
  }

  .fila-inputs {
    grid-template-columns: 1fr;
  }
}
</style>
