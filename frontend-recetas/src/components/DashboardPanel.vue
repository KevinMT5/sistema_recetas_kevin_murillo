<template>
  <div class="app-shell">
    <aside class="sidebar" :class="{ open: menuAbierto }">
      <div class="brand sidebar-brand"><span class="brand-mark">Rx</span><span>RecetaSalud</span></div>
      <nav class="sidebar-nav" aria-label="Navegación principal">
        <button v-for="item in navegacion" :key="item.id" class="nav-item" :class="{ active: seccion === item.id }" @click="cambiarSeccion(item.id)">
          <span class="nav-icon">{{ item.icono }}</span><span>{{ item.nombre }}</span>
        </button>
      </nav>
      <div class="sidebar-footer">
        <div class="user-card"><span class="user-avatar">{{ iniciales }}</span><div><strong>{{ usuario?.name || 'Administrador' }}</strong><small>{{ usuario?.rol || 'Administrador' }}</small></div></div>
        <button class="logout-button" @click="cerrarSesion">Cerrar sesión</button>
      </div>
    </aside>
    <button v-if="menuAbierto" class="sidebar-overlay" aria-label="Cerrar menú" @click="menuAbierto = false"></button>

    <main class="main-content">
      <header class="topbar">
        <button class="menu-button" aria-label="Abrir menú" @click="menuAbierto = true">☰</button>
        <div><p class="breadcrumb">Administración / {{ tituloActual }}</p><h1>{{ tituloActual }}</h1></div>
        <div class="topbar-actions">
          <span class="status-pill"><i></i>Sistema activo</span>
          <button v-if="seccion === 'recetas'" class="primary-button" @click="nuevaReceta">+ Nueva receta</button>
        </div>
      </header>

      <div class="page-content">
        <OverviewPanel v-if="seccion === 'inicio'" @navigate="cambiarSeccion" />
        <section v-else-if="seccion === 'recetas'" class="stack-lg">
          <RecetaForm v-if="mostrarFormulario" :receta-editar="recetaSeleccionada" @saved="recetaGuardada" @cancel="cancelarFormulario" />
          <RecetasList :refresh-key="refreshRecetas" @edit="editarReceta" @changed="refreshRecetas++" />
        </section>
        <CatalogManager v-else :key="seccion" :config="configCatalogos[seccion]" />
      </div>
    </main>
  </div>
</template>

<script>
import api from '../axios';
import CatalogManager from './CatalogManager.vue';
import OverviewPanel from './OverviewPanel.vue';
import RecetaForm from './RecetaForm.vue';
import RecetasList from './RecetasList.vue';

export default {
  name: 'DashboardPanel',
  components: { CatalogManager, OverviewPanel, RecetaForm, RecetasList },
  props: { usuario: { type: Object, default: null } },
  data() {
    return {
      seccion: 'inicio', menuAbierto: false, mostrarFormulario: false, recetaSeleccionada: null, refreshRecetas: 0,
      navegacion: [
        { id: 'inicio', nombre: 'Resumen', icono: '⌂' }, { id: 'recetas', nombre: 'Recetas', icono: 'Rx' },
        { id: 'pacientes', nombre: 'Pacientes', icono: 'P' }, { id: 'medicos', nombre: 'Médicos', icono: 'M' },
        { id: 'medicamentos', nombre: 'Medicamentos', icono: '+' }, { id: 'establecimientos', nombre: 'Establecimientos', icono: 'E' }
      ],
      configCatalogos: {
        pacientes: {
          titulo: 'Pacientes', singular: 'paciente', endpoint: 'pacientes', descripcion: 'Administra la información de las personas atendidas.',
          fields: [{ key: 'nombre', label: 'Nombre completo', required: true }, { key: 'fecha_nacimiento', label: 'Fecha de nacimiento', type: 'date' }, { key: 'telefono', label: 'Teléfono', type: 'tel' }, { key: 'direccion', label: 'Dirección', wide: true }],
          columns: [{ key: 'nombre', label: 'Paciente' }, { key: 'fecha_nacimiento', label: 'Nacimiento', format: 'date' }, { key: 'telefono', label: 'Teléfono' }, { key: 'direccion', label: 'Dirección' }]
        },
        medicos: {
          titulo: 'Médicos', singular: 'médico', endpoint: 'medicos', descripcion: 'Directorio médico y establecimiento asignado.',
          fields: [{ key: 'nombre', label: 'Nombre completo', required: true }, { key: 'numero_jv', label: 'Número JV', required: true }, { key: 'especialidad', label: 'Especialidad' }, { key: 'establecimiento_id', label: 'Establecimiento', type: 'select', required: true, optionsEndpoint: 'establecimientos' }],
          columns: [{ key: 'nombre', label: 'Médico' }, { key: 'numero_jv', label: 'JV' }, { key: 'especialidad', label: 'Especialidad' }, { key: 'establecimiento.nombre', label: 'Establecimiento' }]
        },
        medicamentos: {
          titulo: 'Medicamentos', singular: 'medicamento', endpoint: 'medicamentos', descripcion: 'Catálogo disponible para la prescripción médica.',
          fields: [{ key: 'nombre', label: 'Nombre', required: true }, { key: 'presentacion', label: 'Presentación' }, { key: 'concentracion', label: 'Concentración' }],
          columns: [{ key: 'nombre', label: 'Medicamento' }, { key: 'presentacion', label: 'Presentación' }, { key: 'concentracion', label: 'Concentración' }]
        },
        establecimientos: {
          titulo: 'Establecimientos', singular: 'establecimiento', endpoint: 'establecimientos', descripcion: 'Centros de atención asociados al equipo médico.',
          fields: [{ key: 'nombre', label: 'Nombre', required: true }, { key: 'direccion', label: 'Dirección', wide: true }],
          columns: [{ key: 'nombre', label: 'Establecimiento' }, { key: 'direccion', label: 'Dirección' }]
        }
      }
    };
  },
  computed: {
    tituloActual() { return this.navegacion.find((item) => item.id === this.seccion)?.nombre || 'Resumen'; },
    iniciales() { return (this.usuario?.name || 'Administrador').split(' ').slice(0, 2).map((p) => p[0]).join('').toUpperCase(); }
  },
  mounted() { window.addEventListener('sesion-expirada', this.limpiarSesion); },
  beforeUnmount() { window.removeEventListener('sesion-expirada', this.limpiarSesion); },
  methods: {
    cambiarSeccion(id) { this.seccion = id; this.menuAbierto = false; this.cancelarFormulario(); },
    nuevaReceta() { this.recetaSeleccionada = null; this.mostrarFormulario = true; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    editarReceta(receta) { this.recetaSeleccionada = receta; this.mostrarFormulario = true; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    cancelarFormulario() { this.mostrarFormulario = false; this.recetaSeleccionada = null; },
    recetaGuardada() { this.refreshRecetas++; this.cancelarFormulario(); },
    limpiarSesion() { localStorage.removeItem('token'); localStorage.removeItem('usuario'); this.$emit('logout'); },
    async cerrarSesion() { try { await api.post('logout'); } catch (_) { /* cierre local */ } this.limpiarSesion(); }
  }
};
</script>
