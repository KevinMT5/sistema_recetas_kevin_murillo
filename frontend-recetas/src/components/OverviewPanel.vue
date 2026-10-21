<template>
  <section class="stack-lg">
    <div class="welcome-banner">
      <div><span class="eyebrow">Panel clínico</span><h2>Todo lo importante, en un solo lugar.</h2><p>Consulta la actividad reciente y mantén los catálogos listos para emitir nuevas recetas.</p></div>
      <button class="light-button" @click="$emit('navigate', 'recetas')">Gestionar recetas →</button>
    </div>
    <div class="stats-grid">
      <article v-for="item in tarjetas" :key="item.label" class="stat-card"><span class="stat-icon" :class="item.color">{{ item.icono }}</span><div><small>{{ item.label }}</small><strong>{{ item.valor }}</strong></div></article>
    </div>
    <div class="content-grid">
      <section class="panel-card recent-panel">
        <div class="panel-heading"><div><span class="eyebrow text-primary">Actividad reciente</span><h3>Últimas recetas</h3></div><button class="text-button" @click="$emit('navigate', 'recetas')">Ver todas</button></div>
        <div v-if="cargando" class="empty-state">Cargando información...</div>
        <div v-else-if="!recetas.length" class="empty-state">Aún no hay recetas registradas.</div>
        <div v-else class="recent-list">
          <article v-for="receta in recetas.slice(0, 5)" :key="receta.id" class="recent-row"><span class="document-icon">Rx</span><div class="recent-main"><strong>{{ receta.numero_receta }}</strong><span>{{ receta.paciente?.nombre }}</span></div><div class="recent-doctor"><span>{{ receta.medico?.nombre }}</span><small>{{ formatearFecha(receta.fecha) }}</small></div><span class="count-badge">{{ receta.total_medicamentos }} med.</span></article>
        </div>
      </section>
      <aside class="panel-card quick-panel">
        <div class="panel-heading"><div><span class="eyebrow text-primary">Accesos rápidos</span><h3>Administrar</h3></div></div>
        <button v-for="accion in acciones" :key="accion.id" class="quick-action" @click="$emit('navigate', accion.id)"><span>{{ accion.icono }}</span><div><strong>{{ accion.nombre }}</strong><small>{{ accion.descripcion }}</small></div><b>›</b></button>
      </aside>
    </div>
  </section>
</template>

<script>
import api from '../axios';
export default {
  name: 'OverviewPanel', emits: ['navigate'],
  data() { return { cargando: true, conteos: { recetas: 0, pacientes: 0, medicos: 0, medicamentos: 0 }, recetas: [], acciones: [{ id: 'pacientes', nombre: 'Pacientes', descripcion: 'Actualizar expedientes', icono: 'P' }, { id: 'medicos', nombre: 'Médicos', descripcion: 'Gestionar directorio', icono: 'M' }, { id: 'medicamentos', nombre: 'Medicamentos', descripcion: 'Revisar catálogo', icono: '+' }] }; },
  computed: { tarjetas() { return [{ label: 'Recetas emitidas', valor: this.conteos.recetas, icono: 'Rx', color: 'blue' }, { label: 'Pacientes', valor: this.conteos.pacientes, icono: 'P', color: 'green' }, { label: 'Médicos', valor: this.conteos.medicos, icono: 'M', color: 'violet' }, { label: 'Medicamentos', valor: this.conteos.medicamentos, icono: '+', color: 'orange' }]; } },
  async mounted() {
    try { const [r, p, m, md] = await Promise.all([api.get('recetas'), api.get('pacientes'), api.get('medicos'), api.get('medicamentos')]); this.recetas = r.data; this.conteos = { recetas: r.data.length, pacientes: p.data.length, medicos: m.data.length, medicamentos: md.data.length }; }
    finally { this.cargando = false; }
  },
  methods: { formatearFecha(fecha) { return fecha ? new Intl.DateTimeFormat('es-SV', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }).format(new Date(fecha)) : '—'; } }
};
</script>
