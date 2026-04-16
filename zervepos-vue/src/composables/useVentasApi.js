/**
 * useVentasApi.js
 * Centraliza todas las llamadas al backend PHP del módulo de ventas.
 * Ubicación: src/composables/useVentasApi.js
 *
 * Las rutas /api/* las redirige el proxy de Vite (ver vite.config.js)
 * hacia http://localhost/php/*
 */

const BASE = '/php';

async function getJson(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}

async function postJson(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || `HTTP ${res.status}`);
  }
  return res.json();
}

export function useVentasApi() {
  return {
    // Productos
    buscarProducto: (termino, sucursalId, nivelId = 4) =>
      getJson(`${BASE}/productos/buscar.php?termino=${encodeURIComponent(termino)}&SucursalId=${sucursalId}&NivelId=${nivelId}`),

    obtenerPrecio: (productoId, nivelId) =>
      getJson(`${BASE}/productos/precio.php?ProductoId=${productoId}&NivelId=${nivelId}`),

    // Crédito
    verificarCredito: (clienteId) =>
      getJson(`${BASE}/creditos/verificar.php?ClienteId=${clienteId}`),

    // Ventas
    registrarVenta: (payload) =>
      postJson(`${BASE}/ventas/registrar.php`, payload),

    obtenerTicket: (ventaId) =>
      getJson(`${BASE}/ventas/ticket.php?VentaId=${ventaId}`),

    // Caja
    abrirCaja: (sucursalId, empleadoId, montoInicial) =>
      postJson(`${BASE}/caja/abrir.php`, { SucursalId: sucursalId, EmpleadoId: empleadoId, MontoInicial: montoInicial }),

    cerrarCaja: (cajaId, montoFinal) =>
      postJson(`${BASE}/caja/cerrar.php`, { CajaId: cajaId, MontoFinal: montoFinal }),

    movimientoCaja: (data) =>
      postJson(`${BASE}/caja/movimiento.php`, data),

    resumenCaja: (cajaId) =>
      getJson(`${BASE}/caja/resumen.php?CajaId=${cajaId}`),
  };
}