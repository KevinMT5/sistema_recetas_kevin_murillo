<template>
  <div>
    <h1>Panel de Recetas</h1>
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
  }
}
</script>
