<template>
  <div class="sucursales-container">
    <div class="sucursales-header">
      <router-link to="/app/inventario" class="btn-volver">← Volver</router-link>
      <h1>Gestión de Sucursales</h1>
      <p>Ve y gestiona la información de todas las sucursales</p>
    </div>

    <div class="sucursales-content">
      <!-- BUSCADOR -->
      <div class="buscador-section">
        <div class="input-busqueda-wrapper">
          <input
            v-model="textoBusqueda"
            type="text"
            placeholder="Buscar sucursal..."
            class="input-busqueda"
          />
          <span class="icono">🔍</span>
        </div>
      </div>

      <!-- TABLA DE SUCURSALES -->
      <div class="tabla-wrapper">
        <table class="tabla-sucursales" v-if="!cargando && sucursalesFiltradas.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>Ciudad</th>
              <th>Teléfono</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sucursal in sucursalesFiltradas" :key="sucursal.SucursalId">
              <td class="id">{{ sucursal.SucursalId }}</td>
              <td class="nombre">{{ sucursal.NombreSucursal }}</td>
              <td class="direccion">{{ sucursal.Direccion || 'N/A' }}</td>
              <td class="ciudad">{{ sucursal.Ciudad || 'N/A' }}</td>
              <td class="telefono">{{ sucursal.Telefono || 'N/A' }}</td>
              <td class="estado">
                <span :class="['badge-estado', sucursal.Estado ? 'activa' : 'inactiva']">
                  {{ sucursal.Estado ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- ESTADO DE CARGA -->
        <div v-if="cargando" class="estado-carga">
          <p>Cargando sucursales...</p>
        </div>

        <!-- SIN RESULTADOS -->
        <div v-if="!cargando && sucursalesFiltradas.length === 0" class="sin-resultados">
          <p>No hay sucursales que coincidan con tu búsqueda</p>
        </div>
      </div>

      <!-- ESTADÍSTICAS -->
      <div v-if="!cargando && sucursales.length > 0" class="estadisticas-section">
        <div class="estadistica-card">
          <span class="label">Total Sucursales</span>
          <span class="valor">{{ sucursales.length }}</span>
        </div>
        <div class="estadistica-card activas">
          <span class="label">Activas</span>
          <span class="valor">{{ sucursalesActivas }}</span>
        </div>
        <div class="estadistica-card inactivas">
          <span class="label">Inactivas</span>
          <span class="valor">{{ sucursalesInactivas }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';

const toast = useToast();

// ==================== ESTADO ====================
const sucursales = ref([]);
const textoBusqueda = ref('');
const cargando = ref(false);

// ==================== COMPUTED ====================

/**
 * Filtra las sucursales por búsqueda
 */
const sucursalesFiltradas = computed(() => {
  if (!textoBusqueda.value.trim()) {
    return sucursales.value;
  }

  const busqueda = textoBusqueda.value.toLowerCase();
  return sucursales.value.filter(s =>
    s.NombreSucursal.toLowerCase().includes(busqueda) ||
    s.Direccion?.toLowerCase().includes(busqueda) ||
    s.Ciudad?.toLowerCase().includes(busqueda) ||
    s.Telefono?.includes(busqueda)
  );
});

/**
 * Cuenta sucursales activas
 */
const sucursalesActivas = computed(() => {
  return sucursales.value.filter(s => s.Estado).length;
});

/**
 * Cuenta sucursales inactivas
 */
const sucursalesInactivas = computed(() => {
  return sucursales.value.filter(s => !s.Estado).length;
});

// ==================== MÉTODOS ====================

/**
 * Obtiene todas las sucursales
 */
const obtenerSucursales = async () => {
  cargando.value = true;
  try {
    const response = await fetch('/php/obtener_sucursales.php');

    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`);
    }

    const result = await response.json();

    if (result.status === 'error') {
      throw new Error(result.message || 'Error desconocido');
    }

    // Filtrar la sucursal con ID 0 (Todo)
    sucursales.value = (result.data || []).filter(s => s.SucursalId !== 0);
    toast.success('Sucursales cargadas correctamente');
  } catch (error) {
    console.error('Error al obtener sucursales:', error);
    toast.error(`Error al cargar sucursales: ${error.message}`);
  } finally {
    cargando.value = false;
  }
};

// ==================== CICLO DE VIDA ====================

onMounted(() => {
  obtenerSucursales();
});
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
.sucursales-container {
  padding: 2rem;
  background: var(--color-bg);
  min-height: 100vh;
  color: var(--color-text);
}

/* ==================== HEADER ==================== */
.sucursales-header {
  margin-bottom: 2rem;
}

.btn-volver {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #f0f0f0;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  text-decoration: none;
  color: var(--color-text);
  font-size: 0.875rem;
  margin-bottom: 1rem;
  transition: all 0.2s ease;
}

.btn-volver:hover {
  background: var(--color-border);
}

.sucursales-header h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  color: var(--color-text);
}

.sucursales-header p {
  margin: 0;
  color: var(--color-text-secondary);
}

/* ==================== BUSCADOR ==================== */
.buscador-section {
  margin-bottom: 2rem;
}

.input-busqueda-wrapper {
  position: relative;
  max-width: 400px;
}

.input-busqueda {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.input-busqueda:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-busqueda-wrapper .icono {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-secondary);
  pointer-events: none;
}

/* ==================== TABLA ==================== */
.tabla-wrapper {
  background: var(--color-bg-card);
  border-radius: 8px;
  border: 1px solid var(--color-border);
  overflow: hidden;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.tabla-sucursales {
  width: 100%;
  border-collapse: collapse;
}

.tabla-sucursales thead {
  background: #f5f5f5;
  border-bottom: 2px solid var(--color-border);
}

.tabla-sucursales th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tabla-sucursales td {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
  font-size: 0.875rem;
}

.tabla-sucursales tbody tr {
  transition: all 0.2s ease;
}

.tabla-sucursales tbody tr:hover {
  background: #f5f5f5;
}

/* Columnas específicas */
.id {
  color: var(--color-primary);
  font-weight: 600;
  width: 60px;
}

.nombre {
  color: var(--color-text);
  font-weight: 500;
}

.direccion,
.ciudad,
.telefono {
  color: var(--color-text-secondary);
}

.estado {
  text-align: center;
}

/* BADGES DE ESTADO */
.badge-estado {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-estado.activa {
  background: #d1fae5;
  color: #065f46;
}

.badge-estado.inactiva {
  background: #fee2e2;
  color: #991b1b;
}

/* ==================== ESTADOS ==================== */
.estado-carga,
.sin-resultados {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}

.estado-carga {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.estado-carga p::after {
  content: '';
  display: inline-block;
  margin-left: 0.5rem;
  width: 1rem;
  height: 1rem;
  border: 2px solid var(--color-text-secondary);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ==================== ESTADÍSTICAS ==================== */
.estadisticas-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.estadistica-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.estadistica-card.activas {
  border-left: 4px solid var(--color-success);
}

.estadistica-card.inactivas {
  border-left: 4px solid var(--color-danger);
}

.estadistica-card .label {
  display: block;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.estadistica-card .valor {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text);
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
  .sucursales-container {
    padding: 1rem;
  }

  .sucursales-header h1 {
    font-size: 1.5rem;
  }

  .tabla-sucursales th,
  .tabla-sucursales td {
    padding: 0.75rem 0.5rem;
    font-size: 0.75rem;
  }

  .estadisticas-section {
    grid-template-columns: 1fr;
  }
}
</style>
