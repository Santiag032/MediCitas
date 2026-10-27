Nueva cita médica registrada en MediCitas

Paciente: {{ $cita->paciente->name }}
Correo del paciente: {{ $cita->paciente->email }}
Especialidad: {{ $cita->especialidad }}
Fecha y hora: {{ $cita->fecha_hora->format('d/m/Y H:i') }}
Motivo: {{ $cita->motivo }}
Estado: {{ $cita->estado }}
