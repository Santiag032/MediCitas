<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas | Citas médicas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 35px 20px;
            color: #1e293b;
        }
        .contenedor {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 25px #00000010;
        }
        h1 { color: #0f766e; }
        .boton {
            display: inline-block;
            background: #0f766e;
            color: white;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .mensaje {
            padding: 12px;
            background: #dcfce7;
            color: #166534;
            border-radius: 7px;
            margin-bottom: 18px;
        }
        .tabla-contenedor { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        th { background: #f8fafc; }
        .vacio { padding: 25px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <main class="contenedor">
        <h1>MediCitas</h1>
        <p>Sistema de gestión de citas médicas</p>

        @if (session('status'))
            <div class="mensaje">{{ session('status') }}</div>
        @endif

        <a class="boton" href="{{ route('citas.create') }}">
            Agendar nueva cita
        </a>

        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Fecha y hora</th>
                        <th>Estado</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($citas as $cita)
                        <tr>
                            <td>{{ $cita->paciente->name ?? 'Sin asignar' }}</td>
                            <td>{{ $cita->medico->name ?? 'Sin asignar' }}</td>
                            <td>{{ $cita->especialidad }}</td>
                            <td>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>{{ ucfirst($cita->estado) }}</td>
                            <td>{{ $cita->motivo }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="vacio">
                                Todavía no hay citas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
