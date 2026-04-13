<template>
  <div class="clientes">
    <h1 class="page-title">Gestión de Clientes</h1>
    <p class="page-subtitle">Selecciona una opción para administrar el catálogo de clientes.</p>

    <div class="cards-grid">
      <RouterLink to="/app/clientes/nuevo" class="card">
        <div class="card__icon card__icon--orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <h2 class="card__title card__title--orange">Registrar Cliente</h2>
        <p class="card__desc">Agrega un nuevo cliente al sistema con su información de contacto, dirección y límite de crédito.</p>
        <div class="card__bar card__bar--orange"></div>
      </RouterLink>

      <RouterLink to="/app/clientes/editar" class="card">
        <div class="card__icon card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h2 class="card__title card__title--blue">Editar Cliente</h2>
        <p class="card__desc">Modifica la información, categoría o límite de crédito de un cliente ya registrado en el sistema.</p>
        <div class="card__bar card__bar--blue"></div>
      </RouterLink>

      <RouterLink to="/app/clientes/eliminar" class="card">
        <div class="card__icon card__icon--red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
        </div>
        <h2 class="card__title card__title--red">Eliminar Cliente</h2>
        <p class="card__desc">Retira de forma permanente un cliente del sistema. Esta acción requiere confirmación.</p>
        <div class="card__bar card__bar--red"></div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.clientes { padding: 1.5rem; max-width: 1200px; }
.page-title { font-size: 1.6rem; font-weight: 700; color: #1a2b3c; margin: 0 0 0.4rem; }
.page-subtitle { font-size: 0.9rem; color: #64748b; margin: 0 0 2rem; }
.cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
.card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; padding: 2rem 1.75rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; text-decoration: none; color: inherit; transition: box-shadow 0.2s, transform 0.15s; }
.card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-3px); }
.card__icon { width: 64px; height: 64px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.card__icon svg { width: 32px; height: 32px; }
.card__icon--orange { background: #fff4ed; color: #f97316; }
.card__icon--blue   { background: #eff6ff; color: #2563eb; }
.card__icon--red    { background: #fff1f1; color: #dc2626; }
.card__title { font-size: 1.1rem; font-weight: 700; margin: 0; }
.card__title--orange { color: #f97316; }
.card__title--blue   { color: #2563eb; }
.card__title--red    { color: #dc2626; }
.card__desc { font-size: 0.875rem; color: #64748b; line-height: 1.6; margin: 0; flex: 1; }
.card__bar { height: 3px; width: 40px; border-radius: 2px; margin-top: 0.5rem; }
.card__bar--orange { background: #f97316; }
.card__bar--blue   { background: #2563eb; }
.card__bar--red    { background: #dc2626; }
</style>