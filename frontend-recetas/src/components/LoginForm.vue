<template>
  <div>
    <h2>Login</h2>
    <form @submit.prevent="login">
      <input v-model="usuario" placeholder="Usuario" required />
      <input v-model="password" type="password" placeholder="Contraseña" required />
      <button type="submit">Ingresar</button>
    </form>
  </div>
</template>

<script>
export default {
  name: 'LoginForm',
  data() {
    return { usuario: '', password: '' };
  },
  methods: {
    login() {
      // Aquí va tu lógica de login
      localStorage.setItem('token', '123'); // ejemplo
      location.reload(); // recarga para mostrar dashboard
    }
  }
}
</script>

<style scoped>
input { display: block; margin-bottom: 5px; }
button { margin-top: 5px; }
</style>
