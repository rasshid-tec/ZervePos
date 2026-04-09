export function normalizeRole(rol) {
  return (rol || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
}

export const ROLE_LABELS = {
  cajero: 'Cajero',
  administrador: 'Administrador',
  dueno: 'Dueño',
}