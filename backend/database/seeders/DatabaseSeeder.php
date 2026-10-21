<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\Medicamento;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Receta;
use App\Models\User;
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
        User::updateOrCreate(
            ['email' => 'admin@recetas.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
            ]
        );

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
                'especialidad' => 'Medicina general',
                'establecimiento_id' => $hospital->id,
            ]
        );

        Medico::updateOrCreate(
            ['numero_jv' => 'JV-1002'],
            [
                'nombre' => 'Dr. Carlos Hernández',
                'especialidad' => 'Pediatría',
                'establecimiento_id' => $clinica->id,
            ]
        );

        $paciente = Paciente::updateOrCreate(
            ['nombre' => 'María López', 'fecha_nacimiento' => '1990-05-15'],
            ['telefono' => '7000-0001', 'direccion' => 'San Salvador']
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
    }
}
