<template>
  <div class="container mt-4">
    <h2>Lista de Recetas</h2>

    <!-- Filtros -->
    <div class="mb-3">
      <input v-model="filtros.medico_id" placeholder="ID Médico" class="form-control mb-2" />
      <input v-model="filtros.medicamento_id" placeholder="ID Medicamento" class="form-control mb-2" />
      <input v-model="filtros.fecha" type="date" class="form-control mb-2" />
      <button @click="getRecetas" class="btn btn-primary">Filtrar</button>
    </div>

    <!-- Lista de Recetas -->
    <div v-if="recetas.length === 0" class="alert alert-info">No hay recetas</div>

    <ul class="list-group">
      <li v-for="receta in recetas" :key="receta.id" class="list-group-item mb-3">
        <div>
          <strong>Fecha:</strong> {{ receta.fecha }} <br>
          <strong>Número de Receta:</strong> {{ receta.numero_receta }} <br>
          <strong>Médico:</strong> {{ receta.medico?.nombre || '-' }} 
          (<strong>Establecimiento:</strong> {{ receta.medico?.establecimiento?.nombre || '-' }}) <br>
          <strong>Paciente:</strong> {{ receta.paciente?.nombre || '-' }} <br>
          <strong>Total Medicamentos:</strong> {{ receta.total_medicamentos }}
        </div>

        <!-- Tabla de Detalles de Medicamentos -->
        <table class="table table-sm mt-2">
          <thead>
            <tr>
              <th>Cantidad</th>
              <th>Medicamento</th>
              <th>Dosis</th>
              <th>Indicaciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="detalle in receta.detalles" :key="detalle.id">
              <td>{{ detalle.cantidad }}</td>
              <td>{{ detalle.medicamento?.nombre || '-' }}</td>
              <td>{{ detalle.dosis }}</td>
              <td>{{ detalle.indicaciones || '-' }}</td>
            </tr>
          </tbody>
        </table>

        <button @click="eliminarReceta(receta.id)" class="btn btn-danger btn-sm mt-2">Eliminar</button>
      </li>
    </ul>
  </div>
</template>

<script>
import api from '../axios';

export default {
  data() {
    return {
      recetas: [],
      filtros: { medico_id:'', medicamento_id:'', fecha:'' }
    };
  },
  mounted() {
    this.getRecetas();
  },
  methods: {
    async getRecetas() {
      const token = localStorage.getItem('token');
      try {
        const res = await api.get('recetas', { params: this.filtros, headers:{ Authorization: `Bearer ${token}` } });
        this.recetas = res.data;
      } catch (err) {
        console.error('Error cargando recetas', err);
      }
    },
    async eliminarReceta(id) {
      if(!confirm('¿Eliminar esta receta?')) return;
      const token = localStorage.getItem('token');
      try {
        await api.delete(`recetas/${id}`, { headers:{ Authorization: `Bearer ${token}` } });
        alert('Receta eliminada');
        this.getRecetas();
      } catch(err) {
        console.error('Error eliminando receta', err);
      }
    }
  }
}
</script>

<style scoped>
.list-group-item {
  background-color: #f8f9fa;
}
</style>
