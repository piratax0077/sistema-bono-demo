<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoServidorSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DatabaseSeeder::class, ReviewUsersSeeder::class, ConveniosRevisionSeeder::class]);
        DB::connection('personas_fast')->table('personas')->updateOrInsert(['rut_normalizado'=>'102115686'], ['rut_original'=>'10.211.568-6','rut_cuerpo'=>10211568,'rut_dv'=>'6','nombre1'=>'Paciente','appaterno'=>'Revisión','apmaterno'=>'Demo','nombre_completo'=>'Paciente Revisión Demo','estado'=>'activo','created_at'=>now(),'updated_at'=>now()]);
        DB::connection('personas_fast')->table('personas')->updateOrInsert(['rut_normalizado'=>'111111111'], ['rut_original'=>'11.111.111-1','rut_cuerpo'=>11111111,'rut_dv'=>'1','nombre1'=>'Médico','appaterno'=>'de Prueba','apmaterno'=>'Demo','nombre_completo'=>'Médico de Prueba','estado'=>'activo','created_at'=>now(),'updated_at'=>now()]);
        $pacienteId=DB::connection('medichile')->table('pacientes')->updateOrInsert(['rut'=>'10211568-6'],['nombre'=>'Paciente Revisión','email'=>'paciente@gmail.com','telefono'=>'+56900000000','created_at'=>now(),'updated_at'=>now()]);
        $paciente=DB::connection('medichile')->table('pacientes')->where('rut','10211568-6')->first();
        DB::connection('medichile')->table('profesionales')->updateOrInsert(['rut'=>'11111111-1'],['nombre'=>'Médico','apellido_uno'=>'de Prueba','apellido_dos'=>'Demo','email'=>'profesional@gmail.com','telefono_uno'=>'+56900000001','estado'=>1,'certificado'=>1,'id_tipo_atencion'=>1,'created_at'=>now(),'updated_at'=>now()]);
        $prof=DB::connection('medichile')->table('profesionales')->where('rut','11111111-1')->first();
        DB::connection('medichile')->table('horas_medicas')->updateOrInsert(['descripcion'=>'DEMO-AGENDA-DIARIA'],['fecha_consulta'=>now()->toDateString(),'hora_inicio'=>now()->format('H:i:s'),'hora_termino'=>now()->addMinutes(30)->format('H:i:s'),'observaciones'=>'Hora disponible para flujo demo','id_profesional'=>$prof->id,'id_paciente'=>$paciente->id,'id_estado'=>2,'created_at'=>now(),'updated_at'=>now()]);
        $this->call(DemoPacientePendienteSeeder::class);
        $this->call(AgendaOnlineDemoSeeder::class);
    }
}
