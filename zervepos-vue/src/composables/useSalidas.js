// src/composables/useSalidas.js

import { ref, computed } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';

export function useSalidas() {
    const toast = useToast();
    const authStore = useAuthStore();

    // ==================== ESTADO ====================
    const tipoSalida = ref('Transferencia'); // 'Transferencia', 'Merma', 'Descuento'
    const productoBuscado = ref('');
    const productosDisponibles = ref([]);
    const productosSeleccionados = ref([]);
    const motivo = ref('');
    const referencia = ref('');
    const notas = ref('');
    
    const sucursalDestino = ref(null); // Para transferencias
    const sucursales = ref([]);
    const cargando = ref(false);
    const productos = ref([]);

    // ==================== TIPOS DE SALIDA ====================
    const tiposSalida = [
        { valor: 'Transferencia', label: 'Transferencia', icono: '↔️' },
        { valor: 'Merma', label: 'Merma', icono: '⚠️' },
        { valor: 'Descuento', label: 'Descuento/Ajuste', icono: '⚙️' }
    ];

    // ==================== COMPUTED ====================

    /**
     * Resumen del movimiento
     */
    const resumen = computed(() => {
        const totalProductos = productosSeleccionados.value.length;
        const totalCantidad = productosSeleccionados.value.reduce((sum, p) => sum + (p.cantidad || 0), 0);
        const totalValor = productosSeleccionados.value.reduce((sum, p) => {
            return sum + ((p.PrecioUnitario || 0) * (p.cantidad || 0));
        }, 0);

        return {
            totalProductos,
            totalCantidad,
            totalValor: totalValor.toFixed(2)
        };
    });

    /**
     * Valida si el formulario es válido
     */
    const formularioValido = computed(() => {
        const tieneProductos = productosSeleccionados.value.length > 0 &&
            productosSeleccionados.value.every(p => p.cantidad && p.cantidad > 0);
        
        const tieneMotivo = tipoSalida.value === 'Transferencia' 
            ? sucursalDestino.value !== null
            : motivo.value?.trim().length > 0;

        return tieneProductos && tieneMotivo;
    });

    // ==================== MÉTODOS ====================

    /**
     * Obtiene los productos disponibles de la sucursal actual
     */
    const obtenerProductosSucursal = async () => {
        cargando.value = true;
        try {
            const response = await fetch(`/php/obtener_productos_sucursal.php?sucursalId=${authStore.sucursalId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'error') {
                throw new Error(result.message || 'Error desconocido');
            }

            productos.value = result.data || [];
            toast.success('Productos cargados correctamente');
        } catch (error) {
            console.error('Error al obtener productos:', error);
            toast.error(`Error al cargar productos: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    /**
     * Obtiene las sucursales (para transferencias)
     */
    const obtenerSucursales = async () => {
        try {
            const response = await fetch('/php/obtener_sucursales.php');
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'error') {
                throw new Error(result.message || 'Error desconocido');
            }

            // Filtrar la sucursal actual
            sucursales.value = (result.data || []).filter(
                s => s.SucursalId !== authStore.sucursalId
            );
        } catch (error) {
            console.error('Error al obtener sucursales:', error);
        }
    };

    /**
     * Busca productos por nombre o SKU
     */
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

    /**
     * Agrega un producto a la lista de seleccionados
     */
    const agregarProducto = (producto) => {
        const existe = productosSeleccionados.value.find(p => p.ProductoId === producto.ProductoId);
        
        if (!existe) {
            productosSeleccionados.value.push({
                ...producto,
                cantidad: 1
            });
        }

        productoBuscado.value = '';
        productosDisponibles.value = [];
        toast.success(`${producto.NombreProducto} agregado`);
    };

    /**
     * Elimina un producto de la lista
     */
    const eliminarProducto = (productoId) => {
        productosSeleccionados.value = productosSeleccionados.value.filter(
            p => p.ProductoId !== productoId
        );
    };

    /**
     * Actualiza la cantidad de un producto
     */
    const actualizarCantidad = (productoId, cantidad) => {
        const producto = productosSeleccionados.value.find(p => p.ProductoId === productoId);
        if (producto) {
            producto.cantidad = Math.max(1, parseInt(cantidad) || 0);
        }
    };

    /**
     * Registra la salida
     */
    const registrarSalida = async () => {
        if (!formularioValido.value) {
            toast.error('Complete todos los campos requeridos');
            return;
        }

        cargando.value = true;
        try {
            const payload = {
                sucursalId: authStore.sucursalId,
                empleadoId: authStore.empleadoId,
                tipo: tipoSalida.value,
                motivo: tipoSalida.value === 'Transferencia' 
                    ? `Transferencia a sucursal ${sucursalDestino.value}`
                    : motivo.value,
                detalles: productosSeleccionados.value.map(p => ({
                    productoId: p.ProductoId,
                    cantidad: p.cantidad
                }))
            };

            const response = await fetch('/php/registrar_salida.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'error') {
                throw new Error(result.message || 'Error desconocido');
            }

            toast.success('Salida registrada correctamente');
            limpiarFormulario();
            return result.data?.salidaId;
        } catch (error) {
            console.error('Error al registrar salida:', error);
            toast.error(`Error: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    /**
     * Limpia el formulario
     */
    const limpiarFormulario = () => {
        tipoSalida.value = 'Transferencia';
        productoBuscado.value = '';
        productosSeleccionados.value = [];
        motivo.value = '';
        referencia.value = '';
        notas.value = '';
        sucursalDestino.value = null;
        productosDisponibles.value = [];
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
        sucursales,
        cargando,
        productos,
        tiposSalida,

        // Computed
        resumen,
        formularioValido,

        // Métodos
        obtenerProductosSucursal,
        obtenerSucursales,
        buscarProducto,
        agregarProducto,
        eliminarProducto,
        actualizarCantidad,
        registrarSalida,
        limpiarFormulario
    };
}
