<template>
  <div class="container mt-4">
    <h2 class="mb-4">Crear Receta</h2>
    <form @submit.prevent="crearReceta">

      <!-- Datos principales -->
      <div class="mb-3">
        <label class="form-label">Fecha:</label>
        <input v-model="receta.fecha" type="date" class="form-control" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Número de Receta:</label>
        <input v-model="receta.numero_receta" type="text" class="form-control" placeholder="Número de Receta" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Médico:</label>
        <select v-model="receta.medico_id" class="form-select" required>
          <option value="" disabled>Selecciona un médico</option>
          <option v-for="med in medicos" :key="med.id" :value="med.id">{{ med.nombre }}</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Paciente:</label>
        <select v-model="receta.paciente_id" class="form-select" required>
          <option value="" disabled>Selecciona un paciente</option>
          <option v-for="pac in pacientes" :key="pac.id" :value="pac.id">{{ pac.nombre }}</option>
        </select>
      </div>

      <!-- Detalles de medicamentos -->
      <h4 class="mt-4 mb-3">Medicamentos</h4>
      <div v-for="(detalle,index) in receta.detalles" :key="index" class="border p-3 mb-3 rounded">

        <div class="mb-2">
          <label class="form-label">Medicamento:</label>
          <select v-model="detalle.medicamento_id" class="form-select" required>
            <option value="" disabled>Selecciona un medicamento</option>
            <option v-for="med in medicamentos" :key="med.id" :value="med.id">{{ med.nombre }}</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Cantidad:</label>
          <input v-model="detalle.cantidad" type="number" min="1" class="form-control" required />
        </div>

        <div class="mb-2">
          <label class="form-label">Dosis:</label>
          <input v-model="detalle.dosis" type="text" class="form-control" placeholder="Dosis" required />
        </div>

        <div class="mb-2">
          <label class="form-label">Indicaciones:</label>
          <input v-model="detalle.indicaciones" type="text" class="form-control" placeholder="Indicaciones" />
        </div>

        <button @click.prevent="receta.detalles.splice(index,1)" class="btn btn-danger btn-sm">Eliminar</button>
      </div>

      <button @click.prevent="agregarDetalle" class="btn btn-secondary mb-3">Agregar Medicamento</button>
      <br />
      <button type="submit" class="btn btn-primary">Guardar Receta</button>
    </form>
  </div>
</template>

<script>
import api from '../axios'; // tu configuración de axios

export default {
  data() {
    return {
      receta: { fecha:'', numero_receta:'', medico_id:'', paciente_id:'', detalles:[] },
      medicos: [],
      pacientes: [],
      medicamentos: []
    };
  },
  mounted() {
    this.cargarCatalogos();
  },
  methods: {
    agregarDetalle() {
      this.receta.detalles.push({ medicamento_id:'', cantidad:1, dosis:'', indicaciones:'' });
    },
    async cargarCatalogos() {
      const token = localStorage.getItem('token');
      try {
        const [resMed, resPac, resMedics] = await Promise.all([
          api.get('medicos', { headers:{ Authorization: `Bearer ${token}` } }),
          api.get('pacientes', { headers:{ Authorization: `Bearer ${token}` } }),
          api.get('medicamentos', { headers:{ Authorization: `Bearer ${token}` } })
        ]);
        this.medicos = resMed.data;
        this.pacientes = resPac.data;
        this.medicamentos = resMedics.data;
      } catch (err) {
        console.error('Error cargando catálogos', err);
      }
    },
    async crearReceta() {
      if(!this.receta.fecha || !this.receta.numero_receta || !this.receta.medico_id || !this.receta.paciente_id) {
        alert('Completa todos los campos');
        return;
      }
      if(this.receta.detalles.length === 0) {
        alert('Agrega al menos un medicamento');
        return;
      }

      const token = localStorage.getItem('token');
      const recetaEnviar = {
        ...this.receta,
        medico_id: parseInt(this.receta.medico_id),
        paciente_id: parseInt(this.receta.paciente_id),
        detalles: this.receta.detalles.map(d => ({
          medicamento_id: parseInt(d.medicamento_id),
          cantidad: parseInt(d.cantidad),
          dosis: d.dosis,
          indicaciones: d.indicaciones
        }))
      };

      try {
        await api.post('recetas', recetaEnviar, { headers:{ Authorization: `Bearer ${token}` } });
        alert('Receta creada con éxito');
        this.receta = { fecha:'', numero_receta:'', medico_id:'', paciente_id:'', detalles:[] };
      } catch (err) {
        if(err.response && err.response.status === 422){
          alert('Error de validación: ' + JSON.stringify(err.response.data));
        } else {
          console.error(err);
        }
      }
    }
  }
};
</script>

<style scoped>
/* Puedes añadir ajustes extra si quieres */
</style>
