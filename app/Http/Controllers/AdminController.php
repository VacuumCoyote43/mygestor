<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Constructor: Solo administradores pueden acceder
     */
    public function __construct()
    {
        // El middleware se aplica en las rutas
    }

    /**
     * Muestra el panel de administración con lista de usuarios
     */
    public function index()
    {
        $usuarios = User::orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario (admin)
     */
    public function create()
    {
        return view('admin.create');
    }

    /**
     * Guarda un nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'rol' => 'required|in:admin,jugador',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
        ]);

        return redirect()->route('admin.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario
     */
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.edit', compact('usuario'));
    }

    /**
     * Actualiza un usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6|confirmed',
            'rol' => 'required|in:admin,jugador',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'rol' => $request->rol,
        ];

        // Solo actualizar contraseña si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('admin.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario
     */
    public function destroy($id)
    {
        // No permitir que un admin se elimine a sí mismo
        if (Auth::id() == $id) {
            return redirect()->route('admin.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->route('admin.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
