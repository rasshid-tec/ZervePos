// src/composables/useInventario.js

import { ref, computed } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';

export function useInventario() {
    const toast = useToast();
    const authStore = useAuthStore();

    // ==================== ESTADO ====================
    const inventario = ref([]);
    const cargando = ref(false);
    const sucursalesSeleccionadas = ref([]);
    const textoBusqueda = ref('');
    const sucursales = ref([]);

    // ==================== CONFIG (Modificar aquí si es necesario) ====================
    const CONFIG = {
        UMBRAL_BAJO_STOCK: 10,  // Productos con stock < 10 son "Bajo Stock"
        UMBRAL_AGOTADO: 0       // Productos con stock = 0 son "Agotados"
    };

    // ==================== COMPUTED ====================

    /**
     * Obtiene el inventario filtrado por sucursales seleccionadas y búsqueda
     */
    const inventarioFiltrado = computed(() => {
        let resultado = inventario.value;

        // Filtrar por sucursales seleccionadas
        if (sucursalesSeleccionadas.value.length > 0) {
            resultado = resultado.filter(item =>
                sucursalesSeleccionadas.value.includes(item.SucursalId)
            );
        }

        // Filtrar por búsqueda (nombre del producto o SKU)
        if (textoBusqueda.value.trim()) {
            const busqueda = textoBusqueda.value.toLowerCase();
            resultado = resultado.filter(item =>
                item.Producto.toLowerCase().includes(busqueda) ||
                item.SKU.toString().includes(busqueda)
            );
        }

        return resultado;
    });

    /**
     * Obtiene estadísticas del inventario (total, disponibles, bajo stock, agotados)
     * Basado en las sucursales seleccionadas
     */
    const estadisticas = computed(() => {
        const data = inventarioFiltrado.value;

        // Eliminar duplicados de productos (contar una sola vez por SKU)
        const productosUnicos = new Map();

        data.forEach(item => {
            if (!productosUnicos.has(item.SKU)) {
                productosUnicos.set(item.SKU, item);
            }
        });

        const productos = Array.from(productosUnicos.values());

        return {
            totalProductos: productos.length,
            disponibles: productos.filter(p => p.Estado === 'Disponible').length,
            bajoStock: productos.filter(p => p.Estado === 'Bajo Stock').length,
            agotados: productos.filter(p => p.Estado === 'Agotado').length
        };
    });

    // ==================== MÉTODOS ====================

    /**
     * Obtiene el inventario completo desde el servidor
     */
    const obtenerInventario = async () => {
        cargando.value = true;
        try {
            const response = await fetch('/php/obtener_inventario_completo.php');
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'error') {
                throw new Error(result.message || 'Error desconocido');
            }

            inventario.value = result.data || [];

            // Extraer sucursales únicas
            if (sucursales.value.length === 0) {
                extraerSucursales();
            }

            // Seleccionar sucursales por defecto
            if (sucursalesSeleccionadas.value.length === 0) {
                if (authStore.rol === 'cajero') {
                    // Si es Cajero, seleccionar solo su sucursal
                    sucursalesSeleccionadas.value = [authStore.sucursalId];
                } else if (sucursales.value.length > 0) {
                    // Si es Admin/Dueño, seleccionar todas las sucursales
                    sucursalesSeleccionadas.value = sucursales.value.map(s => s.SucursalId);
                }
            }

            toast.success('Inventario cargado correctamente');
        } catch (error) {
            console.error('Error al obtener inventario:', error);
            toast.error(`Error al cargar inventario: ${error.message}`);
        } finally {
            cargando.value = false;
        }
    };

    /**
     * Extrae las sucursales únicas del inventario
     */
    const extraerSucursales = () => {
        const sucursalesMap = new Map();

        inventario.value.forEach(item => {
            if (!sucursalesMap.has(item.SucursalId)) {
                sucursalesMap.set(item.SucursalId, {
                    SucursalId: item.SucursalId,
                    NombreSucursal: item.NombreSucursal
                });
            }
        });

        sucursales.value = Array.from(sucursalesMap.values()).sort(
            (a, b) => a.SucursalId - b.SucursalId
        );
    };

    /**
     * Busca un producto en el inventario actual
     */
    const buscarProducto = (termino) => {
        textoBusqueda.value = termino;
    };

    /**
     * Alterna la selección de una sucursal
     */
    const alternarSucursal = (sucursalId) => {
        const index = sucursalesSeleccionadas.value.indexOf(sucursalId);
        if (index > -1) {
            sucursalesSeleccionadas.value.splice(index, 1);
        } else {
            sucursalesSeleccionadas.value.push(sucursalId);
        }
    };

    /**
     * Selecciona todas las sucursales
     */
    const seleccionarTodas = () => {
        sucursalesSeleccionadas.value = sucursales.value.map(s => s.SucursalId);
    };

    /**
     * Deselecciona todas las sucursales
     */
    const deseleccionarTodas = () => {
        sucursalesSeleccionadas.value = [];
    };

    /**
     * Verifica si una sucursal está seleccionada
     */
    const estaSucursalSeleccionada = (sucursalId) => {
        return sucursalesSeleccionadas.value.includes(sucursalId);
    };

    /**
     * Limpia los filtros
     */
    const limpiarFiltros = () => {
        textoBusqueda.value = '';
        if (authStore.rol === 'cajero') {
            sucursalesSeleccionadas.value = [authStore.sucursalId];
        } else {
            seleccionarTodas();
        }
    };

    return {
        // Estado
        inventario,
        cargando,
        sucursalesSeleccionadas,
        textoBusqueda,
        sucursales,

        // Computed
        inventarioFiltrado,
        estadisticas,

        // Métodos
        obtenerInventario,
        buscarProducto,
        alternarSucursal,
        seleccionarTodas,
        deseleccionarTodas,
        estaSucursalSeleccionada,
        extraerSucursales,
        limpiarFiltros,

        // Config
        CONFIG
    };
}