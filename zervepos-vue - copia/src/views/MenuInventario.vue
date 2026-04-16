<template>
  <div class="menu-inventario-container">
    <div class="menu-contenido">
      <div class="menu-header">
        <h1>Gestión de Inventario</h1>
        <p>Selecciona el módulo que deseas utilizar</p>
      </div>

      <div class="menu-opciones">
        <!-- OPCIÓN 1: ENTRADAS DE INVENTARIO -->
        <router-link to="/app/inventario/entradas" class="opcion-card entradas">
          <div class="opcion-icono">
            <span>⬇️</span>
          </div>
          <div class="opcion-contenido">
            <h2>Entradas de Inventario</h2>
            <p>Registrar productos que ingresan al almacén</p>
          </div>
          <div class="opcion-arrow">→</div>
        </router-link>

        <!-- OPCIÓN 2: SALIDAS DE INVENTARIO -->
        <router-link to="/app/inventario/salidas" class="opcion-card salidas">
          <div class="opcion-icono">
            <span>⬆️</span>
          </div>
          <div class="opcion-contenido">
            <h2>Salidas de Inventario</h2>
            <p>Registrar productos que salen del almacén</p>
          </div>
          <div class="opcion-arrow">→</div>
        </router-link>

        <!-- OPCIÓN 3: GESTIÓN DE INVENTARIO -->
        <router-link to="/app/inventario/gestion" class="opcion-card gestion">
          <div class="opcion-icono">
            <span>📦</span>
          </div>
          <div class="opcion-contenido">
            <h2>Gestión de Inventario</h2>
            <p>Ver y gestionar el inventario actual de productos</p>
          </div>
          <div class="opcion-arrow">→</div>
        </router-link>

        <!-- OPCIÓN 4: GESTIÓN DE SUCURSALES -->
        <router-link to="/app/inventario/sucursales" class="opcion-card sucursales">
          <div class="opcion-icono">
            <span>🏢</span>
          </div>
          <div class="opcion-contenido">
            <h2>Gestión de Sucursales</h2>
            <p>Ver y gestionar información de sucursales</p>
          </div>
          <div class="opcion-arrow">→</div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Sin lógica adicional
</script>

<style scoped>
/* ==================== VARIABLES ==================== */
:root {
  --color-bg: #f5f5f5;
  --color-bg-card: #ffffff;
  --color-border: #e0e0e0;
  --color-text: #333333;
  --color-text-secondary: #666666;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  --color-primary: #0066cc;
}

/* ==================== CONTENEDOR ==================== */
.menu-inventario-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

.menu-contenido {
  padding: 0;
}

/* ==================== HEADER ==================== */
.menu-header {
  margin-bottom: 3rem;
  text-align: center;
}

.menu-header h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  color: var(--color-text);
}

.menu-header p {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: 1rem;
}

/* ==================== GRID DE OPCIONES ==================== */
.menu-opciones {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* ==================== TARJETA DE OPCIÓN ==================== */
.opcion-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: var(--color-bg-card);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  padding: 1.5rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.opcion-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
  pointer-events: none;
}

.opcion-card:hover::before {
  left: 100%;
}

.opcion-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  border-color: var(--color-primary);
}

/* Variantes de color por tipo */
.opcion-card.entradas {
  border-left: 4px solid var(--color-success);
}

.opcion-card.entradas:hover {
  box-shadow: 0 12px 24px rgba(16, 185, 129, 0.2);
}

.opcion-card.salidas {
  border-left: 4px solid var(--color-warning);
}

.opcion-card.salidas:hover {
  box-shadow: 0 12px 24px rgba(245, 158, 11, 0.2);
}

.opcion-card.gestion {
  border-left: 4px solid var(--color-primary);
}

.opcion-card.gestion:hover {
  box-shadow: 0 12px 24px rgba(0, 102, 204, 0.2);
}

.opcion-card.sucursales {
  border-left: 4px solid var(--color-danger);
}

.opcion-card.sucursales:hover {
  box-shadow: 0 12px 24px rgba(239, 68, 68, 0.2);
}

/* ==================== ICONO ==================== */
.opcion-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 12px;
  font-size: 2rem;
  flex-shrink: 0;
  background: #f0f0f0;
}

.opcion-card.entradas .opcion-icono {
  background: rgba(16, 185, 129, 0.1);
}

.opcion-card.salidas .opcion-icono {
  background: rgba(245, 158, 11, 0.1);
}

.opcion-card.gestion .opcion-icono {
  background: rgba(0, 102, 204, 0.1);
}

.opcion-card.sucursales .opcion-icono {
  background: rgba(239, 68, 68, 0.1);
}

/* ==================== CONTENIDO ==================== */
.opcion-contenido {
  flex: 1;
}

.opcion-contenido h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: var(--color-text);
}

.opcion-contenido p {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}

/* ==================== FLECHA ==================== */
.opcion-arrow {
  font-size: 1.5rem;
  color: var(--color-primary);
  transition: transform 0.3s ease;
  flex-shrink: 0;
}

.opcion-card:hover .opcion-arrow {
  transform: translateX(4px);
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
  .menu-inventario-container {
    padding: 1rem;
  }

  .menu-header h1 {
    font-size: 1.5rem;
  }

  .menu-opciones {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .opcion-card {
    padding: 1rem;
    gap: 1rem;
  }

  .opcion-icono {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }

  .opcion-contenido h2 {
    font-size: 1rem;
  }
}
</style>