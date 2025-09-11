// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css';

// Crear instancia de Axios global
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', // URL de tu backend Laravel
});

// Hacer que api esté disponible en todos los componentes
const app = createApp(App);
app.config.globalProperties.$api = api;

app.mount('#app');
