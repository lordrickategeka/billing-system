<?php

namespace App\Livewire;

use Livewire\Component;

class SidebarComponent extends Component
{
    public function render()
    {
        $menu = [
            [
                'type' => 'link',
                'label' => 'Dashboard',
                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
            ],
            [
                'type' => 'dropdown',
                'label' => 'User Management',
                'icon' => 'M5 13l4 4L19 7',
                'active' => request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*'),
                'items' => [
                    [
                        'label' => 'Users',
                        'route' => 'users.index',
                        'active' => request()->routeIs('users.*'),
                    ],
                    [
                        'label' => 'Roles',
                        'route' => 'roles.index',
                        'active' => request()->routeIs('roles.*'),
                    ],
                    [
                        'label' => 'Permissions',
                        'route' => 'permissions.index',
                        'active' => request()->routeIs('permissions.*'),
                    ],
                ],
            ],
        ];
        return view('livewire.sidebar-component', ['menu' => $menu]);
    }
}
