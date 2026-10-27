<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaCitaMedica;

class CitasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_usuario_puede_registrarse_y_obtener_un_token(): void
    {
        $respuesta = $this->postJson('/api/registro', [
            'name' => 'Paciente de Prueba',
            'email' => 'paciente.test@example.com',
            'password' => 'MedCitas2026!',
            'password_confirmation' => 'MedCitas2026!',
        ]);

        $respuesta
            ->assertCreated()
            ->assertJsonStructure([
                'mensaje',
                'usuario' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_no_se_puede_consultar_citas_sin_autenticacion(): void
    {
        $this->getJson('/api/citas')
            ->assertUnauthorized();
    }

    public function test_un_paciente_autenticado_solo_consulta_sus_citas(): void
    {
        $paciente = User::create([
            'name' => 'Paciente Uno',
            'email' => 'paciente1@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $otroPaciente = User::create([
            'name' => 'Paciente Dos',
            'email' => 'paciente2@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $medico = User::create([
            'name' => 'Medico de Prueba',
            'email' => 'medico@example.com',
            'password' => 'MedCitas2026!',
        ]);

        Cita::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Medicina general',
            'fecha_hora' => now()->addDays(2),
            'estado' => 'pendiente',
            'motivo' => 'Consulta del primer paciente',
        ]);

        Cita::create([
            'patient_id' => $otroPaciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Medicina general',
            'fecha_hora' => now()->addDays(3),
            'estado' => 'pendiente',
            'motivo' => 'Consulta del segundo paciente',
        ]);

        $this->actingAs($paciente, 'sanctum')
            ->getJson('/api/citas')
            ->assertOk()
            ->assertJsonCount(1, 'citas')
            ->assertJsonPath(
                'citas.0.motivo',
                'Consulta del primer paciente'
            );
    }

    
    public function test_muestra_citas_confirmadas(): void
    {
        $paciente = \App\Models\User::create([
            'name' => 'Paciente Prueba',
            'email' => 'paciente.filtro@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $medico = \App\Models\User::create([
            'name' => 'Medico Prueba',
            'email' => 'medico.filtro@example.com',
            'password' => 'MedCitas2026!',
        ]);

        \App\Models\Cita::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'fecha_hora' => now()->addDays(2),
            'estado' => 'confirmada',
            'motivo' => 'Cita confirmada',
        ]);

        \App\Models\Cita::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Pediatría',
            'fecha_hora' => now()->addDays(3),
            'estado' => 'pendiente',
            'motivo' => 'Cita pendiente',
        ]);

        $this->actingAs($paciente, 'sanctum')
            ->getJson('/api/citas?estado=confirmada')
            ->assertOk()
            ->assertJsonCount(1, 'citas')
            ->assertJsonPath('citas.0.estado', 'confirmada')
            ->assertJsonPath('citas.0.motivo', 'Cita confirmada');
    }

    
    public function test_agendar_cita_y_envio_correo(): void
    {
        Mail::fake();

        $paciente = \App\Models\User::create([
            'name' => 'Paciente Correo',
            'email' => 'paciente.correo@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $medico = \App\Models\User::create([
            'name' => 'Medico Correo',
            'email' => 'medico.correo@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $respuesta = $this->actingAs($paciente, 'sanctum')
            ->postJson('/api/citas', [
                'doctor_id' => $medico->id,
                'especialidad' => 'Cardiología',
                'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'motivo' => 'Consulta de prueba para correo',
            ]);

        $respuesta->assertCreated();

        $this->assertDatabaseHas('citas', [
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'motivo' => 'Consulta de prueba para correo',
        ]);

        Mail::assertSent(NuevaCitaMedica::class, function ($correo) use ($medico) {
            return $correo->hasTo($medico->email);
        });
    }

    public function test_detecta_problema_n_mas_1(): void
    {
        $paciente = \App\Models\User::create([
            'name' => 'Paciente N1',
            'email' => 'paciente.n1@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $medico = \App\Models\User::create([
            'name' => 'Medico N1',
            'email' => 'medico.n1@example.com',
            'password' => 'MedCitas2026!',
        ]);

        for ($i = 0; $i < 5; $i++) {
            \App\Models\Cita::create([
                'patient_id' => $paciente->id,
                'doctor_id' => $medico->id,
                'especialidad' => 'Medicina general',
                'fecha_hora' => now()->addDays($i + 1),
                'estado' => 'pendiente',
                'motivo' => 'Prueba N+1 ' . $i,
            ]);
        }

        \Illuminate\Support\Facades\DB::flushQueryLog();
        \Illuminate\Support\Facades\DB::enableQueryLog();

        $respuesta = $this->actingAs($paciente, 'sanctum')
            ->getJson('/api/citas');

        $consultas = \Illuminate\Support\Facades\DB::getQueryLog();

        \Illuminate\Support\Facades\DB::disableQueryLog();

        $respuesta->assertOk()
            ->assertJsonCount(5, 'citas');

        $this->assertLessThan(
            10,
            count($consultas),
            'Se detectó un posible problema N+1: demasiadas consultas SQL.'
        );
    }


}
