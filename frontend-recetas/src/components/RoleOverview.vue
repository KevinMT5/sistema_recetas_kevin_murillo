<template>
  <section class="stack-lg">
    <div class="clinic-hero">
      <div>
        <span class="eyebrow">{{ saludoRol }}</span>
        <h2>{{ titulo }}</h2>
        <p>{{ descripcion }}</p>
      </div>
      <button class="light-button" @click="$emit('navigate', 'agenda')">{{ accion }} →</button>
    </div>

    <div class="stats-grid clinic-stats">
      <article v-for="item in tarjetas" :key="item.label" class="stat-card">
        <span class="stat-icon" :class="item.color">{{ item.icono }}</span>
        <div><small>{{ item.label }}</small><strong>{{ cargando ? '—' : item.valor }}</strong></div>
      </article>
    </div>

    <div class="content-grid">
      <section class="panel-card agenda-preview">
        <div class="panel-heading">
          <div><span class="eyebrow text-primary">Próximamente</span><h3>{{ usuario.rol === 'paciente' ? 'Tus próximas citas' : 'Agenda prioritaria' }}</h3></div>
          <button class="text-button" @click="$emit('navigate', 'agenda')">Abrir agenda</button>
        </div>
        <div v-if="cargando" class="empty-state">Preparando tu panel...</div>
        <div v-else-if="!datos.proximas_citas?.length" class="empty-state"><span class="empty-icon">✓</span><strong>Agenda despejada</strong><p>No hay citas próximas pendientes.</p></div>
        <div v-else class="appointment-preview-list">
          <article v-for="cita in datos.proximas_citas" :key="cita.id" class="appointment-preview-row">
            <div class="date-tile"><strong>{{ dia(cita.fecha) }}</strong><span>{{ mes(cita.fecha) }}</span></div>
            <div class="preview-time"><strong>{{ hora(cita.hora_inicio) }}</strong><span>{{ cita.establecimiento?.nombre }}</span></div>
            <div class="preview-person"><strong>{{ persona(cita) }}</strong><span>{{ cita.motivo }}</span></div>
            <span class="status-badge" :class="`status-${cita.estado}`">{{ estado(cita.estado) }}</span>
          </article>
        </div>
      </section>

      <aside class="panel-card quick-panel">
        <div class="panel-heading"><div><span class="eyebrow text-primary">Flujo recomendado</span><h3>Acciones rápidas</h3></div></div>
        <button v-for="item in acciones" :key="item.id" class="quick-action" @click="$emit('navigate', item.id)">
          <span>{{ item.icono }}</span><div><strong>{{ item.nombre }}</strong><small>{{ item.descripcion }}</small></div><b>›</b>
        </button>
      </aside>
    </div>
  </section>
</template>

<script>
import api from '../axios';

export default {
  name: 'RoleOverview',
  emits: ['navigate'],
  props: { usuario: { type: Object, required: true } },
  data() { return { cargando: true, datos: { metricas: {}, proximas_citas: [] } }; },
  computed: {
    saludoRol() { return { administrador: 'Visión general de la clínica', recepcionista: 'Centro de coordinación', medico: 'Jornada médica', paciente: 'Tu espacio de salud' }[this.usuario.rol]; },
    titulo() { return { administrador: 'Una clínica ordenada empieza con información clara.', recepcionista: 'Mantén cada cita y cada paciente en movimiento.', medico: 'Tu agenda clínica, lista para atender.', paciente: `Hola, ${this.usuario.name.split(' ')[0]}. Cuidar tu salud es más sencillo.` }[this.usuario.rol]; },
    descripcion() { return { administrador: 'Supervisa la operación, el equipo y la demanda desde un solo lugar.', recepcionista: 'Confirma llegadas, evita cruces de horario y acompaña al paciente.', medico: 'Consulta tus pacientes de hoy y documenta cada atención con seguridad.', paciente: 'Reserva, consulta o cambia tus citas y encuentra tus indicaciones médicas.' }[this.usuario.rol]; },
    accion() { return this.usuario.rol === 'paciente' ? 'Reservar una cita' : 'Ver agenda'; },
    tarjetas() {
      const m = this.datos.metricas || {};
      if (this.usuario.rol === 'paciente') return [
        { label: 'Próximas citas', valor: this.datos.proximas_citas?.length || 0, icono: 'C', color: 'blue' },
        { label: 'Confirmadas', valor: m.confirmadas || 0, icono: '✓', color: 'green' },
        { label: 'Recetas disponibles', valor: m.recetas || 0, icono: 'Rx', color: 'violet' },
        { label: 'Centro de ayuda', valor: '24/7', icono: '?', color: 'orange' }
      ];
      return [
        { label: 'Citas de hoy', valor: m.citas_hoy || 0, icono: 'C', color: 'blue' },
        { label: 'Confirmadas', valor: m.confirmadas || 0, icono: '✓', color: 'green' },
        { label: 'En sala de espera', valor: m.en_espera || 0, icono: 'E', color: 'orange' },
        { label: this.usuario.rol === 'administrador' ? 'Pacientes activos' : 'Recetas emitidas', valor: this.usuario.rol === 'administrador' ? (m.pacientes || 0) : (m.recetas || 0), icono: this.usuario.rol === 'administrador' ? 'P' : 'Rx', color: 'violet' }
      ];
    },
    acciones() {
      if (this.usuario.rol === 'paciente') return [{ id: 'agenda', nombre: 'Reservar cita', descripcion: 'Encuentra un horario disponible', icono: '+' }, { id: 'recetas', nombre: 'Mis recetas', descripcion: 'Consulta tus indicaciones', icono: 'Rx' }];
      if (this.usuario.rol === 'recepcionista') return [{ id: 'agenda', nombre: 'Nueva cita', descripcion: 'Agenda en menos de un minuto', icono: '+' }, { id: 'pacientes', nombre: 'Buscar paciente', descripcion: 'Consulta o completa su perfil', icono: 'P' }];
      if (this.usuario.rol === 'medico') return [{ id: 'consulta', nombre: 'Iniciar atención', descripcion: 'Abre la siguiente cita', icono: '→' }, { id: 'recetas', nombre: 'Nueva receta', descripcion: 'Prescribe desde la consulta', icono: 'Rx' }];
      return [{ id: 'agenda', nombre: 'Supervisar agenda', descripcion: 'Revisa la operación del día', icono: 'C' }, { id: 'usuarios', nombre: 'Equipo y accesos', descripcion: 'Administra roles y permisos', icono: 'U' }, { id: 'especialidades', nombre: 'Configurar clínica', descripcion: 'Servicios y duración de citas', icono: '⚙' }];
    }
  },
  async mounted() { try { this.datos = (await api.get('dashboard')).data; } finally { this.cargando = false; } },
  methods: {
    dia(fecha) { return new Date(`${fecha}T12:00:00`).toLocaleDateString('es-SV', { day: '2-digit' }); },
    mes(fecha) { return new Date(`${fecha}T12:00:00`).toLocaleDateString('es-SV', { month: 'short' }).replace('.', ''); },
    hora(valor) { return String(valor || '').slice(0, 5); },
    persona(cita) { return this.usuario.rol === 'paciente' ? cita.medico?.nombre : cita.paciente?.nombre; },
    estado(valor) { return { pendiente: 'Pendiente', confirmada: 'Confirmada', en_espera: 'En espera', en_consulta: 'En consulta', finalizada: 'Finalizada', cancelada: 'Cancelada', no_asistio: 'No asistió' }[valor] || valor; }
  }
};
</script>
