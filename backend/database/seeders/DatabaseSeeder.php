<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\Medicamento;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Receta;
use App\Models\User;
use App\Models\Cita;
use App\Models\Consultorio;
use App\Models\Especialidad;
use App\Models\HorarioMedico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@recetas.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
            ]
        );

        $recepcion = User::updateOrCreate(['email' => 'recepcion@clinicanova.test'], [
            'name' => 'Sofía Recepción', 'telefono' => '2200-1000', 'password' => Hash::make('password'), 'rol' => 'recepcionista', 'activo' => true,
        ]);
        $usuarioMedico = User::updateOrCreate(['email' => 'medico@clinicanova.test'], [
            'name' => 'Dra. Ana Martínez', 'telefono' => '7000-1001', 'password' => Hash::make('password'), 'rol' => 'medico', 'activo' => true,
        ]);
        $usuarioPaciente = User::updateOrCreate(['email' => 'paciente@clinicanova.test'], [
            'name' => 'María López', 'telefono' => '7000-0001', 'password' => Hash::make('password'), 'rol' => 'paciente', 'activo' => true,
        ]);

        $general = Especialidad::updateOrCreate(['nombre' => 'Medicina general'], ['duracion_cita' => 30, 'activo' => true]);
        $pediatria = Especialidad::updateOrCreate(['nombre' => 'Pediatría'], ['duracion_cita' => 30, 'activo' => true]);
        Especialidad::updateOrCreate(['nombre' => 'Dermatología'], ['duracion_cita' => 40, 'activo' => true]);

        $hospital = Establecimiento::updateOrCreate(
            ['nombre' => 'Hospital Nacional'],
            ['direccion' => 'San Salvador']
        );

        $clinica = Establecimiento::updateOrCreate(
            ['nombre' => 'Clínica Central'],
            ['direccion' => 'Santa Tecla']
        );

        $medico = Medico::updateOrCreate(
            ['numero_jv' => 'JV-1001'],
            [
                'nombre' => 'Dra. Ana Martínez',
                'user_id' => $usuarioMedico->id,
                'especialidad' => 'Medicina general',
                'especialidad_id' => $general->id,
                'establecimiento_id' => $hospital->id,
            ]
        );

        $medicoPediatra = Medico::updateOrCreate(
            ['numero_jv' => 'JV-1002'],
            [
                'nombre' => 'Dr. Carlos Hernández',
                'especialidad' => 'Pediatría',
                'especialidad_id' => $pediatria->id,
                'establecimiento_id' => $clinica->id,
            ]
        );

        $paciente = Paciente::updateOrCreate(
            ['nombre' => 'María López', 'fecha_nacimiento' => '1990-05-15'],
            ['user_id' => $usuarioPaciente->id, 'email' => $usuarioPaciente->email, 'documento' => '01234567-8', 'telefono' => '7000-0001', 'direccion' => 'San Salvador', 'contacto_emergencia' => 'Laura López · 7000-0099']
        );

        Paciente::updateOrCreate(
            ['nombre' => 'José Pérez', 'fecha_nacimiento' => '1985-11-20'],
            ['telefono' => '7000-0002', 'direccion' => 'Santa Tecla']
        );

        $acetaminofen = Medicamento::updateOrCreate(
            ['nombre' => 'Acetaminofén', 'concentracion' => '500 mg'],
            ['presentacion' => 'Tabletas']
        );

        Medicamento::updateOrCreate(
            ['nombre' => 'Amoxicilina', 'concentracion' => '500 mg'],
            ['presentacion' => 'Cápsulas']
        );

        Medicamento::updateOrCreate(
            ['nombre' => 'Ibuprofeno', 'concentracion' => '400 mg'],
            ['presentacion' => 'Tabletas']
        );

        $receta = Receta::updateOrCreate(
            ['numero_receta' => 'REC-0001'],
            [
                'fecha' => now()->toDateString(),
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
            ]
        );

        $receta->detalles()->updateOrCreate(
            ['medicamento_id' => $acetaminofen->id],
            [
                'cantidad' => 10,
                'dosis' => '1 tableta cada 8 horas',
                'indicaciones' => 'Tomar después de los alimentos',
            ]
        );

        $consultorio = Consultorio::updateOrCreate(
            ['establecimiento_id' => $hospital->id, 'nombre' => 'Consultorio 1'],
            ['activo' => true]
        );
        Consultorio::updateOrCreate(['establecimiento_id' => $clinica->id, 'nombre' => 'Consultorio A'], ['activo' => true]);

        foreach ([1, 2, 3, 4, 5] as $dia) {
            HorarioMedico::updateOrCreate(
                ['medico_id' => $medico->id, 'establecimiento_id' => $hospital->id, 'dia_semana' => $dia],
                ['hora_inicio' => '08:00', 'hora_fin' => '16:00', 'intervalo_minutos' => 30, 'activo' => true]
            );
            HorarioMedico::updateOrCreate(
                ['medico_id' => $medicoPediatra->id, 'establecimiento_id' => $clinica->id, 'dia_semana' => $dia],
                ['hora_inicio' => '09:00', 'hora_fin' => '15:00', 'intervalo_minutos' => 30, 'activo' => true]
            );
        }

        $proximoDiaHabil = now()->addDay();
        while ($proximoDiaHabil->isWeekend()) $proximoDiaHabil->addDay();
        Cita::updateOrCreate(['codigo' => 'CIT-DEMO-001'], [
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'establecimiento_id' => $hospital->id,
            'consultorio_id' => $consultorio->id,
            'creada_por' => $recepcion->id,
            'fecha' => $proximoDiaHabil->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fin' => '09:30',
            'estado' => 'confirmada',
            'canal' => 'recepcion',
            'motivo' => 'Consulta de control general',
            'notas' => 'Paciente confirmó por teléfono.',
        ]);
    }
}
