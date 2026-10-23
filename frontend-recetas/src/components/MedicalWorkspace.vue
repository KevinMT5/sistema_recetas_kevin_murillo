<template>
  <section class="stack-lg">
    <div class="section-intro"><div><span class="eyebrow text-primary">Atención médica</span><h2>Consulta clínica</h2><p>Documenta la atención y deja las indicaciones listas para el paciente.</p></div></div>
    <div class="medical-layout">
      <aside class="panel-card patient-queue">
        <div class="panel-heading"><div><span class="eyebrow text-primary">Lista de trabajo</span><h3>Pacientes por atender</h3></div><span class="result-count">{{ citasActivas.length }}</span></div>
        <div v-if="cargando" class="empty-state">Cargando agenda...</div>
        <div v-else-if="!citasActivas.length" class="empty-state"><span class="empty-icon">✓</span><strong>Sin citas pendientes</strong><p>La jornada está al día.</p></div>
        <button v-for="cita in citasActivas" :key="cita.id" class="queue-item" :class="{ active: seleccionada?.id === cita.id }" @click="seleccionar(cita)">
          <span class="queue-time">{{ hora(cita.hora_inicio) }}</span><span class="patient-avatar">{{ iniciales(cita.paciente?.nombre) }}</span><span><strong>{{ cita.paciente?.nombre }}</strong><small>{{ cita.motivo }}</small></span><span class="status-badge" :class="`status-${cita.estado}`">{{ estado(cita.estado) }}</span>
        </button>
      </aside>

      <form class="panel-card consultation-sheet" @submit.prevent="guardar">
        <div v-if="!seleccionada" class="empty-state consultation-empty"><span class="empty-icon">+</span><strong>Selecciona un paciente</strong><p>Abre una cita de la lista para iniciar la atención.</p></div>
        <template v-else>
          <div class="consultation-header"><div class="patient-avatar large">{{ iniciales(seleccionada.paciente?.nombre) }}</div><div><span class="eyebrow text-primary">{{ seleccionada.codigo }}</span><h3>{{ seleccionada.paciente?.nombre }}</h3><p>{{ seleccionada.motivo }} · {{ seleccionada.paciente?.telefono || 'Sin teléfono' }}</p></div><span class="status-badge" :class="`status-${seleccionada.estado}`">{{ estado(seleccionada.estado) }}</span></div>
          <div v-if="mensaje" class="form-alert" :class="mensaje.tipo === 'error' ? 'form-alert-error' : 'form-alert-success'">{{ mensaje.texto }}</div>
          <fieldset class="clinical-fieldset"><legend>Signos vitales</legend><div class="vitals-grid"><label class="form-field"><span>Presión arterial</span><input v-model="form.signos_vitales.presion" placeholder="120/80" /></label><label class="form-field"><span>Temperatura °C</span><input v-model="form.signos_vitales.temperatura" type="number" step="0.1" placeholder="36.5" /></label><label class="form-field"><span>Peso kg</span><input v-model="form.signos_vitales.peso" type="number" step="0.1" placeholder="70" /></label><label class="form-field"><span>Saturación %</span><input v-model="form.signos_vitales.saturacion" type="number" placeholder="98" /></label></div></fieldset>
          <label class="form-field"><span>Motivo y evolución</span><textarea v-model="form.motivo_consulta" rows="3" placeholder="Describe síntomas, evolución y hallazgos relevantes"></textarea></label>
          <label class="form-field"><span>Diagnóstico</span><textarea v-model="form.diagnostico" rows="3" placeholder="Diagnóstico clínico o impresión diagnóstica"></textarea></label>
          <label class="form-field"><span>Indicaciones y plan</span><textarea v-model="form.indicaciones" rows="3" placeholder="Tratamiento, exámenes, cuidados y próxima revisión"></textarea></label>
          <div class="privacy-note"><strong>Privacidad clínica</strong><span>Esta información forma parte del expediente del paciente y solo es visible para personal autorizado.</span></div>
          <div class="form-actions"><button type="button" class="secondary-button" @click="limpiar">Cerrar</button><button class="primary-button" :disabled="guardando">{{ guardando ? 'Guardando...' : (form.diagnostico ? 'Finalizar consulta' : 'Guardar borrador') }}</button></div>
        </template>
      </form>
    </div>
  </section>
</template>

<script>
import api from '../axios';
const formulario = () => ({ motivo_consulta: '', diagnostico: '', indicaciones: '', signos_vitales: { presion: '', temperatura: '', peso: '', saturacion: '' } });
export default {
  name: 'MedicalWorkspace',
  data() { return { citas: [], seleccionada: null, form: formulario(), cargando: true, guardando: false, mensaje: null }; },
  computed: { citasActivas() { return this.citas.filter((c) => !['cancelada', 'finalizada', 'no_asistio'].includes(c.estado)); } },
  async mounted() { try { this.citas = (await api.get('citas', { params: { desde: new Date().toISOString().slice(0, 10) } })).data; } finally { this.cargando = false; } },
  methods: {
    seleccionar(cita) { this.seleccionada = cita; this.form = cita.consulta ? { motivo_consulta: cita.consulta.motivo_consulta || '', diagnostico: cita.consulta.diagnostico || '', indicaciones: cita.consulta.indicaciones || '', signos_vitales: cita.consulta.signos_vitales || formulario().signos_vitales } : formulario(); this.mensaje = null; },
    limpiar() { this.seleccionada = null; this.form = formulario(); },
    async guardar() { this.guardando = true; this.mensaje = null; try { const response = await api.post(`citas/${this.seleccionada.id}/consulta`, this.form); this.seleccionada.consulta = response.data; this.seleccionada.estado = this.form.diagnostico ? 'finalizada' : 'en_consulta'; this.mensaje = { tipo: 'success', texto: this.form.diagnostico ? 'Consulta finalizada y guardada.' : 'Borrador clínico guardado.' }; } catch (e) { this.mensaje = { tipo: 'error', texto: e.response?.data?.message || 'No se pudo guardar la consulta.' }; } finally { this.guardando = false; } },
    hora(valor) { return String(valor || '').slice(0, 5); },
    iniciales(nombre = '') { return nombre.split(' ').slice(0, 2).map((p) => p[0]).join('').toUpperCase(); },
    estado(valor) { return { pendiente: 'Pendiente', confirmada: 'Confirmada', en_espera: 'En espera', en_consulta: 'En consulta' }[valor] || valor; }
  }
};
</script>
