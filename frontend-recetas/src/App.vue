<template>
  <div id="app">
    <LoginForm v-if="!token" />
    <DashboardPanel v-else />
  </div>
</template>

<script>
import LoginForm from './components/LoginForm.vue';
import DashboardPanel from './components/DashboardPanel.vue';

export default {
  name: 'App',
  components: { LoginForm, DashboardPanel },
  computed: {
    token() { return localStorage.getItem('token'); }
  }
}
</script>
