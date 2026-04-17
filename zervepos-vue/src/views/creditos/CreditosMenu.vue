<template>
  <div class="creditos-menu">

    <div class="page-header">
      <h1 class="page-titulo">Créditos</h1>
      <p class="page-subtitulo">Gestiona los créditos y pagos de tus clientes</p>
    </div>

    <div class="opciones-grid">

      <!-- Consultar créditos -->
      <button class="opcion-card" @click="$router.push('/app/creditos/consultar')">
        <div class="opcion-icono opcion-icono--azul">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <line x1="9" y1="12" x2="15" y2="12"/>
            <line x1="9" y1="16" x2="13" y2="16"/>
          </svg>
        </div>
        <div class="opcion-info">
          <h2 class="opcion-titulo">Consultar Créditos</h2>
          <p class="opcion-desc">Revisa los créditos activos y el historial de cada cliente</p>
        </div>
        <span class="opcion-arrow">→</span>
      </button>

      <!-- Pagar créditos -->
      <button class="opcion-card" @click="$router.push('/app/creditos/pagar')">
        <div class="opcion-icono opcion-icono--verde">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <line x1="2" y1="10" x2="22" y2="10"/>
            <line x1="6" y1="15" x2="10" y2="15"/>
          </svg>
        </div>
        <div class="opcion-info">
          <h2 class="opcion-titulo">Pagar Créditos</h2>
          <p class="opcion-desc">Registra pagos parciales o totales de créditos pendientes</p>
        </div>
        <span class="opcion-arrow">→</span>
      </button>

    </div>

  </div>
</template>

<script setup>
</script>

<style scoped>
.creditos-menu {
  max-width: 800px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-titulo {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0 0 0.5rem;
}

.page-subtitulo {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0;
}

.opciones-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.opcion-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.75rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.opcion-card:hover {
  border-color: #2563eb;
  box-shadow: 0 4px 16px rgba(37,99,235,0.1);
  transform: translateY(-2px);
}

.opcion-icono {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.opcion-icono svg {
  width: 26px;
  height: 26px;
}

.opcion-icono--azul { background: #eff6ff; color: #2563eb; }
.opcion-icono--verde { background: #f0fdf4; color: #16a34a; }

.opcion-info {
  flex: 1;
}

.opcion-titulo {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0 0 0.35rem;
}

.opcion-desc {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.opcion-arrow {
  font-size: 1.2rem;
  color: #cbd5e1;
  transition: color 0.2s, transform 0.2s;
}

.opcion-card:hover .opcion-arrow {
  color: #2563eb;
  transform: translateX(4px);
}
</style>