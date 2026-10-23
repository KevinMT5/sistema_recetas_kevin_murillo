<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono', 25)->nullable()->after('email');
            $table->boolean('activo')->default(true)->after('rol');
        });

        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->unsignedSmallInteger('duracion_cita')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('medicos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('especialidad_id')->nullable()->after('especialidad')->constrained('especialidades')->nullOnDelete();
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->nullOnDelete();
            $table->string('email')->nullable()->after('nombre');
            $table->string('documento', 40)->nullable()->unique()->after('email');
            $table->string('contacto_emergencia')->nullable()->after('direccion');
        });

        Schema::create('consultorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['establecimiento_id', 'nombre']);
        });

        Schema::create('horarios_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedSmallInteger('intervalo_minutos')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('consultorio_id')->nullable()->constrained('consultorios')->nullOnDelete();
            $table->foreignId('creada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado')->default('pendiente');
            $table->string('canal')->default('recepcion');
            $table->string('motivo');
            $table->text('notas')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamps();
            $table->unique(['medico_id', 'fecha', 'hora_inicio']);
            $table->index(['fecha', 'estado']);
        });

        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->unique()->constrained('citas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->dateTime('fecha_atencion')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('indicaciones')->nullable();
            $table->json('signos_vitales')->nullable();
            $table->timestamps();
        });

        Schema::table('recetas', function (Blueprint $table) {
            $table->foreignId('consulta_id')->nullable()->after('paciente_id')->constrained('consultas')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('recetas', fn (Blueprint $table) => $table->dropConstrainedForeignId('consulta_id'));
        Schema::dropIfExists('consultas');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('horarios_medicos');
        Schema::dropIfExists('consultorios');
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['email', 'documento', 'contacto_emergencia']);
        });
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('especialidad_id');
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::dropIfExists('especialidades');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['telefono', 'activo']));
    }
};
