<template>
  <div>
    <h2>Login</h2>
    <form @submit.prevent="login">
      <input v-model="email" type="email" placeholder="Correo electrónico" required />
      <input v-model="password" type="password" placeholder="Contraseña" required />
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="cargando">
        {{ cargando ? 'Ingresando...' : 'Ingresar' }}
      </button>
    </form>
  </div>
</template>

<script>
import api from '../axios';

export default {
  name: 'LoginForm',
  data() {
    return {
      email: 'admin@recetas.test',
      password: 'password',
      error: '',
      cargando: false
    };
  },
  methods: {
    async login() {
      this.error = '';
      this.cargando = true;

      try {
        const response = await api.post('login', {
          email: this.email,
          password: this.password
        });

        localStorage.setItem('token', response.data.access_token);
        localStorage.setItem('usuario', JSON.stringify(response.data.usuario));
        location.reload();
      } catch (error) {
        this.error = error.response?.data?.message || 'No fue posible iniciar sesión.';
      } finally {
        this.cargando = false;
      }
    }
  }
}
</script>

<style scoped>
input { display: block; margin-bottom: 5px; }
button { margin-top: 5px; }
.error { color: #b91c1c; }
</style>
