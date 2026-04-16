<template>
  <div class="coming-soon">
    <span class="cs-icon">🚧</span>
    <h2 class="cs-title">Módulo en construcción</h2>
    <p class="cs-text">Esta sección estará disponible próximamente.</p>
  </div>
</template>

<style scoped>
.coming-soon {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  gap: 12px;
  text-align: center;
}

.cs-icon  { font-size: 3rem; }
.cs-title { font-size: 1.35rem; color: #444; margin: 0; }
.cs-text  { font-size: 0.93rem; color: #888; margin: 0; }
</style>