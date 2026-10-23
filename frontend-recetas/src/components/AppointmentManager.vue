<template>
  <section class="stack-lg appointment-module">
    <div class="section-intro">
      <div><span class="eyebrow text-primary">Coordinación clínica</span><h2>{{ titulo }}</h2><p>{{ descripcion }}</p></div>
      <button v-if="puedeCrear" class="primary-button" @click="abrirFormulario">+ {{ usuario.rol === 'paciente' ? 'Reservar cita' : 'Nueva cita' }}</button>
    </div>

    <form v-if="mostrarFormulario" class="panel-card appointment-form" @submit.prevent="guardar">
      <div class="panel-heading"><div><span class="eyebrow text-primary">{{ pasoTexto }}</span><h3>{{ usuario.rol === 'paciente' ? 'Reserva tu cita' : 'Datos de la cita' }}</h3></div><button type="button" class="close-button" aria-label="Cerrar" @click="cerrarFormulario">×</button></div>
      <div v-if="error" class="form-alert form-alert-error">{{ error }}</div>
      <div class="form-grid">
        <label v-if="usuario.rol !== 'paciente'" class="form-field wide"><span>Paciente <b>*</b></span><select v-model="form.paciente_id" required><option value="">Selecciona un paciente</option><option v-for="p in pacientes" :key="p.id" :value="p.id">{{ p.nombre }} · {{ p.telefono || 'sin teléfono' }}</option></select></label>
        <label class="form-field"><span>Especialista <b>*</b></span><select v-model="form.medico_id" required @change="alCambiarMedico"><option value="">Selecciona un médico</option><option v-for="m in medicos" :key="m.id" :value="m.id">{{ m.nombre }} · {{ m.especialidad_catalogo?.nombre || m.especialidad }}</option></select></label>
        <label class="form-field"><span>Sede <b>*</b></span><select v-model="form.establecimiento_id" required @change="cargarDisponibilidad"><option value="">Selecciona una sede</option><option v-for="e in establecimientos" :key="e.id" :value="e.id">{{ e.nombre }}</option></select></label>
        <label class="form-field"><span>Fecha <b>*</b></span><input v-model="form.fecha" type="date" :min="hoy" required @change="cargarDisponibilidad" /></label>
        <label class="form-field"><span>Hora disponible <b>*</b></span><select v-model="form.hora_inicio" required :disabled="cargandoHorarios"><option value="">{{ cargandoHorarios ? 'Consultando agenda...' : 'Selecciona una hora' }}</option><option v-for="hora in horarios" :key="hora" :value="hora">{{ hora }}</option></select><small v-if="form.fecha && form.medico_id && !cargandoHorarios && !horarios.length" class="field-help">El médico no tiene espacios disponibles ese día.</small></label>
        <label class="form-field wide"><span>Motivo de la consulta <b>*</b></span><input v-model.trim="form.motivo" maxlength="255" placeholder="Ej. Consulta de control, dolor de garganta..." required /></label>
        <label v-if="usuario.rol !== 'paciente'" class="form-field wide"><span>Nota interna</span><input v-model.trim="form.notas" placeholder="Información útil para recepción o el médico" /></label>
      </div>
      <div class="booking-summary"><span class="summary-mark">i</span><p><strong>Confirmación clara</strong><br>{{ resumenReserva }}</p></div>
      <div class="form-actions"><button type="button" class="secondary-button" @click="cerrarFormulario">Cancelar</button><button class="primary-button" :disabled="guardando || !form.hora_inicio">{{ guardando ? 'Guardando...' : (usuario.rol === 'paciente' ? 'Confirmar solicitud' : 'Confirmar cita') }}</button></div>
    </form>

    <section class="panel-card table-card agenda-card">
      <div class="agenda-toolbar">
        <div class="date-navigation"><button class="icon-button" @click="moverDia(-1)">‹</button><button class="secondary-button" @click="irHoy">Hoy</button><button class="icon-button" @click="moverDia(1)">›</button><div><strong>{{ fechaLarga(fechaFiltro) }}</strong><small>{{ citasFiltradas.length }} citas visibles</small></div></div>
        <div class="agenda-filters"><select v-if="usuario.rol !== 'medico' && usuario.rol !== 'paciente'" v-model="medicoFiltro"><option value="">Todos los médicos</option><option v-for="m in medicos" :key="m.id" :value="m.id">{{ m.nombre }}</option></select><select v-model="estadoFiltro"><option value="">Todos los estados</option><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select></div>
      </div>

      <div v-if="cargando" class="empty-state">Cargando la agenda...</div>
      <div v-else-if="!citasFiltradas.length" class="empty-state"><span class="empty-icon">C</span><strong>No hay citas en esta fecha</strong><p>{{ puedeCrear ? 'Puedes crear una nueva cita sin salir de esta pantalla.' : 'Tu agenda está despejada.' }}</p></div>
      <div v-else class="schedule-list">
        <article v-for="cita in citasFiltradas" :key="cita.id" class="schedule-row" :class="`schedule-${cita.estado}`">
          <div class="schedule-time"><strong>{{ hora(cita.hora_inicio) }}</strong><span>{{ hora(cita.hora_fin) }}</span></div>
          <div class="schedule-accent"></div>
          <div class="schedule-person"><div class="patient-avatar">{{ iniciales(cita.paciente?.nombre) }}</div><div><strong>{{ usuario.rol === 'paciente' ? cita.medico?.nombre : cita.paciente?.nombre }}</strong><span>{{ cita.motivo }}</span></div></div>
          <div class="schedule-meta"><strong>{{ cita.medico?.especialidad_catalogo?.nombre || cita.medico?.especialidad }}</strong><span>{{ cita.establecimiento?.nombre }}<template v-if="cita.consultorio"> · {{ cita.consultorio.nombre }}</template></span></div>
          <span class="status-badge" :class="`status-${cita.estado}`">{{ estadoLabel(cita.estado) }}</span>
          <div class="schedule-actions">
            <select v-if="puedeCambiarEstado(cita)" :value="cita.estado" aria-label="Cambiar estado" @change="cambiarEstado(cita, $event.target.value)"><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select>
            <button v-if="usuario.rol === 'paciente' && !['cancelada','finalizada'].includes(cita.estado)" class="text-button danger-text" @click="cambiarEstado(cita, 'cancelada')">Cancelar</button>
          </div>
        </article>
      </div>
    </section>
  </section>
</template>

<script>
import api from '../axios';

const vacio = () => ({ paciente_id: '', medico_id: '', establecimiento_id: '', fecha: new Date().toISOString().slice(0, 10), hora_inicio: '', motivo: '', notas: '', canal: 'recepcion' });

export default {
  name: 'AppointmentManager',
  props: { usuario: { type: Object, required: true } },
  data() { return { citas: [], medicos: [], pacientes: [], establecimientos: [], horarios: [], form: vacio(), mostrarFormulario: false, cargando: true, guardando: false, cargandoHorarios: false, error: '', fechaFiltro: new Date().toISOString().slice(0, 10), medicoFiltro: '', estadoFiltro: '', estados: [{ value: 'pendiente', label: 'Pendiente' }, { value: 'confirmada', label: 'Confirmada' }, { value: 'en_espera', label: 'En espera' }, { value: 'en_consulta', label: 'En consulta' }, { value: 'finalizada', label: 'Finalizada' }, { value: 'cancelada', label: 'Cancelada' }, { value: 'no_asistio', label: 'No asistió' }] }; },
  computed: {
    hoy() { return new Date().toISOString().slice(0, 10); },
    puedeCrear() { return ['administrador', 'recepcionista', 'paciente'].includes(this.usuario.rol); },
    titulo() { return this.usuario.rol === 'paciente' ? 'Mis citas' : this.usuario.rol === 'medico' ? 'Mi agenda' : 'Agenda clínica'; },
    descripcion() { return this.usuario.rol === 'paciente' ? 'Consulta tus próximas atenciones o encuentra un horario que se adapte a ti.' : 'Coordina pacientes, médicos y estados de atención desde una vista operativa.'; },
    pasoTexto() { return this.usuario.rol === 'paciente' ? 'Agenda en pocos pasos' : 'Registro asistido'; },
    citasFiltradas() { return this.citas.filter((c) => c.fecha === this.fechaFiltro && (!this.medicoFiltro || String(c.medico_id) === String(this.medicoFiltro)) && (!this.estadoFiltro || c.estado === this.estadoFiltro)); },
    resumenReserva() { const medico = this.medicos.find((m) => String(m.id) === String(this.form.medico_id)); const sede = this.establecimientos.find((e) => String(e.id) === String(this.form.establecimiento_id)); return medico && this.form.fecha && this.form.hora_inicio ? `${medico.nombre}, ${this.fechaLarga(this.form.fecha)} a las ${this.form.hora_inicio}${sede ? ` en ${sede.nombre}` : ''}.` : 'Selecciona médico, sede, fecha y hora para ver el resumen antes de confirmar.'; }
  },
  async mounted() { await this.cargarTodo(); },
  methods: {
    async cargarTodo() { this.cargando = true; try { const peticiones = [api.get('citas'), api.get('medicos'), api.get('establecimientos')]; if (this.usuario.rol !== 'paciente') peticiones.push(api.get('pacientes')); const respuestas = await Promise.all(peticiones); [this.citas, this.medicos, this.establecimientos] = respuestas.slice(0, 3).map((r) => r.data); this.pacientes = respuestas[3]?.data || []; } finally { this.cargando = false; } },
    abrirFormulario() { this.form = vacio(); if (this.usuario.rol === 'paciente') this.form.canal = 'portal_paciente'; this.mostrarFormulario = true; this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' })); },
    cerrarFormulario() { this.mostrarFormulario = false; this.error = ''; },
    alCambiarMedico() { const medico = this.medicos.find((m) => String(m.id) === String(this.form.medico_id)); if (medico) this.form.establecimiento_id = medico.establecimiento_id; this.form.hora_inicio = ''; this.cargarDisponibilidad(); },
    async cargarDisponibilidad() { if (!this.form.medico_id || !this.form.fecha) return; this.cargandoHorarios = true; this.form.hora_inicio = ''; try { this.horarios = (await api.get('disponibilidad', { params: { medico_id: this.form.medico_id, fecha: this.form.fecha, establecimiento_id: this.form.establecimiento_id || undefined } })).data; } finally { this.cargandoHorarios = false; } },
    async guardar() { this.guardando = true; this.error = ''; try { await api.post('citas', this.form); this.fechaFiltro = this.form.fecha; await this.cargarTodo(); this.cerrarFormulario(); } catch (e) { this.error = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'No pudimos guardar la cita.'; } finally { this.guardando = false; } },
    async cambiarEstado(cita, estado) { if (!window.confirm(`¿Cambiar la cita ${cita.codigo} a “${this.estadoLabel(estado)}”?`)) return; try { await api.patch(`citas/${cita.id}`, { estado }); cita.estado = estado; } catch (e) { window.alert(e.response?.data?.message || 'No se pudo actualizar la cita.'); } },
    puedeCambiarEstado() { return ['administrador', 'recepcionista', 'medico'].includes(this.usuario.rol); },
    moverDia(dias) { const fecha = new Date(`${this.fechaFiltro}T12:00:00`); fecha.setDate(fecha.getDate() + dias); this.fechaFiltro = fecha.toISOString().slice(0, 10); },
    irHoy() { this.fechaFiltro = this.hoy; },
    fechaLarga(fecha) { return new Date(`${fecha}T12:00:00`).toLocaleDateString('es-SV', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); },
    hora(valor) { return String(valor || '').slice(0, 5); },
    iniciales(nombre = '') { return nombre.split(' ').slice(0, 2).map((p) => p[0]).join('').toUpperCase(); },
    estadoLabel(valor) { return this.estados.find((e) => e.value === valor)?.label || valor; }
  }
};
</script>
