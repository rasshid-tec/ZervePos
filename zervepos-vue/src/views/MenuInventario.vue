<template>
  <div class="inventario-menu">

    <div class="menu-header">
      <h1>Gestión de Inventario</h1>
      <p>Selecciona una opción para administrar el inventario</p>
    </div>

    <div class="cards-grid">
      <RouterLink
        v-for="card in opciones"
        :key="card.route"
        :to="card.route"
        class="card"
      >
        <div class="card-icono" :style="{ background: card.bgColor }">
          <span v-html="card.icon" :style="{ color: card.iconColor }"></span>
        </div>
        <h3 class="card-titulo">{{ card.titulo }}</h3>
        <p class="card-desc">{{ card.desc }}</p>
        <div class="card-linea" :style="{ background: card.iconColor }"></div>
      </RouterLink>
    </div>

  </div>
</template>

<script setup>
const opciones = [
  {
    route:     '/app/inventario/entradas',
    titulo:    'Entradas de Inventario',
    desc:      'Registra productos que ingresan al almacén por compra o ajuste',
    bgColor:   '#f0fdf4',
    iconColor: '#16a34a',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12l10 10 10-10"/></svg>`,
  },
  {
    route:     '/app/inventario/salidas',
    titulo:    'Salidas de Inventario',
    desc:      'Registra productos que salen del almacén por merma, ajuste o transferencia',
    bgColor:   '#fff7ed',
    iconColor: '#f97316',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V2M2 12l10-10 10 10"/></svg>`,
  },
  {
    route:     '/app/inventario/gestion',
    titulo:    'Gestión de Inventario',
    desc:      'Consulta el stock actual de productos por sucursal',
    bgColor:   '#eff6ff',
    iconColor: '#2563eb',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>`,
  },
  {
    route:     '/app/inventario/sucursales',
    titulo:    'Gestión de Sucursales',
    desc:      'Consulta y edita la información de las sucursales',
    bgColor:   '#fdf4ff',
    iconColor: '#9333ea',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
  },
]
</script>

<style scoped>
.inventario-menu {
  padding: 32px;
  max-width: 900px;
}

.menu-header {
  margin-bottom: 32px;
}
.menu-header h1 {
  font-size: 26px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 6px;
}
.menu-header p {
  color: #64748b;
  margin: 0;
  font-size: 14px;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  transition: box-shadow 0.2s, transform 0.15s;
  position: relative;
}
.card:hover {
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
  transform: translateY(-3px);
}

.card-icono {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.card-titulo {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.card-desc {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
  flex: 1;
}

.card-linea {
  height: 3px;
  width: 36px;
  border-radius: 2px;
  margin-top: 4px;
}
</style>