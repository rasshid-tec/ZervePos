<template>
  <div class="registrar-cliente">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.back()">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/clientes" class="breadcrumb__link">Clientes</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Registrar Cliente</span>
          </span>
          <h1 class="page-title">Registrar Cliente</h1>
        </div>
      </div>
    </div>

    <div class="form-layout">
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
                  <input v-model="form.negocio" type="text" class="input" placeholder="Nombre del negocio" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">NOMBRE CONTACTO <span class="required">*</span></label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <input v-model="form.nombreCliente" type="text" class="input" placeholder="Nombre del contacto" />
                </div>
              </div>
            </div>

            <div class="field-row">
              <div class="field">
                <label class="field__label">TÍTULO CONTACTO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/></svg>
                  <input v-model="form.tituloContacto" type="text" class="input" placeholder="Ej. Sr., Lic., Ing." />
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
                  <input v-model="form.telefono" type="text" class="input" placeholder="10 dígitos" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">CORREO</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  <input v-model="form.correo" type="email" class="input" placeholder="correo@ejemplo.com" />
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
                <input v-model="form.direccion" type="text" class="input" placeholder="Calle y número" />
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label class="field__label">COLONIA</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                  <input v-model="form.colonia" type="text" class="input" placeholder="Colonia" />
                </div>
              </div>
              <div class="field">
                <label class="field__label">CIUDAD</label>
                <div class="input-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                  <input v-model="form.ciudad" type="text" class="input" placeholder="Ciudad" />
                </div>
              </div>
            </div>
            <div class="field field--half">
              <label class="field__label">CÓDIGO POSTAL</label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2"/></svg>
                <input v-model="form.codigoPostal" type="text" class="input" placeholder="00000" maxlength="5" />
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
                <input v-model.number="form.creditoPermitido" type="number" min="0" step="0.01" class="input input--prefix" placeholder="0.00" />
              </div>
              <span class="field__hint">Monto máximo de crédito para este cliente</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn--secondary" @click="limpiarFormulario">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
            Limpiar Formulario
          </button>
          <button class="btn btn--primary" @click="guardarCliente" :disabled="guardando">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ guardando ? 'Guardando...' : 'Guardar Cliente' }}
          </button>
        </div>

      </div>

      <!-- Resumen -->
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
            <div class="summary-row"><span class="summary-row__label">Categoría</span><span class="summary-row__value">{{ nombreCategoria || '—' }}</span></div>
            <div class="summary-row"><span class="summary-row__label">Crédito</span><span class="summary-row__value">{{ form.creditoPermitido ? '$' + form.creditoPermitido.toFixed(2) : '—' }}</span></div>
          </div>
        </div>
      </div>
    </div>

    <AlertaToast ref="alerta" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AlertaToast from '../../components/AlertaToast.vue'

const alerta   = ref(null)
const guardando = ref(false)
const niveles   = ref([])

const form = ref({
  negocio: '', nombreCliente: '', tituloContacto: '', direccion: '',
  ciudad: '', colonia: '', codigoPostal: '', telefono: '',
  correo: '', creditoPermitido: null, categoriaId: '',
})

const nombreCategoria = computed(() => {
  const n = niveles.value.find(n => n.NivelId === form.value.categoriaId)
  return n ? n.NombreNivel : ''
})

const cargarNiveles = async () => {
  try {
    const res  = await fetch('/php/obtener_niveles_cliente.php')
    const data = await res.json()
    if (data.status === 1) niveles.value = data.niveles
  } catch (e) {
    alerta.value.mostrar('Error al cargar categorías', 'error')
  }
}

const guardarCliente = async () => {
  if (!form.value.negocio || !form.value.nombreCliente || !form.value.telefono || !form.value.direccion || !form.value.categoriaId) {
    alerta.value.mostrar('Completa los campos obligatorios', 'advertencia')
    return
  }
  guardando.value = true
  try {
    const res  = await fetch('/php/insertar_cliente.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        negocio:          form.value.negocio,
        nombreCliente:    form.value.nombreCliente,
        tituloContacto:   form.value.tituloContacto,
        direccion:        form.value.direccion,
        ciudad:           form.value.ciudad,
        colonia:          form.value.colonia,
        codigoPostal:     form.value.codigoPostal,
        telefono:         form.value.telefono,
        correo:           form.value.correo || '',
        creditoPermitido: form.value.creditoPermitido || 0,
        categoriaId:      form.value.categoriaId,
      })
    })
    const data = await res.json()
    if      (data.status === 1)  { alerta.value.mostrar('Cliente registrado correctamente', 'exito'); limpiarFormulario() }
    else if (data.status === -2) { alerta.value.mostrar('Ya existe un cliente con ese teléfono', 'advertencia') }
    else if (data.status === -3) { alerta.value.mostrar('Ya existe un cliente con ese correo', 'advertencia') }
    else                         { alerta.value.mostrar('Error al guardar el cliente', 'error') }
  } catch (e) {
    alerta.value.mostrar('Error de conexión', 'error')
  } finally {
    guardando.value = false
  }
}

const limpiarFormulario = () => {
  form.value = {
    negocio: '', nombreCliente: '', tituloContacto: '', direccion: '',
    ciudad: '', colonia: '', codigoPostal: '', telefono: '',
    correo: '', creditoPermitido: null, categoriaId: '',
  }
}

onMounted(cargarNiveles)
</script>

<style scoped>
.registrar-cliente { padding: 1.5rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; align-items: center; margin-bottom: 1.5rem; }
.page-header__left { display: flex; align-items: center; gap: 1rem; }
.btn-back { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; color: #1a2b3c; transition: background 0.2s; }
.btn-back:hover { background: #f1f5f9; }
.breadcrumb { font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem; }
.breadcrumb__link { color: #f97316; text-decoration: none; }
.breadcrumb__link:hover { text-decoration: underline; }
.breadcrumb__sep { color: #cbd5e1; }
.page-title { font-size: 1.4rem; font-weight: 700; color: #1a2b3c; margin: 0; }
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
.field__hint { font-size: 0.75rem; color: #94a3b8; }
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
</style>