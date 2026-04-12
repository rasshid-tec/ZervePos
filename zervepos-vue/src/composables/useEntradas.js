// src/composables/useEntradas.js

import { ref, computed } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';

export function useEntradas() {
    const toast = useToast();
    const authStore = useAuthStore();

    // ==================== ESTADO ====================
    const tipoEntrada = ref('Compra'); // 'Compra', 'Transferencia', 'Devolución', 'Ajuste'
    const productoBuscado = ref('');
    const productosDisponibles = ref([]);
    const productosSeleccionados = ref([]);
    const motivo = ref('');
    const referencia = ref('');
    const notas = ref('');
    
    const sucursalOrigen = ref(null); // Para transferencias
    const cargando = ref(false);
    const productos = ref([]);

    // ==================== TIPOS DE ENTRADA ====================
    const tiposEntrada = [
        { valor: 'Compra', label: 'Compra', icono: '🛒' },
        { valor: 'Transferencia', label: 'Transferencia', icono: '↔️' },
        { valor: 'Devolución', label: 'Devolución', icono: '↩️' },
        { valor: 'Ajuste', label: 'Ajuste', icono: '⚙️' }
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
        return (
            tipoEntrada.value &&
            productosSeleccionados.value.length > 0 &&
            productosSeleccionados.value.every(p => p.cantidad && p.cantidad > 0)
        );
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
     * Registra la entrada
     */
    const registrarEntrada = async () => {
        if (!formularioValido.value) {
            toast.error('Complete todos los campos requeridos');
            return;
        }

        cargando.value = true;
        try {
            const payload = {
                sucursalId: authStore.sucursalId,
                empleadoId: authStore.empleadoId,
                tipo: tipoEntrada.value,
                motivo: motivo.value || referencia.value,
                detalles: productosSeleccionados.value.map(p => ({
                    productoId: p.ProductoId,
                    cantidad: p.cantidad
                }))
            };

            const response = await fetch('/php/registrar_entrada.php', {
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

            toast.success('Entrada registrada correctamente');
            limpiarFormulario();
            return result.data?.entradaId;
        } catch (error) {
            console.error('Error al registrar entrada:', error);
            toast.error(`Error: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    /**
     * Limpia el formulario
     */
    const limpiarFormulario = () => {
        tipoEntrada.value = 'Compra';
        productoBuscado.value = '';
        productosSeleccionados.value = [];
        motivo.value = '';
        referencia.value = '';
        notas.value = '';
        sucursalOrigen.value = null;
        productosDisponibles.value = [];
    };

    return {
        // Estado
        tipoEntrada,
        productoBuscado,
        productosDisponibles,
        productosSeleccionados,
        motivo,
        referencia,
        notas,
        sucursalOrigen,
        cargando,
        productos,
        tiposEntrada,

        // Computed
        resumen,
        formularioValido,

        // Métodos
        obtenerProductosSucursal,
        buscarProducto,
        agregarProducto,
        eliminarProducto,
        actualizarCantidad,
        registrarEntrada,
        limpiarFormulario
    };
}
