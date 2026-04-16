<template>
  <div class="editar-cliente">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.back()">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/clientes" class="breadcrumb__link">Clientes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Editar Cliente</span>
          </span>
          <h1 class="page-title">Editar Cliente</h1>
        </div>
      </div>
    </div>

    <div class="card buscador-card">
      <div class="card__header">
        <div class="card__icon card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div>
          <h2 class="card__title">Buscar Cliente</h2>
          <p class="card__subtitle">Por nombre de contacto o negocio</p>
        </div>
      </div>
      <div class="card__body">
        <div class="buscador-wrap">
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="busqueda" type="text" class="input" placeholder="Buscar por nombre o negocio..." @input="onBusqueda" autocomplete="off" />
          </div>
          <Transition name="dropdown">
            <div v-if="sugerencias.length > 0" class="dropdown">
              <div v-for="c in sugerencias" :key="c.ClienteId" class="dropdown__item" @click="seleccionarCliente(c)">
                <div class="dropdown__item-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="dropdown__item-info">
                  <span class="dropdown__item-nombre">{{ c.NombreCliente }}</span>
                  <span class="dropdown__item-sub">{{ c.Negocio }} · {{ c.Telefono }}</span>
                </div>
                <span class="dropdown__item-cat">{{ c.NombreCategoria || '—' }}</span>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <div v-if="!clienteSeleccionado" class="empty-state">
      <div class="empty-state__icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <h3 class="empty-state__titulo">Ningún cliente seleccionado</h3>
      <p class="empty-state__desc">Usa el buscador para encontrar el cliente que deseas editar.</p>
    </div>

    <div v-else class="form-layout">
      <div class="form-col">

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
              <h2 class="card__title">Información del Cliente</h2>
              <p class="card__subtitle">Datos de contacto e identificación</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field-row">
              <div class="field">
                <label class="field__label">NEGOCIO <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                  <input v-model="form.negocio" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">NOMBRE CONTACTO <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <input v-model="form.nombreCliente" type="text" class="input" />
                </div>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">TÍTULO CONTACTO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                  <input v-model="form.tituloContacto" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">CATEGORÍA <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                  <select v-model="form.categoriaId" class="input input--select">
                    <option value="">Selecciona...</option>
                    <option v-for="n in niveles" :key="n.NivelId" :value="n.NivelId">{{ n.NombreNivel }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">TELÉFONO <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 15.1 19.79 19.79 0 0 1 1.61 6.53 2 2 0 0 1 3.6 4.34h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 12a16 16 0 0 0 6.06 6.06l.98-.98a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.92 19.4z"/></svg>
                  <input v-model="form.telefono" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">CORREO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  <input v-model="form.correo" type="email" class="input" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <h2 class="card__title">Dirección</h2>
              <p class="card__subtitle">Ubicación del cliente</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field">
              <label class="field__label">DIRECCIÓN <span class="required">*</span></label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/></svg>
                <input v-model="form.direccion" type="text" class="input" />
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">COLONIA</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                  <input v-model="form.colonia" type="text" class="input" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">CIUDAD</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                  <input v-model="form.ciudad" type="text" class="input" />
                </div>
              </div>
            </div>
            <div class="field field--half">
              <label class="field__label">CÓDIGO POSTAL</label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2"/></svg>
                <input v-model="form.codigoPostal" type="text" class="input" maxlength="5" />
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="card__icon card__icon--orange">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <h2 class="card__title">Crédito</h2>
              <p class="card__subtitle">Límite de crédito permitido</p>
            </div>
          </div>
          <div class="card__body">
            <div class="field field--half">
              <label class="field__label">CRÉDITO PERMITIDO</label>
              <div class="input-wrap">
                <span class="input-prefix">$</span>
                <input v-model.number="form.creditoPermitido" type="number" min="0" step="0.01" class="input input--prefix" />
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn--secondary" @click="cancelar">Cancelar</button>
          <button class="btn btn--primary" @click="guardarCambios" :disabled="guardando">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>

      </div>

      <div class="summary-col">
        <div class="card card--sticky">
          <div class="card__header">
            <div class="card__icon card__icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div><h2 class="card__title">Resumen del Cliente</h2></div>
          </div>
          <div class="card__body">
            <div class="summary-row"><span class="summary-row__label">Negocio</span><span class="summary-row__value">{{ form.negocio || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Contacto</span><span class="summary-row__value">{{ form.tituloContacto ? form.tituloContacto + ' ' : '' }}{{ form.nombreCliente || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Teléfono</span><span class="summary-row__value">{{ form.telefono || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Correo</span><span class="summary-row__value">{{ form.correo || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Ciudad</span><span class="summary-row__value">{{ form.ciudad || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Crédito</span><span class="summary-row__value">{{ form.creditoPermitido ? '$' + Number(form.creditoPermitido).toFixed(2) : '—' }}</span></div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmacionPeligrosa ref="confirmacion" />
    <AlertaToast ref="alerta" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AlertaToast from '../../components/AlertaToast.vue'
import ConfirmacionPeligrosa from '../../components/ConfirmacionPeligrosa.vue'

const alerta           = ref(null)
const confirmacion     = ref(null)
const busqueda         = ref('')
const sugerencias      = ref([])
const clienteSeleccionado = ref(null)
const niveles          = ref([])
const guardando        = ref(false)
let   busquedaTimer    = null

const form = ref({
  clienteId: null, negocio: '', nombreCliente: '', tituloContacto: '',
  direccion: '', ciudad: '', colonia: '', codigoPostal: '',
  telefono: '', correo: '', creditoPermitido: null, categoriaId: '',
})

const onBusqueda = () => {
  clearTimeout(busquedaTimer)
  if (!busqueda.value.trim()) { sugerencias.value = []; return }
  busquedaTimer = setTimeout(async () => {
    try {
      const res  = await fetch('/php/buscar_clientes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ busqueda: busqueda.value.trim() })
      })
      const data = await res.json()
      if (data.status === 1) sugerencias.value = data.clientes
    } catch (e) { sugerencias.value = [] }
  }, 300)
}

const seleccionarCliente = (c) => {
  sugerencias.value      = []
  busqueda.value         = c.NombreCliente
  clienteSeleccionado.value = c
  form.value = {
    clienteId:        c.ClienteId,
    negocio:          c.Negocio,
    nombreCliente:    c.NombreCliente,
    tituloContacto:   c.TituloContacto,
    direccion:        c.Direccion,
    ciudad:           c.Ciudad,
    colonia:          c.Colonia,
    codigoPostal:     c.CodigoPostal,
    telefono:         c.Telefono,
    correo:           c.Correo,
    creditoPermitido: c.CreditoPermitido,
    categoriaId:      c.CategoriaId,
  }
}

const guardarCambios = async () => {
  if (!form.value.negocio || !form.value.nombreCliente || !form.value.telefono || !form.value.direccion || !form.value.categoriaId) {
    alerta.value.mostrar('Completa los campos obligatorios', 'advertencia')
    return
  }
  const confirmado = await confirmacion.value.mostrar(
    '¿Modificar cliente?',
    'Estás a punto de editar información sensible del cliente. Esta acción no se puede deshacer.'
  )
  if (!confirmado) return

  guardando.value = true
  try {
    const res  = await fetch('/php/editar_cliente.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...form.value, correo: form.value.correo || '' })
    })
    const data = await res.json()
    if      (data.status === 1)  { alerta.value.mostrar('Cliente actualizado correctamente', 'exito'); cancelar() }
    else if (data.status === -2) { alerta.value.mostrar('El cliente no existe', 'advertencia') }
    else if (data.status === -3) { alerta.value.mostrar('Ya existe un cliente con ese teléfono', 'advertencia') }
    else if (data.status === -4) { alerta.value.mostrar('Ya existe un cliente con ese correo', 'advertencia') }
    else                         { alerta.value.mostrar('Error al actualizar el cliente', 'error') }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  } finally {
    guardando.value = false
  }
}

const cancelar = () => {
  clienteSeleccionado.value = null
  busqueda.value = ''
  sugerencias.value = []
  form.value = { clienteId: null, negocio: '', nombreCliente: '', tituloContacto: '', direccion: '', ciudad: '', colonia: '', codigoPostal: '', telefono: '', correo: '', creditoPermitido: null, categoriaId: '' }
}

const cargarNiveles = async () => {
  try {
    const res  = await fetch('/php/obtener_niveles_cliente.php')
    const data = await res.json()
    if (data.status === 1) niveles.value = data.niveles
  } catch (e) {}
}

onMounted(cargarNiveles)
</script>

<style scoped>
.editar-cliente { padding: 1.5rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; align-items: center; margin-bottom: 1.5rem; }
.page-header__left { display: flex; align-items: center; gap: 1rem; }
.btn-back { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; color: #1a2b3c; transition: background 0.2s; }
.btn-back:hover { background: #f1f5f9; }
.breadcrumb { font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem; }
.breadcrumb__link { color: #f97316; text-decoration: none; }
.breadcrumb__link:hover { text-decoration: underline; }
.breadcrumb__sep { color: #cbd5e1; }
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.buscador-card { overflow: visible; margin-bottom: 1.25rem; }
.buscador-wrap { position: relative; }
.dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; overflow: hidden; }
.dropdown__item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 1rem; cursor: pointer; transition: background 0.15s; }
.dropdown__item:hover { background: #f8fafc; }
.dropdown__item-icon { width: 36px; height: 36px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #2563eb; }
.dropdown__item-icon svg { width: 18px; height: 18px; }
.dropdown__item-info { flex: 1; display: flex; flex-direction: column; gap: 0.15rem; }
.dropdown__item-nombre { font-size: 0.9rem; font-weight: 600; color: #1a2b3c; }
.dropdown__item-sub { font-size: 0.78rem; color: #94a3b8; }
.dropdown__item-cat { font-size: 0.78rem; font-weight: 600; color: #2563eb; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; gap: 0.75rem; text-align: center; }
.empty-state__icono { width: 80px; height: 80px; background: #eff6ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #2563eb; }
.empty-state__icono svg { width: 40px; height: 40px; }
.empty-state__titulo { font-size: 1.1rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.empty-state__desc { font-size: 0.875rem; color: #94a3b8; margin: 0; max-width: 320px; line-height: 1.6; }
.form-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; align-items: start; }
.card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; overflow: hidden; }
.card--sticky { position: sticky; top: 1rem; }
.card__header { display: flex; align-items: center; gap: 0.875rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.card__icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card__icon svg { width: 18px; height: 18px; }
.card__icon--blue   { background: #1a2b3c; color: #fff; }
.card__icon--orange { background: #f97316; color: #fff; }
.card__title { font-size: 0.95rem; font-weight: 700; color: #1a2b3c; margin: 0; }
.card__subtitle { font-size: 0.78rem; color: #94a3b8; margin: 0; }
.card__body { padding: 1.25rem; }
.field { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
.field:last-child { margin-bottom: 0; }
.field--half { max-width: 50%; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field__label { font-size: 0.72rem; font-weight: 700; color: #64748b; letter-spacing: 0.04em; }
.required { color: #f97316; }
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 0.75rem; width: 15px; height: 15px; color: #94a3b8; pointer-events: none; }
.input-prefix { position: absolute; left: 0.75rem; font-size: 0.9rem; color: #64748b; pointer-events: none; }
.input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #1a2b3c; background: #f8fafc; outline: none; transition: border-color 0.2s, background 0.2s; box-sizing: border-box; }
.input:focus { border-color: #1a2b3c; background: #fff; }
.input--select { appearance: none; cursor: pointer; }
.input--prefix { padding-left: 1.5rem; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
.summary-row:last-child { border-bottom: none; }
.summary-row__label { color: #94a3b8; }
.summary-row__value { font-weight: 600; color: #1a2b3c; text-align: right; max-width: 60%; word-break: break-word; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem; }
.btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s, opacity 0.2s; }
.btn svg { width: 16px; height: 16px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--primary   { background: #f97316; color: #fff; }
.btn--primary:hover:not(:disabled) { background: #ea580c; }
.btn--secondary { background: #fff; color: #1a2b3c; border: 1px solid #e2e8f0; }
.btn--secondary:hover { background: #f1f5f9; }
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>