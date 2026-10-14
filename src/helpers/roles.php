<?php
const ROLE_APPLICANT      = 1;
const ROLE_SUPER_ADMIN    = 2;
const ROLE_IMMEDIATE_BOSS = 3;
const ROLE_PERSONAL_FILE  = 4;
const ROLE_SUBJECT_CLERK  = 5;
const ROLE_BRANCH_OFFICER = 6;
const ROLE_FINAL_APPROVAL = 7;

function roleName($roleId) {
    $names = [
        1 => 'Applicant', 2 => 'Super Admin', 3 => 'Immediate Boss',
        4 => 'Personal File', 5 => 'Subject Clerk',
        6 => 'Branch Officer', 7 => 'Final Approval'
    ];
    return $names[(int)$roleId] ?? 'Applicant';
}

// Path (relative to baseUrl) where each role lands after login
function roleHomePath($roleId) {
    $paths = [
        1 => 'dashboard',
        2 => 'admin/dashboard.php',
        3 => 'admin/IB_Dashboard.php',
        4 => 'admin/PF_Dashboard.php',
        5 => 'admin/SC_dashboard.php',
        6 => 'admin/BO_Dashboard.php',
        7 => 'admin/FA_dashboard.php',
    ];
    return $paths[(int)$roleId] ?? 'dashboard';
}

function currentRoleId() {
    return (int)($_SESSION['role_id'] ?? 0);
}

// $path example: /admin/IB_Dashboard.php
function canAccessAdminPath($path) {
    $role = currentRoleId();
    if ($role < 2 || $role > 7) return false;   // applicants and unknown: no admin access
    if ($role === ROLE_SUPER_ADMIN) return true; // Super Admin can open everything

    $file = strtolower(basename($path));

    // Each dashboard belongs to exactly one role
    $dashboards = [
        'dashboard.php'    => 2,
        'ib_dashboard.php' => 3,
        'pf_dashboard.php' => 4,
        'sc_dashboard.php' => 5,
        'bo_dashboard.php' => 6,
        'fa_dashboard.php' => 7,
    ];
    if (isset($dashboards[$file])) {
        return $dashboards[$file] === $role;
    }

    // Files ending in _IB / -IB, _PF, _SC, _FA, _BO belong to that role
    $suffixRoles = ['ib' => 3, 'pf' => 4, 'sc' => 5, 'bo' => 6, 'fa' => 7];
    if (preg_match('/[_-](ib|pf|sc|fa|bo)\.php$/', $file, $m)) {
        return $suffixRoles[$m[1]] === $role;
    }

    // Shared admin files (no suffix): any staff role
    return true;
}