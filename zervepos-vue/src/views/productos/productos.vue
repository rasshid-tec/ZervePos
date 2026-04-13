<template>
  <div class="productos">
    <h1 class="page-title">Gestión de Productos</h1>
    <p class="page-subtitle">Selecciona una opción para administrar el catálogo de productos.</p>

    <div class="cards-grid">
      <RouterLink to="/app/productos/nuevo" class="card">
        <div class="card__icon card__icon--orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><line x1="12" y1="12" x2="12" y2="21"/><line x1="17" y1="9" x2="12" y2="12"/><line x1="7" y1="9" x2="12" y2="12"/><circle cx="18" cy="18" r="3" fill="currentColor" stroke="none"/><line x1="18" y1="16" x2="18" y2="20" stroke="white" stroke-width="1.5"/><line x1="16" y1="18" x2="20" y2="18" stroke="white" stroke-width="1.5"/></svg>
        </div>
        <h2 class="card__title card__title--orange">Registrar Producto</h2>
        <p class="card__desc">Agrega un nuevo producto al catálogo con todos sus datos, precios y configuración de márgenes.</p>
        <div class="card__bar card__bar--orange"></div>
      </RouterLink>

      <RouterLink to="/app/productos/editar" class="card">
        <div class="card__icon card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><circle cx="12" cy="12" r="3"/><line x1="18.5" y1="18.5" x2="21" y2="21"/></svg>
        </div>
        <h2 class="card__title card__title--blue">Editar Producto</h2>
        <p class="card__desc">Modifica la información, precios o existencias de un producto ya registrado en el sistema.</p>
        <div class="card__bar card__bar--blue"></div>
      </RouterLink>

      <RouterLink to="/app/productos/eliminar" class="card">
        <div class="card__icon card__icon--red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h2 class="card__title card__title--red">Eliminar Producto</h2>
        <p class="card__desc">Retira de forma permanente un producto del catálogo. Esta acción requiere confirmación.</p>
        <div class="card__bar card__bar--red"></div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.productos {
  padding: 1.5rem;
  max-width: 1200px;
}
.page-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0 0 0.4rem;
}
.page-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0 0 2rem;
}
.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
}
.card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 2rem 1.75rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  position: relative;
  overflow: hidden;
  transition: box-shadow 0.2s, transform 0.15s;
}
.card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  transform: translateY(-3px);
}
.card__icon {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card__icon svg { width: 32px; height: 32px; }
.card__icon--orange { background: #fff4ed; color: #f97316; }
.card__icon--blue   { background: #eff6ff; color: #2563eb; }
.card__icon--red    { background: #fff1f1; color: #dc2626; }
.card__title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}
.card__title--orange { color: #f97316; }
.card__title--blue   { color: #2563eb; }
.card__title--red    { color: #dc2626; }
.card__desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.6;
  margin: 0;
  flex: 1;
}
.card__bar {
  height: 3px;
  width: 40px;
  border-radius: 2px;
  margin-top: 0.5rem;
}
.card__bar--orange { background: #f97316; }
.card__bar--blue   { background: #2563eb; }
.card__bar--red    { background: #dc2626; }
</style>