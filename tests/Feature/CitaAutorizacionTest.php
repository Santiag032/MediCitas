<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaAutorizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_paciente_no_puede_actualizar_la_cita_de_otro(): void
    {
        [$paciente, $cita] = $this->crearCitaDeOtroPaciente();

        $this->actingAs($paciente, 'sanctum')
            ->putJson('/api/citas/' . $cita->id, [
                'motivo' => 'Intento de modificación no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'motivo' => 'Consulta original',
        ]);
    }

    public function test_un_paciente_no_puede_eliminar_la_cita_de_otro(): void
    {
        [$paciente, $cita] = $this->crearCitaDeOtroPaciente();

        $this->actingAs($paciente, 'sanctum')
            ->deleteJson('/api/citas/' . $cita->id)
            ->assertForbidden();

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
        ]);
    }

    
    public function test_un_paciente_puede_actualizar_su_propia_cita(): void
    {
        [$paciente, $cita] = $this->crearCitaDeOtroPaciente();

        $cita->update([
            'patient_id' => $paciente->id,
        ]);

        $this->actingAs($paciente, 'sanctum')
            ->putJson('/api/citas/' . $cita->id, [
                'motivo' => 'Motivo actualizado por el propietario',
            ])
            ->assertOk()
            ->assertJsonPath(
                'cita.motivo',
                'Motivo actualizado por el propietario'
            );
    }

    public function test_un_paciente_puede_eliminar_su_propia_cita(): void
    {
        [$paciente, $cita] = $this->crearCitaDeOtroPaciente();

        $cita->update([
            'patient_id' => $paciente->id,
        ]);

        $this->actingAs($paciente, 'sanctum')
            ->deleteJson('/api/citas/' . $cita->id)
            ->assertOk()
            ->assertJsonPath(
                'mensaje',
                'Cita cancelada correctamente.'
            );

        $this->assertDatabaseMissing('citas', [
            'id' => $cita->id,
        ]);
    }


    private function crearCitaDeOtroPaciente(): array
    {
        $paciente = User::create([
            'name' => 'Paciente Uno',
            'email' => 'paciente1@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $propietario = User::create([
            'name' => 'Paciente Propietario',
            'email' => 'propietario@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $medico = User::create([
            'name' => 'Medico de Prueba',
            'email' => 'medico@example.com',
            'password' => 'MedCitas2026!',
        ]);

        $cita = Cita::create([
            'patient_id' => $propietario->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Medicina general',
            'fecha_hora' => now()->addDays(2),
            'estado' => 'pendiente',
            'motivo' => 'Consulta original',
        ]);

        return [$paciente, $cita];
    }
}
