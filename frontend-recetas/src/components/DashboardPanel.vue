<template>
  <div>
    <div class="encabezado">
      <h1>Panel de Recetas</h1>
      <button class="btn btn-outline-secondary" @click="cerrarSesion">Cerrar sesión</button>
    </div>
    <RecetaForm />
    <RecetasList />
  </div>
</template>

<script>
import RecetaForm from './RecetaForm.vue';
import RecetasList from './RecetasList.vue';

export default {
  name: 'DashboardPanel',
  components: {
    RecetaForm,
    RecetasList
  },
  methods: {
    cerrarSesion() {
      localStorage.removeItem('token');
      localStorage.removeItem('usuario');
      location.reload();
    }
  }
}
</script>

<style scoped>
.encabezado {
  align-items: center;
  display: flex;
  justify-content: space-between;
}
</style>
