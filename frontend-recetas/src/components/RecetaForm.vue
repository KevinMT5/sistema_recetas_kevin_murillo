<template>
  <form class="panel-card prescription-form" @submit.prevent="guardar">
    <div class="panel-heading">
      <div><span class="eyebrow text-primary">{{ recetaEditar ? 'Editar receta' : 'Nueva prescripción' }}</span><h3>{{ recetaEditar ? recetaEditar.numero_receta : 'Crear receta médica' }}</h3><p>Completa los datos generales y agrega al menos un medicamento.</p></div>
      <button type="button" class="close-button" @click="$emit('cancel')">×</button>
    </div>

    <div v-if="mensaje" class="form-alert form-alert-error">{{ mensaje }}</div>
    <div class="form-grid prescription-main">
      <label class="form-field"><span>Fecha *</span><input v-model="receta.fecha" type="date" required /><small v-if="errorDe('fecha')">{{ errorDe('fecha') }}</small></label>
      <label class="form-field"><span>Número de receta *</span><input v-model.trim="receta.numero_receta" placeholder="Ej. REC-0002" required /><small v-if="errorDe('numero_receta')">{{ errorDe('numero_receta') }}</small></label>
      <label class="form-field"><span>Médico *</span><select v-model="receta.medico_id" required><option value="">Selecciona un médico</option><option v-for="medico in medicos" :key="medico.id" :value="medico.id">{{ medico.nombre }} · {{ medico.numero_jv }}</option></select><small v-if="errorDe('medico_id')">{{ errorDe('medico_id') }}</small></label>
      <label class="form-field"><span>Paciente *</span><select v-model="receta.paciente_id" required><option value="">Selecciona un paciente</option><option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">{{ paciente.nombre }}</option></select><small v-if="errorDe('paciente_id')">{{ errorDe('paciente_id') }}</small></label>
    </div>

    <div class="details-heading"><div><span class="eyebrow text-primary">Tratamiento</span><h4>Medicamentos indicados</h4></div><button type="button" class="secondary-button" @click="agregarDetalle">+ Agregar medicamento</button></div>
    <div class="medicine-list">
      <article v-for="(detalle, index) in receta.detalles" :key="index" class="medicine-row">
        <span class="medicine-number">{{ index + 1 }}</span>
        <label class="form-field"><span>Medicamento *</span><select v-model="detalle.medicamento_id" required><option value="">Seleccionar</option><option v-for="medicamento in medicamentos" :key="medicamento.id" :value="medicamento.id">{{ medicamento.nombre }} {{ medicamento.concentracion || '' }}</option></select></label>
        <label class="form-field compact"><span>Cantidad *</span><input v-model.number="detalle.cantidad" type="number" min="1" required /></label>
        <label class="form-field"><span>Dosis *</span><input v-model.trim="detalle.dosis" placeholder="Ej. 1 cada 8 horas" required /></label>
        <label class="form-field"><span>Indicaciones</span><input v-model.trim="detalle.indicaciones" placeholder="Después de alimentos" /></label>
        <button type="button" class="remove-button" :disabled="receta.detalles.length === 1" title="Quitar medicamento" @click="quitarDetalle(index)">×</button>
      </article>
    </div>

    <div class="form-actions"><button type="button" class="secondary-button" @click="$emit('cancel')">Cancelar</button><button class="primary-button" :disabled="guardando"><span v-if="guardando" class="spinner"></span>{{ guardando ? 'Guardando...' : recetaEditar ? 'Actualizar receta' : 'Guardar receta' }}</button></div>
  </form>
</template>

<script>
import api from '../axios';
const detalleVacio = () => ({ medicamento_id: '', cantidad: 1, dosis: '', indicaciones: '' });
const fechaHoy = () => new Date().toISOString().slice(0, 10);

export default {
  name: 'RecetaForm', emits: ['saved', 'cancel'], props: { recetaEditar: { type: Object, default: null } },
  data() { return { receta: { fecha: fechaHoy(), numero_receta: '', medico_id: '', paciente_id: '', detalles: [detalleVacio()] }, medicos: [], pacientes: [], medicamentos: [], guardando: false, errores: {}, mensaje: '' }; },
  watch: { recetaEditar: { immediate: true, handler(value) { if (value) this.receta = { fecha: value.fecha, numero_receta: value.numero_receta, medico_id: value.medico_id, paciente_id: value.paciente_id, detalles: value.detalles.map((d) => ({ medicamento_id: d.medicamento_id, cantidad: d.cantidad, dosis: d.dosis, indicaciones: d.indicaciones || '' })) }; } } },
  async mounted() { try { const [m, p, md] = await Promise.all([api.get('medicos'), api.get('pacientes'), api.get('medicamentos')]); this.medicos = m.data; this.pacientes = p.data; this.medicamentos = md.data; } catch (_) { this.mensaje = 'No fue posible cargar los catálogos.'; } },
  methods: {
    agregarDetalle() { this.receta.detalles.push(detalleVacio()); }, quitarDetalle(index) { if (this.receta.detalles.length > 1) this.receta.detalles.splice(index, 1); }, errorDe(campo) { return this.errores[campo]?.[0]; },
    async guardar() {
      this.guardando = true; this.errores = {}; this.mensaje = '';
      const payload = { ...this.receta, medico_id: Number(this.receta.medico_id), paciente_id: Number(this.receta.paciente_id), detalles: this.receta.detalles.map((d) => ({ ...d, medicamento_id: Number(d.medicamento_id), cantidad: Number(d.cantidad) })) };
      try { const response = this.recetaEditar ? await api.put(`recetas/${this.recetaEditar.id}`, payload) : await api.post('recetas', payload); this.$emit('saved', response.data); }
      catch (error) { if (error.response?.status === 422) { this.errores = error.response.data; this.mensaje = 'Revisa los campos marcados antes de continuar.'; } else this.mensaje = error.response?.data?.message || 'No fue posible guardar la receta.'; }
      finally { this.guardando = false; }
    }
  }
};
</script>
