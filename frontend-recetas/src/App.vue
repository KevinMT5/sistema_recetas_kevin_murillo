<template>
  <div id="app">
    <LoginForm v-if="!token" @authenticated="onAuthenticated" />
    <DashboardPanel v-else :usuario="usuario" @logout="onLogout" />
  </div>
</template>

<script>
import LoginForm from './components/LoginForm.vue';
import DashboardPanel from './components/DashboardPanel.vue';

export default {
  name: 'App',
  components: { LoginForm, DashboardPanel },
  data() {
    return {
      token: localStorage.getItem('token'),
      usuario: JSON.parse(localStorage.getItem('usuario') || 'null')
    };
  },
  methods: {
    onAuthenticated(payload) {
      this.token = payload.access_token;
      this.usuario = payload.usuario;
    },
    onLogout() {
      this.token = null;
      this.usuario = null;
    }
  }
}
</script>
