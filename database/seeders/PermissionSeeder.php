<?php

namespace Database\Seeders;
//johan
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir permisos por módulo
        $permisos = [
            // Usuarios
            [
                'nombre' => 'ver_usuarios',
                'modulo' => 'Usuarios',
                'descripcion' => 'Ver lista de usuarios del sistema',
            ],
            [
                'nombre' => 'crear_usuarios',
                'modulo' => 'Usuarios',
                'descripcion' => 'Crear nuevos usuarios',
            ],
            [
                'nombre' => 'editar_usuarios',
                'modulo' => 'Usuarios',
                'descripcion' => 'Editar información de usuarios',
            ],
            [
                'nombre' => 'eliminar_usuarios',
                'modulo' => 'Usuarios',
                'descripcion' => 'Eliminar usuarios del sistema',
            ],

            // Personas
            [
                'nombre' => 'ver_personas',
                'modulo' => 'Personas',
                'descripcion' => 'Ver lista de personas registradas',
            ],
            [
                'nombre' => 'crear_personas',
                'modulo' => 'Personas',
                'descripcion' => 'Registrar nuevas personas',
            ],
            [
                'nombre' => 'editar_personas',
                'modulo' => 'Personas',
                'descripcion' => 'Editar información de personas',
            ],
            [
                'nombre' => 'eliminar_personas',
                'modulo' => 'Personas',
                'descripcion' => 'Eliminar personas del registro',
            ],

            // Accesos
            [
                'nombre' => 'ver_accesos',
                'modulo' => 'Accesos',
                'descripcion' => 'Ver registros de accesos',
            ],
            [
                'nombre' => 'registrar_acceso',
                'modulo' => 'Accesos',
                'descripcion' => 'Registrar entrada/salida de personas',
            ],
            [
                'nombre' => 'editar_accesos',
                'modulo' => 'Accesos',
                'descripcion' => 'Modificar registros de acceso',
            ],
            [
                'nombre' => 'eliminar_accesos',
                'modulo' => 'Accesos',
                'descripcion' => 'Eliminar registros de acceso',
            ],

            // Incidencias
            [
                'nombre' => 'ver_incidencias',
                'modulo' => 'Incidencias',
                'descripcion' => 'Ver lista de incidencias',
            ],
            [
                'nombre' => 'crear_incidencias',
                'modulo' => 'Incidencias',
                'descripcion' => 'Reportar nuevas incidencias',
            ],
            [
                'nombre' => 'editar_incidencias',
                'modulo' => 'Incidencias',
                'descripcion' => 'Editar información de incidencias',
            ],
            [
                'nombre' => 'resolver_incidencias',
                'modulo' => 'Incidencias',
                'descripcion' => 'Marcar incidencias como resueltas',
            ],
            [
                'nombre' => 'eliminar_incidencias',
                'modulo' => 'Incidencias',
                'descripcion' => 'Eliminar incidencias',
            ],

            // Reportes
            [
                'nombre' => 'ver_historial',
                'modulo' => 'Reportes',
                'descripcion' => 'Ver historial de accesos',
            ],
            [
                'nombre' => 'exportar_reportes',
                'modulo' => 'Reportes',
                'descripcion' => 'Exportar reportes en PDF/Excel',
            ],

            // Permisos y Roles
            [
                'nombre' => 'ver_permisos',
                'modulo' => 'Administración',
                'descripcion' => 'Ver lista de permisos del sistema',
            ],
            [
                'nombre' => 'gestionar_permisos',
                'modulo' => 'Administración',
                'descripcion' => 'Crear, editar y eliminar permisos',
            ],
            [
                'nombre' => 'gestionar_roles',
                'modulo' => 'Administración',
                'descripcion' => 'Administrar roles y sus permisos',
            ],
        ];

        foreach ($permisos as $permiso) {
            Permission::updateOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        // Asignar todos los permisos al rol administrador
        $adminRole = Role::where('nombre', 'administrador')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // Asignar permisos básicos al rol celador
        $celadorRole = Role::where('nombre', 'celador')->first();
        if ($celadorRole) {
            $celadorPermissions = Permission::whereIn('nombre', [
                'ver_personas',
                'crear_personas',
                'ver_accesos',
                'registrar_acceso',
                'ver_incidencias',
                'crear_incidencias',
                'ver_historial',
            ])->get();
            
            $celadorRole->permissions()->sync($celadorPermissions->pluck('id'));
        }

        $this->command->info('✓ Permisos creados y asignados correctamente');
    }
}
