<template>
  <main class="login-page">
    <section class="login-visual">
      <div class="brand brand-light">
        <span class="brand-mark">Rx</span>
        <span>Clínica Nova</span>
      </div>
      <div class="login-message">
        <span class="eyebrow">Gestión clínica integral</span>
        <h1>Más orden.<br>Mejor atención.</h1>
        <p>Agenda, pacientes, consultas y recetas conectados en una experiencia segura para todo tu equipo.</p>
        <div class="visual-stats">
          <div><strong>100%</strong><span>Información centralizada</span></div>
          <div><strong>24/7</strong><span>Acceso al historial</span></div>
        </div>
      </div>
      <div class="visual-orb orb-one"></div>
      <div class="visual-orb orb-two"></div>
    </section>

    <section class="login-form-panel">
      <form class="login-card" @submit.prevent="login">
        <div class="mobile-brand">
          <span class="brand-mark">Rx</span>
          <span>Clínica Nova</span>
        </div>
        <span class="eyebrow text-primary">Bienvenido de nuevo</span>
        <h2>Inicia sesión</h2>
        <p class="login-subtitle">Ingresa a tu espacio de trabajo según el rol asignado por la clínica.</p>

        <label class="field-label" for="email">Correo electrónico</label>
        <div class="input-shell">
          <span>@</span>
          <input id="email" v-model.trim="email" type="email" autocomplete="email" placeholder="nombre@clinica.com" required />
        </div>

        <label class="field-label" for="password">Contraseña</label>
        <div class="input-shell">
          <span>●</span>
          <input id="password" v-model="password" :type="mostrarPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Tu contraseña" required />
          <button type="button" class="visibility-button" @click="mostrarPassword = !mostrarPassword">
            {{ mostrarPassword ? 'Ocultar' : 'Ver' }}
          </button>
        </div>

        <div v-if="error" class="form-alert form-alert-error">{{ error }}</div>

        <button class="primary-button login-button" type="submit" :disabled="cargando">
          <span v-if="cargando" class="spinner"></span>
          {{ cargando ? 'Verificando acceso...' : 'Ingresar al sistema' }}
        </button>

        <div class="demo-access">
          <span>Explorar como</span>
          <button v-for="perfil in perfiles" :key="perfil.email" type="button" @click="usarPerfil(perfil)">{{ perfil.nombre }}</button>
        </div>
      </form>
    </section>
  </main>
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
      cargando: false,
      mostrarPassword: false,
      perfiles: [
        { nombre: 'Admin', email: 'admin@recetas.test' },
        { nombre: 'Recepción', email: 'recepcion@clinicanova.test' },
        { nombre: 'Médico', email: 'medico@clinicanova.test' },
        { nombre: 'Paciente', email: 'paciente@clinicanova.test' }
      ]
    };
  },
  methods: {
    usarPerfil(perfil) { this.email = perfil.email; this.password = 'password'; },
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
        this.$emit('authenticated', response.data);
      } catch (error) {
        this.error = error.response?.status === 401
          ? 'El correo o la contraseña no son correctos.'
          : 'No pudimos conectar con el servidor. Intenta nuevamente.';
      } finally {
        this.cargando = false;
      }
    }
  }
}
</script>
