<?php
// includes/RoleAccess.php

class RoleAccess {
    protected $rolePermissions = [
        'administrateur' => [
            'form' => true,
            'calendar' => true,
            'validation' => true,
            'tracking' => true,
            'historique' => true,
            'deleteHistorique' => true,
            'modifyRequest' => true,
            'validateRequest' => true,
            'rejectRequest' => true
        ],
        'gestionnaire' => [
            'form' => true,
            'calendar' => true,
            'validation' => true,
            'tracking' => true,
            'historique' => true,
            'deleteHistorique' => false,
            'modifyRequest' => true,
            'validateRequest' => false,
            'rejectRequest' => false
        ],
        'validateur' => [
            'form' => false,
            'calendar' => true,
            'validation' => false,
            'tracking' => false,
            'suivi' => true,
            'tracking_v' => false,
            'historique' => true,
            'deleteHistorique' => false,
            'modifyRequest' => false,
            'validateRequest' => true,
            'rejectRequest' => true
        ],
        'utilisateur' => [
            'form' => false,
            'calendar' => true,
            'validation' => false,
            'tracking' => false,
            'historique' => false,
            'deleteHistorique' => false,
            'modifyRequest' => false,
            'validateRequest' => false,
            'rejectRequest' => false
        ]
    ];

    private $userRole;

    public function __construct($role) {
        $this->userRole = $role;
    }

    public function hasPermission($permission) {
        return isset($this->rolePermissions[$this->userRole][$permission]) 
            ? $this->rolePermissions[$this->userRole][$permission] 
            : false;
    }

    public function getUserRole() {
        return $this->userRole;
    }

    public function getRolePermissions($role = null) {
        if ($role === null) {
            $role = $this->userRole;
        }
        return isset($this->rolePermissions[$role]) ? $this->rolePermissions[$role] : [];
    }
}