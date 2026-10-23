<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->index(['medico_id', 'fecha', 'hora_inicio'], 'citas_agenda_lookup_index');
            $table->dropUnique('citas_medico_id_fecha_hora_inicio_unique');
        });
    }

    public function down()
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->unique(['medico_id', 'fecha', 'hora_inicio']);
            $table->dropIndex('citas_agenda_lookup_index');
        });
    }
};
