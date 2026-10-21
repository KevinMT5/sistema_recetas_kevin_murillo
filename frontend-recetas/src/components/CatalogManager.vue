<template>
  <section class="stack-lg">
    <div class="section-intro">
      <div><span class="eyebrow text-primary">Catálogo</span><h2>{{ config.titulo }}</h2><p>{{ config.descripcion }}</p></div>
      <button class="primary-button" @click="abrirFormulario">+ Nuevo {{ config.singular }}</button>
    </div>

    <div v-if="mensaje" class="form-alert" :class="mensaje.tipo === 'error' ? 'form-alert-error' : 'form-alert-success'">{{ mensaje.texto }}</div>

    <form v-if="formularioVisible" class="panel-card catalog-form" @submit.prevent="guardar">
      <div class="panel-heading"><div><span class="eyebrow text-primary">{{ editandoId ? 'Editar registro' : 'Nuevo registro' }}</span><h3>{{ editandoId ? `Actualizar ${config.singular}` : `Agregar ${config.singular}` }}</h3></div><button type="button" class="close-button" @click="cerrarFormulario">×</button></div>
      <div class="form-grid">
        <label v-for="field in config.fields" :key="field.key" class="form-field" :class="{ wide: field.wide }">
          <span>{{ field.label }} <b v-if="field.required">*</b></span>
          <select v-if="field.type === 'select'" v-model="form[field.key]" :required="field.required">
            <option value="">Selecciona una opción</option>
            <option v-for="option in opciones[field.key] || []" :key="option.id" :value="option.id">{{ option.nombre }}</option>
          </select>
          <input v-else v-model="form[field.key]" :type="field.type || 'text'" :required="field.required" :placeholder="field.label" />
          <small v-if="errores[field.key]">{{ errores[field.key][0] }}</small>
        </label>
      </div>
      <div class="form-actions"><button type="button" class="secondary-button" @click="cerrarFormulario">Cancelar</button><button class="primary-button" :disabled="guardando">{{ guardando ? 'Guardando...' : 'Guardar cambios' }}</button></div>
    </form>

    <section class="panel-card table-card">
      <div class="table-toolbar"><div class="search-box"><span>⌕</span><input v-model.trim="busqueda" type="search" :placeholder="`Buscar ${config.titulo.toLowerCase()}...`" /></div><span class="result-count">{{ filtrados.length }} registros</span></div>
      <div v-if="cargando" class="empty-state">Cargando registros...</div>
      <div v-else-if="!filtrados.length" class="empty-state"><span class="empty-icon">◇</span><strong>No hay resultados</strong><p>Agrega un registro o cambia el término de búsqueda.</p></div>
      <div v-else class="table-responsive">
        <table class="data-table"><thead><tr><th v-for="column in config.columns" :key="column.key">{{ column.label }}</th><th class="actions-column">Acciones</th></tr></thead>
          <tbody><tr v-for="item in filtrados" :key="item.id"><td v-for="column in config.columns" :key="column.key" :data-label="column.label"><strong v-if="column === config.columns[0]">{{ valor(item, column) }}</strong><span v-else>{{ valor(item, column) }}</span></td><td class="row-actions"><button class="icon-button" title="Editar" @click="editar(item)">Editar</button><button class="icon-button danger" title="Eliminar" @click="eliminar(item)">Eliminar</button></td></tr></tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script>
import api from '../axios';
export default {
  name: 'CatalogManager', props: { config: { type: Object, required: true } },
  data() { return { items: [], opciones: {}, form: {}, errores: {}, busqueda: '', cargando: true, guardando: false, formularioVisible: false, editandoId: null, mensaje: null }; },
  computed: {
    filtrados() { const q = this.busqueda.toLowerCase(); return q ? this.items.filter((item) => JSON.stringify(item).toLowerCase().includes(q)) : this.items; }
  },
  async mounted() { await Promise.all([this.cargar(), this.cargarOpciones()]); },
  methods: {
    formVacio() { return Object.fromEntries(this.config.fields.map((f) => [f.key, ''])); },
    async cargar() { this.cargando = true; try { this.items = (await api.get(this.config.endpoint)).data; } finally { this.cargando = false; } },
    async cargarOpciones() { for (const field of this.config.fields.filter((f) => f.optionsEndpoint)) this.opciones[field.key] = (await api.get(field.optionsEndpoint)).data; },
    abrirFormulario() { this.form = this.formVacio(); this.editandoId = null; this.errores = {}; this.formularioVisible = true; this.mensaje = null; },
    cerrarFormulario() { this.formularioVisible = false; this.editandoId = null; this.errores = {}; },
    editar(item) { this.form = Object.fromEntries(this.config.fields.map((f) => [f.key, item[f.key] ?? ''])); this.editandoId = item.id; this.formularioVisible = true; this.errores = {}; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    async guardar() {
      this.guardando = true; this.errores = {};
      try { if (this.editandoId) await api.put(`${this.config.endpoint}/${this.editandoId}`, this.form); else await api.post(this.config.endpoint, this.form); await this.cargar(); this.cerrarFormulario(); this.mensaje = { tipo: 'success', texto: `El ${this.config.singular} se guardó correctamente.` }; }
      catch (error) { if (error.response?.status === 422) this.errores = error.response.data.errors || error.response.data; else this.mensaje = { tipo: 'error', texto: error.response?.data?.message || 'No fue posible guardar el registro.' }; }
      finally { this.guardando = false; }
    },
    async eliminar(item) { if (!window.confirm(`¿Eliminar ${item.nombre}?`)) return; try { await api.delete(`${this.config.endpoint}/${item.id}`); await this.cargar(); this.mensaje = { tipo: 'success', texto: 'Registro eliminado correctamente.' }; } catch (error) { this.mensaje = { tipo: 'error', texto: error.response?.data?.message || 'No fue posible eliminar el registro.' }; } },
    valor(item, column) { const raw = column.key.split('.').reduce((current, key) => current?.[key], item); if (!raw) return '—'; if (column.format === 'date') return new Intl.DateTimeFormat('es-SV', { timeZone: 'UTC' }).format(new Date(raw)); return raw; }
  }
};
</script>
