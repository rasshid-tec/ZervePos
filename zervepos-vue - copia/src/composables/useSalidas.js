// src/composables/useSalidas.js

import { ref, computed } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';
import { useSucursalStore } from '../stores/sucursal';
import { useRouter } from 'vue-router';

export function useSalidas() {
    const toast         = useToast();
    const authStore     = useAuthStore();
    const sucursalStore = useSucursalStore();
    const router        = useRouter();

    // ==================== ESTADO ====================
    const tipoSalida             = ref('Transferencia');
    const productoBuscado        = ref('');
    const productosDisponibles   = ref([]);
    const productosSeleccionados = ref([]);
    const motivo                 = ref('');
    const referencia             = ref('');
    const notas                  = ref('');
    const sucursalDestino        = ref(null);
    const cargando               = ref(false);
    const productos              = ref([]);

    // ==================== TIPOS DE SALIDA ====================
    const tiposSalida = [
        { valor: 'Transferencia', label: 'Transferencia',    icono: '↔️' },
        { valor: 'Merma',         label: 'Merma',            icono: '⚠️' },
        { valor: 'Descuento',     label: 'Descuento/Ajuste', icono: '⚙️' }
    ];

    // ==================== COMPUTED ====================

    const sucursalesDisponibles = computed(() => {
        const activaId = sucursalStore.sucursalActiva?.SucursalId;
        return sucursalStore.sucursales.filter(s => s.SucursalId !== activaId);
    });

    const resumen = computed(() => {
        const totalProductos = productosSeleccionados.value.length;
        const totalCantidad  = productosSeleccionados.value.reduce((sum, p) => sum + (p.cantidad || 0), 0);
        const totalValor     = productosSeleccionados.value.reduce((sum, p) => {
            return sum + ((p.PrecioCompra || 0) * (p.cantidad || 0));
        }, 0);
        return { totalProductos, totalCantidad, totalValor: totalValor.toFixed(2) };
    });

    const formularioValido = computed(() => {
        const tieneProductos = productosSeleccionados.value.length > 0 &&
            productosSeleccionados.value.every(p => p.cantidad && p.cantidad > 0);

        const tieneMotivo = tipoSalida.value === 'Transferencia'
            ? sucursalDestino.value !== null
            : motivo.value?.trim().length > 0;

        return tieneProductos && tieneMotivo;
    });

    // ==================== MÉTODOS ====================

    const obtenerProductosSucursal = async () => {
        const sucursalId = sucursalStore.sucursalActiva?.SucursalId;
        if (!sucursalId) {
            toast.error('Error: No hay sucursal seleccionada');
            return;
        }

        cargando.value = true;
        try {
            const response = await fetch(`/php/obtener_productos_sucursal.php?sucursalId=${sucursalId}`);
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const result = await response.json();
            if (result.status === 'error') throw new Error(result.message || 'Error desconocido');

            productos.value = result.data || [];
            toast.success('Productos cargados correctamente');
        } catch (error) {
            console.error('Error al obtener productos:', error);
            toast.error(`Error al cargar productos: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    const buscarProducto = (termino) => {
        productoBuscado.value = termino;
        if (!termino.trim()) {
            productosDisponibles.value = [];
            return;
        }
        const busqueda = termino.toLowerCase();
        productosDisponibles.value = productos.value.filter(p =>
            p.NombreProducto.toLowerCase().includes(busqueda) ||
            p.ProductoId.toString().includes(busqueda)
        );
    };

    const agregarProducto = (producto) => {
        const existe = productosSeleccionados.value.find(p => p.ProductoId === producto.ProductoId);
        if (!existe) {
            productosSeleccionados.value.push({ ...producto, cantidad: 1 });
        }
        productoBuscado.value      = '';
        productosDisponibles.value = [];
        toast.success(`${producto.NombreProducto} agregado`);
    };

    const eliminarProducto = (productoId) => {
        productosSeleccionados.value = productosSeleccionados.value.filter(
            p => p.ProductoId !== productoId
        );
    };

    const actualizarCantidad = (productoId, cantidad) => {
        const producto = productosSeleccionados.value.find(p => p.ProductoId === productoId);
        if (producto) {
            producto.cantidad = Math.max(1, parseInt(cantidad) || 0);
        }
    };

    // ==================== VALIDACIÓN FRONTEND ====================

    const validarStock = () => {
        for (const p of productosSeleccionados.value) {
            const stockDisponible = parseInt(p.Inventario) || 0;
            if (p.cantidad > stockDisponible) {
                toast.error(
                    `Stock insuficiente para "${p.NombreProducto}": ` +
                    `solicitado ${p.cantidad}, disponible ${stockDisponible}`
                );
                return false;
            }
        }
        return true;
    };

    // ==================== REGISTRAR SALIDA ====================

    const registrarSalida = async () => {
        if (!formularioValido.value) {
            toast.error('Complete todos los campos requeridos');
            return;
        }

        // Validar stock en frontend antes de llamar al backend
        if (!validarStock()) return;

        cargando.value = true;
        try {
            const payload = {
                sucursalId:      sucursalStore.sucursalActiva?.SucursalId,
                empleadoId:      authStore.empleadoId,
                tipo:            tipoSalida.value,
                motivo:          tipoSalida.value === 'Transferencia'
                                    ? `Transferencia a sucursal ${sucursalDestino.value}`
                                    : motivo.value,
                sucursalDestino: tipoSalida.value === 'Transferencia' ? sucursalDestino.value : null,
                detalles: productosSeleccionados.value.map(p => ({
                    productoId: p.ProductoId,
                    cantidad:   p.cantidad
                }))
            };

            const response = await fetch('/php/registrar_salida_movimientos.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload)
            });

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const result = await response.json();
            if (result.status === 'error') throw new Error(result.message || 'Error desconocido');

            toast.success('Salida registrada correctamente');
            limpiarFormulario();
            router.push({ name: 'salida-exito', query: { id: result.data.salidaId } });
        } catch (error) {
            console.error('Error al registrar salida:', error);
            toast.error(`Error: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    // ==================== LIMPIAR ====================

    const limpiarFormulario = () => {
        tipoSalida.value             = 'Transferencia';
        productoBuscado.value        = '';
        productosSeleccionados.value = [];
        motivo.value                 = '';
        referencia.value             = '';
        notas.value                  = '';
        sucursalDestino.value        = null;
        productosDisponibles.value   = [];
    };

    return {
        // Estado
        tipoSalida,
        productoBuscado,
        productosDisponibles,
        productosSeleccionados,
        motivo,
        referencia,
        notas,
        sucursalDestino,
        cargando,
        productos,
        tiposSalida,

        // Computed
        resumen,
        formularioValido,
        sucursalesDisponibles,

        // Métodos
        obtenerProductosSucursal,
        buscarProducto,
        agregarProducto,
        eliminarProducto,
        actualizarCantidad,
        registrarSalida,
        limpiarFormulario
    };
}