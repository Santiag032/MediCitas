<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use App\Mail\NuevaCitaMedica;

class CitaController extends Controller
{
    public function index(): View
    {
        $citas = Cita::with(['paciente', 'medico'])
            ->orderBy('fecha_hora')
            ->get();

        return view('citas.index', compact('citas'));
    }

    public function create(): View
    {
        $medicos = \App\Models\User::orderBy('name')->get();

        return view('citas.create', compact('medicos'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'especialidad' => ['required', 'string', 'max:150'],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'motivo' => ['required', 'string', 'max:2000'],
        ]);

        $datos['patient_id'] = $request->user()->id;
        $datos['estado'] = 'pendiente';

        $cita = Cita::create($datos);

        try {
            $medico = $cita->medico;

            if ($medico && $medico->email) {
                Mail::raw(
                    "Se ha registrado una nueva cita médica.\n\n"
                    . "Paciente: {$cita->paciente->name}\n"
                    . "Especialidad: {$cita->especialidad}\n"
                    . "Fecha y hora: {$cita->fecha_hora->format('d/m/Y H:i')}\n"
                    . "Motivo: {$cita->motivo}",
                    function ($message) use ($medico) {
                        $message->to($medico->email)
                            ->subject('Nueva cita médica - MediCitas');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar la notificación de la cita.', [
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'mensaje' => 'Cita registrada correctamente.',
                'cita' => $cita->load(['paciente', 'medico']),
            ], 201);
        }

        return redirect()
            ->route('citas.index')
            ->with('status', 'Cita registrada correctamente.');
    }

   
    public function apiIndex(Request $request): JsonResponse
    {
        $consulta = Cita::with([
            'paciente:id,name,email',
            'medico:id,name,email',
        ])->where('patient_id', $request->user()->id);

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->query('estado'));
        }

        return response()->json([
            'citas' => $consulta->orderBy('fecha_hora')->get(),
        ]);
    }


   
    public function apiStore(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'especialidad' => ['required', 'string', 'max:150'],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'motivo' => ['required', 'string', 'max:2000'],
        ]);

        $datos['patient_id'] = $request->user()->id;
        $datos['estado'] = 'pendiente';

        
        $cita = Cita::create($datos);

        $cita->load(['paciente', 'medico']);

        try {
            if ($cita->medico && $cita->medico->email) {
                Mail::to($cita->medico->email)
                    ->send(new NuevaCitaMedica($cita));
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar la notificación de la cita.', [
                'cita_id' => $cita->id,
                'error' => $e->getMessage(),
            ]);
        }


        return response()->json([
            'mensaje' => 'Cita registrada correctamente.',
            'cita' => $cita->load([
                'paciente:id,name,email',
                'medico:id,name,email',
            ]),
        ], 201);
    }

    
    public function apiUpdate(Request $request, Cita $cita): JsonResponse
    {
        Gate::authorize('update', $cita);

        $datos = $request->validate([
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'especialidad' => ['sometimes', 'required', 'string', 'max:150'],
            'fecha_hora' => ['sometimes', 'required', 'date', 'after:now'],
            'motivo' => ['sometimes', 'required', 'string', 'max:2000'],
        ]);

        $cita->update($datos);

        return response()->json([
            'mensaje' => 'Cita actualizada correctamente.',
            'cita' => $cita->fresh()->load([
                'paciente:id,name,email',
                'medico:id,name,email',
            ]),
        ]);
    }

    public function apiDestroy(Cita $cita): JsonResponse
    {
        Gate::authorize('delete', $cita);

        $cita->delete();

        return response()->json([
            'mensaje' => 'Cita cancelada correctamente.',
        ]);
    }


}
