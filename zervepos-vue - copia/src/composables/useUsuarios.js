// composables/useUsuarios.js
import { ref, computed } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';

export function useUsuarios() {
    const authStore = useAuthStore();
    const toast = useToast();
    
    const usuarios = ref([]);
    const cargando = ref(false);
    const sucursalesDisponibles = ref([]);
    const rolesPermitidos = ref([]);
    
    // Obtener lista de usuarios según permisos
    const obtenerUsuarios = async () => {
        cargando.value = true;
        try {
            const response = await fetch('/php/usuarios.php?action=obtener', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    empleadoId: authStore.empleadoId,
                    rol: authStore.rol
                })
            });
            
            const resultado = await response.json();
            
            if (resultado.status !== 'ok') {
                toast.registrarToast('error', 'Error al cargar usuarios', resultado.message);
                return false;
            }
            
            usuarios.value = resultado.data || [];
            return true;
        } catch (error) {
            console.error('Error en obtenerUsuarios:', error);
            toast.registrarToast('error', 'Error de conexión', error.message);
            return false;
        } finally {
            cargando.value = false;
        }
    };
    
    // Obtener sucursales disponibles para asignar (según rol)
    const obtenerSucursalesDisponibles = async () => {
        try {
            const response = await fetch('/php/usuarios.php?action=sucursales-disponibles', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    empleadoId: authStore.empleadoId,
                    rol: authStore.rol
                })
            });
            
            const resultado = await response.json();
            
            if (resultado.status !== 'ok') {
                console.error('Error al obtener sucursales:', resultado.message);
                return false;
            }
            
            sucursalesDisponibles.value = resultado.data || [];
            return true;
        } catch (error) {
            console.error('Error en obtenerSucursalesDisponibles:', error);
            return false;
        }
    };
    
    // Obtener roles que el usuario actual puede crear
    const obtenerRolesPermitidos = async () => {
        try {
            const response = await fetch('/php/usuarios.php?action=roles-permitidos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    rol: authStore.rol
                })
            });
            
            const resultado = await response.json();
            
            if (resultado.status !== 'ok') {
                console.error('Error al obtener roles:', resultado.message);
                return false;
            }
            
            rolesPermitidos.value = resultado.data || [];
            return true;
        } catch (error) {
            console.error('Error en obtenerRolesPermitidos:', error);
            return false;
        }
    };
    
    // Crear nuevo usuario
    const crearUsuario = async (datosUsuario) => {
        try {
            const payload = {
                empleadoIdSolicitante: authStore.empleadoId,
                rolSolicitante: authStore.rol,
                nombre: datosUsuario.nombre,
                apellidos: datosUsuario.apellidos,
                fechaNacimiento: datosUsuario.fechaNacimiento,
                direccion: datosUsuario.direccion,
                ciudad: datosUsuario.ciudad,
                codigoPostal: datosUsuario.codigoPostal,
                telefono: datosUsuario.telefono,
                rolNuevo: datosUsuario.rol,
                nombreUsuario: datosUsuario.nombreUsuario,
                contrasena: datosUsuario.contrasena,
                sucursales: datosUsuario.sucursales || []
            };
            
            const response = await fetch('/php/usuarios.php?action=crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const resultado = await response.json();
            
            if (resultado.status !== 'ok') {
                toast.error(`Error al crear usuario: ${resultado.message}`);
                return { success: false, message: resultado.message };
            }
            
            toast.success(`Usuario creado - ID: ${resultado.empleadoId}`);

            // Recargar lista
            await obtenerUsuarios();
            
            return { 
                success: true, 
                empleadoId: resultado.empleadoId,
                message: resultado.message 
            };
        } catch (error) {
            console.error('Error en crearUsuario:', error);
            toast.registrarToast('error', 'Error de conexión', error.message);
            return { success: false, message: error.message };
        }
    };
    
    // Computed: Filtros útiles
    const usuariosActivos = computed(() => 
        usuarios.value.filter(u => u.Estado === 1)
    );
    
    const usuariosInactivos = computed(() => 
        usuarios.value.filter(u => u.Estado === 0)
    );
    
    // Computed: ¿Puede crear usuarios?
    const puedeCrearUsuarios = computed(() => 
        authStore.rol !== 'Cajero'
    );
    
    return {
        usuarios,
        cargando,
        sucursalesDisponibles,
        rolesPermitidos,
        usuariosActivos,
        usuariosInactivos,
        puedeCrearUsuarios,
        obtenerUsuarios,
        obtenerSucursalesDisponibles,
        obtenerRolesPermitidos,
        crearUsuario
    };
}
