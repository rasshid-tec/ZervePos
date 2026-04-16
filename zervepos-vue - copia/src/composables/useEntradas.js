// src/composables/useEntradas.js
import { useRouter } from 'vue-router';
import { ref, computed, watch } from 'vue';
import { useToast } from './useToast';
import { useAuthStore } from '../stores/auth';
import { useSucursalStore } from '../stores/sucursal';

export function useEntradas() {
    const toast         = useToast();
    const authStore     = useAuthStore();
    const sucursalStore = useSucursalStore();
    const router = useRouter();
    // ==================== ESTADO MOVIMIENTO ====================
    const tipoEntrada             = ref('Compra');
    const productoBuscado         = ref('');
    const productosDisponibles    = ref([]);
    const productosSeleccionados  = ref([]);
    const motivo                  = ref('');
    const referencia              = ref('');
    const notas                   = ref('');
    const sucursalOrigen          = ref(null);
    const cargando                = ref(false);
    const productos               = ref([]);

    // ==================== ESTADO FACTURA ====================
    const facturado               = ref(false);
    const proveedorBuscado        = ref('');
    const proveedoresDisponibles  = ref([]);
    const proveedorSeleccionado   = ref(null);
    const mostrarModalProveedor   = ref(false);
    const rfcFactura              = ref('');
    const fechaFactura            = ref(new Date().toISOString().split('T')[0]);
    const nuevoProveedor          = ref({
        NombreEmpresa: '',
        Nombre: '',
        Apellidos: '',
        Telefono: ''
    });

    // ==================== TIPOS DE ENTRADA ====================
    const tiposEntrada = [
        { valor: 'Compra',        label: 'Compra',        icono: '🛒' },
        { valor: 'Transferencia', label: 'Transferencia', icono: '↔️' },
        { valor: 'Devolución',    label: 'Devolución',    icono: '↩️' },
        { valor: 'Ajuste',        label: 'Ajuste',        icono: '⚙️' }
    ];

    // ==================== WATCHERS ====================

    // Si cambia el tipo y no es Compra, resetear factura
    watch(tipoEntrada, (nuevoTipo) => {
        if (nuevoTipo !== 'Compra') {
            facturado.value = false;
        }
    });

    // ==================== COMPUTED ====================

    const sucursalesDisponibles = computed(() => {
        const activaId = sucursalStore.sucursalActiva?.SucursalId;
        return sucursalStore.sucursales.filter(s => s.SucursalId !== activaId);
    });

    const totalFactura = computed(() => {
        return productosSeleccionados.value.reduce((sum, p) => {
            return sum + ((p.PrecioCompra || 0) * (p.cantidad || 0));
        }, 0).toFixed(2);
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

        const tieneOrigen = tipoEntrada.value === 'Transferencia'
            ? sucursalOrigen.value !== null
            : true;

        const tieneFactura = !facturado.value || (
            proveedorSeleccionado.value !== null &&
            rfcFactura.value.trim().length > 0 &&
            fechaFactura.value
        );

        return tieneProductos && tieneOrigen && tieneFactura;
    });

    // ==================== MÉTODOS PRODUCTO ====================

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
            productosSeleccionados.value.push({
                ...producto,
                cantidad: 1,
                lote: '',
                fechaVencimiento: ''
            });
        }
        productoBuscado.value    = '';
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

    // ==================== MÉTODOS PROVEEDOR ====================

    const buscarProveedor = async (termino) => {
        proveedorBuscado.value = termino;
        if (!termino.trim()) {
            proveedoresDisponibles.value = [];
            return;
        }
        try {
            const response = await fetch(`/php/buscar_proveedores.php?busqueda=${encodeURIComponent(termino)}`);
            const result   = await response.json();
            if (result.status === 'ok') {
                proveedoresDisponibles.value = result.data || [];
            }
        } catch (error) {
            console.error('Error al buscar proveedor:', error);
        }
    };

    const seleccionarProveedor = (proveedor) => {
        proveedorSeleccionado.value    = proveedor;
        proveedorBuscado.value         = proveedor.NombreEmpresa;
        proveedoresDisponibles.value   = [];
    };

    const registrarNuevoProveedor = async () => {
        const { NombreEmpresa, Nombre, Apellidos, Telefono } = nuevoProveedor.value;
        if (!NombreEmpresa.trim()) {
            toast.error('El nombre de la empresa es requerido');
            return;
        }

        try {
            const response = await fetch('/php/registrar_proveedor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ NombreEmpresa, Nombre, Apellidos, Telefono })
            });
            const result = await response.json();

            if (result.status === 'ok') {
                proveedorSeleccionado.value  = { ...nuevoProveedor.value, ProveedorId: result.data.proveedorId };
                proveedorBuscado.value       = NombreEmpresa;
                mostrarModalProveedor.value  = false;
                nuevoProveedor.value         = { NombreEmpresa: '', Nombre: '', Apellidos: '', Telefono: '' };
                toast.success('Proveedor registrado correctamente');
            } else {
                toast.error(result.message || 'Error al registrar proveedor');
            }
        } catch (error) {
            toast.error('Error de conexión al registrar proveedor');
        }
    };

    // ==================== REGISTRAR ENTRADA ====================
const registrarEntrada = async () => {
    if (!formularioValido.value) {
        toast.error('Complete todos los campos requeridos');
        return;
    }

    cargando.value = true;
    try {
        const payload = {
            sucursalId:    sucursalStore.sucursalActiva?.SucursalId,
            empleadoId:    authStore.empleadoId,
            tipo:          tipoEntrada.value,
            motivo:        motivo.value || referencia.value,
            sucursalOrigen: tipoEntrada.value === 'Transferencia' ? sucursalOrigen.value : null,
            facturado:     facturado.value,
            proveedorId:   facturado.value ? proveedorSeleccionado.value?.ProveedorId : null,
            rfc:           facturado.value ? rfcFactura.value : null,
            fechaFactura:  facturado.value ? fechaFactura.value : null,
            detalles: productosSeleccionados.value.map(p => ({
                productoId:       p.ProductoId,
                cantidad:         p.cantidad,
                precioCompra:     p.PrecioCompra || 0,
                lote:             p.lote || null,
                fechaVencimiento: p.fechaVencimiento || null
            }))
        };

        const response = await fetch('/php/registrar_entrada_movimientos.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const result = await response.json();
        if (result.status === 'error') throw new Error(result.message || 'Error desconocido');

        toast.success('Entrada registrada correctamente');
        limpiarFormulario();
        router.push({ name: 'entrada-exito', query: { id: result.data.entradaId } });

    } catch (error) {
        console.error('Error al registrar entrada:', error);
        toast.error(`Error: ${error.message}`);
    } finally {
        cargando.value = false;
    }
};
    // ==================== LIMPIAR ====================

    const limpiarFormulario = () => {
        tipoEntrada.value            = 'Compra';
        productoBuscado.value        = '';
        productosSeleccionados.value = [];
        motivo.value                 = '';
        referencia.value             = '';
        notas.value                  = '';
        sucursalOrigen.value         = null;
        productosDisponibles.value   = [];
        facturado.value              = false;
        proveedorBuscado.value       = '';
        proveedoresDisponibles.value = [];
        proveedorSeleccionado.value  = null;
        rfcFactura.value             = '';
        fechaFactura.value           = new Date().toISOString().split('T')[0];
    };

    return {
        // Estado movimiento
        tipoEntrada, productoBuscado, productosDisponibles,
        productosSeleccionados, motivo, referencia, notas,
        sucursalOrigen, cargando, productos, tiposEntrada,
        // Estado factura
        facturado, proveedorBuscado, proveedoresDisponibles,
        proveedorSeleccionado, mostrarModalProveedor, nuevoProveedor,
        rfcFactura, fechaFactura,
        // Computed
        resumen, formularioValido, sucursalesDisponibles, totalFactura,
        // Métodos
        obtenerProductosSucursal, buscarProducto, agregarProducto,
        eliminarProducto, actualizarCantidad, registrarEntrada, limpiarFormulario,
        buscarProveedor, seleccionarProveedor, registrarNuevoProveedor
    };
}