<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas | Agendar cita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 35px 20px;
            color: #1e293b;
        }
        .contenedor {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 25px #00000010;
        }
        h1 { color: #0f766e; }
        label { display: block; margin-top: 17px; font-weight: bold; }
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 7px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 15px;
        }
        button, .volver {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 18px;
            border: none;
            border-radius: 7px;
            background: #0f766e;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
        }
        .error {
            color: #b91c1c;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <main class="contenedor">
        <h1>Agendar cita médica</h1>
        <p>Completa los datos para registrar tu cita.</p>

        @if ($errors->any())
            <div class="error">
                <p>Por favor, revisa los siguientes errores:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('citas.store') }}" method="POST">
            @csrf

            <label for="doctor_id">Médico</label>
            <select id="doctor_id" name="doctor_id" required>
                <option value="">Selecciona un médico</option>
                @foreach ($medicos as $medico)
                    <option value="{{ $medico->id }}"
                        @selected(old('doctor_id') == $medico->id)>
                        {{ $medico->name }}
                    </option>
                @endforeach
            </select>

            <label for="especialidad">Especialidad</label>
            <input
                id="especialidad"
                type="text"
                name="especialidad"
                value="{{ old('especialidad') }}"
                maxlength="150"
                required
            >

            <label for="fecha_hora">Fecha y hora</label>
            <input
                id="fecha_hora"
                type="datetime-local"
                name="fecha_hora"
                value="{{ old('fecha_hora') }}"
                min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"
                required
            >

            <label for="motivo">Motivo de la consulta</label>
            <textarea
                id="motivo"
                name="motivo"
                rows="4"
                maxlength="2000"
                required
            >{{ old('motivo') }}</textarea>

            <button type="submit">Registrar cita</button>
            <a class="volver" href="{{ route('citas.index') }}">Volver</a>
        </form>
    </main>
</body>
</html>
