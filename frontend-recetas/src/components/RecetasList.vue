<template>
  <section class="panel-card table-card">
    <div class="panel-heading recipes-heading"><div><span class="eyebrow text-primary">Historial</span><h3>Recetas registradas</h3><p>Consulta, filtra y administra las prescripciones emitidas.</p></div><span class="result-count">{{ recetas.length }} recetas</span></div>
    <div class="filter-grid">
      <label class="form-field"><span>Médico</span><select v-model="filtros.medico_id"><option value="">Todos los médicos</option><option v-for="item in medicos" :key="item.id" :value="item.id">{{ item.nombre }}</option></select></label>
      <label class="form-field"><span>Medicamento</span><select v-model="filtros.medicamento_id"><option value="">Todos los medicamentos</option><option v-for="item in medicamentos" :key="item.id" :value="item.id">{{ item.nombre }}</option></select></label>
      <label class="form-field"><span>Fecha</span><input v-model="filtros.fecha" type="date" /></label>
      <div class="filter-actions"><button class="secondary-button" @click="limpiarFiltros">Limpiar</button><button class="primary-button" @click="cargar">Aplicar filtros</button></div>
    </div>
    <div v-if="mensaje" class="form-alert" :class="mensaje.tipo === 'error' ? 'form-alert-error' : 'form-alert-success'">{{ mensaje.texto }}</div>
    <div v-if="cargando" class="empty-state">Cargando recetas...</div>
    <div v-else-if="!recetas.length" class="empty-state"><span class="empty-icon">Rx</span><strong>No hay recetas para mostrar</strong><p>Crea una nueva receta o cambia los filtros.</p></div>
    <div v-else class="prescription-cards">
      <article v-for="receta in recetas" :key="receta.id" class="prescription-card">
        <div class="prescription-card-head"><div><span class="prescription-number">{{ receta.numero_receta }}</span><strong>{{ receta.paciente?.nombre || 'Sin paciente' }}</strong><small>{{ formatearFecha(receta.fecha) }}</small></div><span class="count-badge">{{ receta.total_medicamentos ?? receta.detalles?.length }} medicamentos</span></div>
        <div class="prescription-meta"><span><b>Médico</b>{{ receta.medico?.nombre || '—' }}</span><span><b>Establecimiento</b>{{ receta.medico?.establecimiento?.nombre || '—' }}</span></div>
        <div class="medicine-summary"><div v-for="detalle in receta.detalles" :key="detalle.id"><strong>{{ detalle.medicamento?.nombre }}</strong><span>{{ detalle.cantidad }} · {{ detalle.dosis }}</span></div></div>
        <div class="card-actions"><button class="text-button" @click="$emit('edit', receta)">Editar receta</button><button class="text-button danger-text" @click="eliminar(receta)">Eliminar</button></div>
      </article>
    </div>
  </section>
</template>

<script>
import api from '../axios';
export default {
  name: 'RecetasList', emits: ['edit', 'changed'], props: { refreshKey: { type: Number, default: 0 } },
  data() { return { recetas: [], medicos: [], medicamentos: [], filtros: { medico_id: '', medicamento_id: '', fecha: '' }, cargando: true, mensaje: null }; },
  watch: { refreshKey() { this.cargar(); } },
  async mounted() { const [m, md] = await Promise.all([api.get('medicos'), api.get('medicamentos')]); this.medicos = m.data; this.medicamentos = md.data; await this.cargar(); },
  methods: {
    async cargar() { this.cargando = true; try { this.recetas = (await api.get('recetas', { params: this.filtros })).data; } catch (_) { this.mensaje = { tipo: 'error', texto: 'No fue posible cargar las recetas.' }; } finally { this.cargando = false; } },
    limpiarFiltros() { this.filtros = { medico_id: '', medicamento_id: '', fecha: '' }; this.cargar(); },
    async eliminar(receta) { if (!window.confirm(`¿Eliminar la receta ${receta.numero_receta}?`)) return; try { await api.delete(`recetas/${receta.id}`); this.mensaje = { tipo: 'success', texto: 'Receta eliminada correctamente.' }; await this.cargar(); this.$emit('changed'); } catch (error) { this.mensaje = { tipo: 'error', texto: error.response?.data?.message || 'No fue posible eliminar la receta.' }; } },
    formatearFecha(fecha) { return new Intl.DateTimeFormat('es-SV', { day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(fecha)); }
  }
};
</script>
