<template>
  <div class="reportes-menu">

    <div class="page-header">
      <h1 class="page-titulo">Reportes</h1>
      <p class="page-subtitulo">Consulta y exporta información del negocio</p>
    </div>

    <div class="reportes-grid">
      <button
        v-for="r in reportes"
        :key="r.route"
        class="reporte-card"
        @click="$router.push(r.route)"
      >
        <div class="reporte-icono" :class="r.clase">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="r.svg"></svg>
        </div>
        <div class="reporte-info">
          <h2 class="reporte-titulo">{{ r.titulo }}</h2>
          <p class="reporte-desc">{{ r.desc }}</p>
        </div>
        <div class="reporte-exportar">
          <span class="badge-export">XLS</span>
          <span class="badge-export">PDF</span>
        </div>
        <span class="reporte-arrow">→</span>
      </button>
    </div>

  </div>
</template>

<script setup>
const reportes = [
  {
    titulo: 'Salidas de Inventario',
    desc:   'Historial de salidas por ventas y movimientos de inventario',
    clase:  'icono--naranja',
    svg:    '<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>',
    route:  '/app/reportes/salidas',
  },
  {
    titulo: 'Ventas',
    desc:   'Reporte de ventas por fecha, sucursal, cajero, caja y método de pago',
    clase:  'icono--verde',
    svg:    '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    route:  '/app/reportes/ventas',
  },
  {
    titulo: 'Créditos',
    desc:   'Saldos activos e historial de abonos por cliente',
    clase:  'icono--azul',
    svg:    '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/>',
    route:  '/app/reportes/creditos',
  },
  {
    titulo: 'Compras por Cliente',
    desc:   'Historial de compras y ventas asociadas a cada cliente',
    clase:  'icono--morado',
    svg:    '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    route:  '/app/reportes/clientes',
  },
  {
    titulo: 'Ventas por Producto',
    desc:   'Ranking de productos más vendidos con cantidad y monto total',
    clase:  'icono--rojo',
    svg:    '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>',
    route:  '/app/reportes/productos',
  },
]
</script>

<style scoped>
.reportes-menu { max-width: 900px; margin: 0 auto; }

.page-header { margin-bottom: 2rem; }
.page-titulo { font-size: 1.6rem; font-weight: 700; color: #1a2b3c; margin: 0 0 0.5rem; }
.page-subtitulo { font-size: 0.95rem; color: #64748b; margin: 0; }

.reportes-grid { display: flex; flex-direction: column; gap: 1rem; }

.reporte-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem 1.5rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.reporte-card:hover {
  border-color: #2563eb;
  box-shadow: 0 4px 16px rgba(37,99,235,0.08);
  transform: translateX(4px);
}

.reporte-icono {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.reporte-icono svg { width: 24px; height: 24px; }

.icono--naranja { background: #fff4ed; color: #f97316; }
.icono--verde   { background: #f0fdf4; color: #16a34a; }
.icono--azul    { background: #eff6ff; color: #2563eb; }
.icono--morado  { background: #faf5ff; color: #7c3aed; }
.icono--rojo    { background: #fef2f2; color: #ef4444; }

.reporte-info { flex: 1; }
.reporte-titulo { font-size: 1rem; font-weight: 700; color: #1a2b3c; margin: 0 0 0.3rem; }
.reporte-desc { font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5; }

.reporte-exportar { display: flex; gap: 6px; flex-shrink: 0; }
.badge-export {
  font-size: 0.7rem; font-weight: 700; padding: 3px 8px;
  border-radius: 6px; background: #f1f5f9; color: #64748b;
  border: 1px solid #e2e8f0;
}

.reporte-arrow {
  font-size: 1.1rem; color: #cbd5e1;
  transition: color 0.2s, transform 0.2s; flex-shrink: 0;
}
.reporte-card:hover .reporte-arrow { color: #2563eb; transform: translateX(4px); }
</style>