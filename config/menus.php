<?php

return [
    'system' => [
        'administrador' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'heroicon-m-home',
                'route' => 'system.admin.dashboard',
            ],
            [
                'label' => 'Personas',
                'icon'  => 'heroicon-m-users',
                'route' => 'system.admin.personas',
            ],
            [
                'label' => 'Accesos',
                'icon'  => 'heroicon-m-key',
                'route' => 'system.admin.accesos.index',
            ],
            [
                'label' => 'Verificación QR',
                'icon'  => 'heroicon-m-qr-code',
                'route' => 'system.admin.qr.index',
            ],
            [
                'label' => 'Incidencias',
                'icon'  => 'heroicon-m-exclamation-triangle',
                'route' => 'system.admin.incidencias.index',
            ],
            [
                'label' => 'Historial',
                'icon'  => 'heroicon-m-archive-box',
                'route' => 'system.admin.historial.index',
            ],
            [
                'label' => 'Auditoría',
                'icon'  => 'heroicon-m-clipboard-document-list',
                'route' => 'system.admin.activity-logs.index',
            ],
            [
                'label' => 'Gestión de Usuarios',
                'icon'  => 'heroicon-m-user-cog',
                'route' => 'system.admin.users.index',
            ],
            [
                'label' => 'Permisos',
                'icon'  => 'heroicon-m-lock-closed',
                'route' => 'system.admin.permissions.index',
            ],
            [
                'label' => 'Portátiles',
                'icon'  => 'heroicon-m-computer-desktop',
                'route' => 'system.admin.portatiles.index',
            ],
            [
                'label' => 'Vehículos',
                'icon'  => 'heroicon-m-truck',
                'route' => 'system.admin.vehiculos.index',
            ],
            [
                'label' => 'Programas de Formación',
                'icon'  => 'heroicon-m-academic-cap',
                'route' => 'system.admin.programas-formacion.index',
            ],
            [
                'label' => 'Papelera',
                'icon'  => 'heroicon-m-trash',
                'route' => 'system.admin.papelera.index',
            ],
        ],
        'celador' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'heroicon-m-home',
                'route' => 'system.celador.dashboard',
            ],
            [
                'label' => 'Personas',
                'icon'  => 'heroicon-m-users',
                'route' => 'system.celador.personas.index',
            ],
            [
                'label' => 'Accesos',
                'icon'  => 'heroicon-m-key',
                'route' => 'system.celador.accesos.index',
                'can'   => 'accesos.view',
            ],
            [
                'label' => 'Verificación QR',
                'icon'  => 'heroicon-m-qr-code',
                'route' => 'system.celador.qr.index',
            ],
            [
                'label' => 'Incidencias',
                'icon'  => 'heroicon-m-exclamation-triangle',
                'route' => 'system.celador.incidencias.index',
                'can'   => 'incidencias.view',
            ],
            [
                'label' => 'Historial',
                'icon'  => 'heroicon-m-archive-box',
                'route' => 'system.celador.historial.index',
            ],
        ],
    ],
];
