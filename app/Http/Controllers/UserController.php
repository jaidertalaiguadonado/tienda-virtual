<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para acceder al usuario autenticado
use App\Models\User; // Asegúrate de importar el modelo User

class UserController extends Controller
{
    /**
     * Guarda la latitud y longitud del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLocation(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Asegúrate de que hay un usuario autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Actualizar la ubicación del usuario
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            // Puedes devolver una respuesta JSON de éxito
            return response()->json([
                'message' => 'Ubicación guardada correctamente.',
                'latitude' => $user->latitude,
                'longitude' => $user->longitude
            ], 200);
        }

        // Si no hay usuario autenticado, devolver un error 401 (Unauthorized)
        return response()->json(['message' => 'No autenticado.'], 401);
    }
}