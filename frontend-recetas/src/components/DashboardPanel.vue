<template>
  <div class="app-shell">
    <aside class="sidebar" :class="{ open: menuAbierto }">
      <div class="brand sidebar-brand"><span class="brand-mark">CN</span><span>Clínica Nova</span></div>
      <div class="workspace-label"><span>{{ etiquetaRol }}</span><small>Espacio de trabajo</small></div>
      <nav class="sidebar-nav" aria-label="Navegación principal">
        <button v-for="item in navegacion" :key="item.id" class="nav-item" :class="{ active: seccion === item.id }" @click="cambiarSeccion(item.id)">
          <span class="nav-icon">{{ item.icono }}</span><span>{{ item.nombre }}</span>
        </button>
      </nav>
      <div class="sidebar-footer">
        <div class="user-card"><span class="user-avatar">{{ iniciales }}</span><div><strong>{{ usuario?.name }}</strong><small>{{ etiquetaRol }}</small></div></div>
        <button class="logout-button" @click="cerrarSesion">Cerrar sesión</button>
      </div>
    </aside>
    <button v-if="menuAbierto" class="sidebar-overlay" aria-label="Cerrar menú" @click="menuAbierto = false"></button>

    <main class="main-content">
      <header class="topbar">
        <button class="menu-button" aria-label="Abrir menú" @click="menuAbierto = true">☰</button>
        <div><p class="breadcrumb">Clínica Nova / {{ etiquetaRol }}</p><h1>{{ tituloActual }}</h1></div>
        <div class="topbar-actions">
          <span class="privacy-pill">Datos protegidos</span>
          <button v-if="seccion === 'recetas' && ['administrador','medico'].includes(usuario.rol)" class="primary-button" @click="nuevaReceta">+ Nueva receta</button>
        </div>
      </header>

      <div class="page-content">
        <RoleOverview v-if="seccion === 'inicio'" :usuario="usuario" @navigate="cambiarSeccion" />
        <AppointmentManager v-else-if="seccion === 'agenda'" ref="agenda" :key="agendaKey" :usuario="usuario" />
        <MedicalWorkspace v-else-if="seccion === 'consulta'" />
        <section v-else-if="seccion === 'recetas'" class="stack-lg">
          <RecetaForm v-if="mostrarFormulario && usuario.rol !== 'paciente'" :receta-editar="recetaSeleccionada" @saved="recetaGuardada" @cancel="cancelarFormulario" />
          <RecetasList :refresh-key="refreshRecetas" :readonly="usuario.rol === 'paciente'" @edit="editarReceta" @changed="refreshRecetas++" />
        </section>
        <CatalogManager v-else :key="seccion" :config="configCatalogos[seccion]" />
      </div>
    </main>
  </div>
</template>

<script>
import api from '../axios';
import AppointmentManager from './AppointmentManager.vue';
import CatalogManager from './CatalogManager.vue';
import MedicalWorkspace from './MedicalWorkspace.vue';
import RoleOverview from './RoleOverview.vue';
import RecetaForm from './RecetaForm.vue';
import RecetasList from './RecetasList.vue';

const menus = {
  administrador: [
    { id: 'inicio', nombre: 'Resumen', icono: '⌂' }, { id: 'agenda', nombre: 'Agenda', icono: 'C' }, { id: 'pacientes', nombre: 'Pacientes', icono: 'P' }, { id: 'medicos', nombre: 'Médicos', icono: 'M' }, { id: 'recetas', nombre: 'Recetas', icono: 'Rx' }, { id: 'usuarios', nombre: 'Usuarios y roles', icono: 'U' }, { id: 'especialidades', nombre: 'Especialidades', icono: 'E' }, { id: 'establecimientos', nombre: 'Sedes', icono: 'S' }, { id: 'consultorios', nombre: 'Consultorios', icono: 'CO' }, { id: 'horarios', nombre: 'Horarios médicos', icono: 'H' }, { id: 'medicamentos', nombre: 'Medicamentos', icono: '+' }
  ],
  recepcionista: [
    { id: 'inicio', nombre: 'Resumen', icono: '⌂' }, { id: 'agenda', nombre: 'Agenda', icono: 'C' }, { id: 'pacientes', nombre: 'Pacientes', icono: 'P' }
  ],
  medico: [
    { id: 'inicio', nombre: 'Mi jornada', icono: '⌂' }, { id: 'agenda', nombre: 'Mi agenda', icono: 'C' }, { id: 'consulta', nombre: 'Consulta clínica', icono: '+' }, { id: 'pacientes', nombre: 'Pacientes', icono: 'P' }, { id: 'recetas', nombre: 'Recetas', icono: 'Rx' }
  ],
  paciente: [
    { id: 'inicio', nombre: 'Inicio', icono: '⌂' }, { id: 'agenda', nombre: 'Mis citas', icono: 'C' }, { id: 'recetas', nombre: 'Mis recetas', icono: 'Rx' }
  ]
};

export default {
  name: 'DashboardPanel',
  components: { AppointmentManager, CatalogManager, MedicalWorkspace, RoleOverview, RecetaForm, RecetasList },
  props: { usuario: { type: Object, required: true } },
  data() {
    return {
      seccion: 'inicio', menuAbierto: false, mostrarFormulario: false, recetaSeleccionada: null, refreshRecetas: 0, agendaKey: 0,
      configCatalogos: {
        pacientes: { titulo: 'Pacientes', singular: 'paciente', endpoint: 'pacientes', descripcion: 'Perfiles de contacto y datos esenciales para la atención.', fields: [{ key: 'nombre', label: 'Nombre completo', required: true }, { key: 'email', label: 'Correo', type: 'email' }, { key: 'documento', label: 'Documento' }, { key: 'fecha_nacimiento', label: 'Fecha de nacimiento', type: 'date' }, { key: 'telefono', label: 'Teléfono', type: 'tel' }, { key: 'direccion', label: 'Dirección', wide: true }, { key: 'contacto_emergencia', label: 'Contacto de emergencia', wide: true }], columns: [{ key: 'nombre', label: 'Paciente' }, { key: 'documento', label: 'Documento' }, { key: 'fecha_nacimiento', label: 'Nacimiento', format: 'date' }, { key: 'telefono', label: 'Teléfono' }, { key: 'email', label: 'Correo' }] },
        medicos: { titulo: 'Médicos', singular: 'médico', endpoint: 'medicos', descripcion: 'Directorio profesional, especialidad y sede principal.', fields: [{ key: 'nombre', label: 'Nombre completo', required: true }, { key: 'numero_jv', label: 'Número JV', required: true }, { key: 'especialidad', label: 'Especialidad' }, { key: 'especialidad_id', label: 'Especialidad del catálogo', type: 'select', optionsEndpoint: 'especialidades' }, { key: 'establecimiento_id', label: 'Sede', type: 'select', required: true, optionsEndpoint: 'establecimientos' }], columns: [{ key: 'nombre', label: 'Médico' }, { key: 'numero_jv', label: 'JV' }, { key: 'especialidad_catalogo.nombre', label: 'Especialidad' }, { key: 'establecimiento.nombre', label: 'Sede' }] },
        medicamentos: { titulo: 'Medicamentos', singular: 'medicamento', endpoint: 'medicamentos', descripcion: 'Catálogo disponible para prescripción.', fields: [{ key: 'nombre', label: 'Nombre', required: true }, { key: 'presentacion', label: 'Presentación' }, { key: 'concentracion', label: 'Concentración' }], columns: [{ key: 'nombre', label: 'Medicamento' }, { key: 'presentacion', label: 'Presentación' }, { key: 'concentracion', label: 'Concentración' }] },
        establecimientos: { titulo: 'Sedes', singular: 'sede', endpoint: 'establecimientos', descripcion: 'Centros donde la clínica ofrece atención.', fields: [{ key: 'nombre', label: 'Nombre', required: true }, { key: 'direccion', label: 'Dirección', wide: true }], columns: [{ key: 'nombre', label: 'Sede' }, { key: 'direccion', label: 'Dirección' }] },
        especialidades: { titulo: 'Especialidades', singular: 'especialidad', endpoint: 'especialidades', descripcion: 'Servicios clínicos y duración estándar de cada cita.', fields: [{ key: 'nombre', label: 'Nombre', required: true }, { key: 'duracion_cita', label: 'Duración (minutos)', type: 'number', required: true }], columns: [{ key: 'nombre', label: 'Especialidad' }, { key: 'duracion_cita', label: 'Duración (min)' }] },
        consultorios: { titulo: 'Consultorios', singular: 'consultorio', endpoint: 'consultorios', descripcion: 'Espacios físicos disponibles en cada sede.', fields: [{ key: 'establecimiento_id', label: 'Sede', type: 'select', required: true, optionsEndpoint: 'establecimientos' }, { key: 'nombre', label: 'Nombre', required: true }], columns: [{ key: 'nombre', label: 'Consultorio' }, { key: 'establecimiento.nombre', label: 'Sede' }] },
        horarios: { titulo: 'Horarios médicos', singular: 'horario', endpoint: 'horarios', descripcion: 'Disponibilidad semanal que alimenta los espacios de la agenda.', fields: [{ key: 'medico_id', label: 'Médico', type: 'select', required: true, optionsEndpoint: 'medicos' }, { key: 'establecimiento_id', label: 'Sede', type: 'select', required: true, optionsEndpoint: 'establecimientos' }, { key: 'dia_semana', label: 'Día', type: 'select-static', required: true, options: [{ id: 1, nombre: 'Lunes' }, { id: 2, nombre: 'Martes' }, { id: 3, nombre: 'Miércoles' }, { id: 4, nombre: 'Jueves' }, { id: 5, nombre: 'Viernes' }, { id: 6, nombre: 'Sábado' }, { id: 7, nombre: 'Domingo' }] }, { key: 'hora_inicio', label: 'Hora de inicio', type: 'time', required: true }, { key: 'hora_fin', label: 'Hora de cierre', type: 'time', required: true }, { key: 'intervalo_minutos', label: 'Intervalo (min)', type: 'number', required: true }], columns: [{ key: 'medico.nombre', label: 'Médico' }, { key: 'establecimiento.nombre', label: 'Sede' }, { key: 'dia_semana', label: 'Día (1-7)' }, { key: 'hora_inicio', label: 'Inicio' }, { key: 'hora_fin', label: 'Fin' }, { key: 'intervalo_minutos', label: 'Intervalo' }] },
        usuarios: { titulo: 'Usuarios y roles', singular: 'usuario', endpoint: 'usuarios', descripcion: 'Controla quién puede acceder y qué puede hacer.', fields: [{ key: 'name', label: 'Nombre', required: true }, { key: 'email', label: 'Correo', type: 'email', required: true }, { key: 'telefono', label: 'Teléfono', type: 'tel' }, { key: 'rol', label: 'Rol', type: 'select-static', required: true, options: [{ id: 'administrador', nombre: 'Administrador' }, { id: 'recepcionista', nombre: 'Recepcionista' }, { id: 'medico', nombre: 'Médico' }, { id: 'paciente', nombre: 'Paciente' }] }, { key: 'password', label: 'Contraseña temporal', type: 'password' }], columns: [{ key: 'name', label: 'Usuario' }, { key: 'email', label: 'Correo' }, { key: 'rol', label: 'Rol' }, { key: 'telefono', label: 'Teléfono' }] }
      }
    };
  },
  computed: {
    navegacion() { return menus[this.usuario.rol] || menus.paciente; },
    tituloActual() { return this.navegacion.find((i) => i.id === this.seccion)?.nombre || 'Resumen'; },
    iniciales() { return this.usuario.name.split(' ').slice(0, 2).map((p) => p[0]).join('').toUpperCase(); },
    etiquetaRol() { return { administrador: 'Administración', recepcionista: 'Recepción', medico: 'Área médica', paciente: 'Portal del paciente' }[this.usuario.rol] || this.usuario.rol; }
  },
  mounted() {
    this.configCatalogos.pacientes.readonly = this.usuario.rol === 'medico';
    this.configCatalogos.pacientes.canDelete = this.usuario.rol === 'administrador';
    window.addEventListener('sesion-expirada', this.limpiarSesion);
  },
  beforeUnmount() { window.removeEventListener('sesion-expirada', this.limpiarSesion); },
  methods: {
    cambiarSeccion(id) { this.seccion = id; this.menuAbierto = false; this.cancelarFormulario(); },
    nuevaReceta() { this.recetaSeleccionada = null; this.mostrarFormulario = true; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    editarReceta(receta) { if (this.usuario.rol === 'paciente') return; this.recetaSeleccionada = receta; this.mostrarFormulario = true; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    cancelarFormulario() { this.mostrarFormulario = false; this.recetaSeleccionada = null; },
    recetaGuardada() { this.refreshRecetas++; this.cancelarFormulario(); },
    limpiarSesion() { localStorage.removeItem('token'); localStorage.removeItem('usuario'); this.$emit('logout'); },
    async cerrarSesion() { try { await api.post('logout'); } catch (_) { /* cierre local */ } this.limpiarSesion(); }
  }
};
</script>
