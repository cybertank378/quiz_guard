<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'quizaccess_guard_get_active_attempts' => [
        'classname'   => 'quizaccess_guard\external\get_active_attempts',
        'methodname'  => 'execute',
        'description' => 'Mendapatkan daftar siswa yang sedang aktif ujian pada kuis tertentu',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'mod/quiz:viewreports',
    ],
    'quizaccess_guard_get_active_quizzes' => [
        'classname'   => 'quizaccess_guard\external\get_active_quizzes',
        'methodname'  => 'execute',
        'description' => 'Mendapatkan daftar kuis yang sedang aktif dengan aturan guard',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'quizaccess_guard_lock_student_attempt' => [
        'classname'   => 'quizaccess_guard\external\lock_student_attempt',
        'methodname'  => 'execute',
        'description' => 'Mengunci attempt siswa karena pelanggaran',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'quizaccess_guard_unlock_student_attempt' => [
        'classname'   => 'quizaccess_guard\external\unlock_student_attempt',
        'methodname'  => 'execute',
        'description' => 'Membuka kunci attempt siswa yang terkunci',
        'type'        => 'write',
        'ajax'        => true,
    ],
];

// Otomatis daftarkan ke pre-built service Moodle
$services = [
    'Quiz Guard Proctor Service' => [
        'functions' => [
            'quizaccess_guard_get_active_attempts',
            'quizaccess_guard_get_active_quizzes',
            'quizaccess_guard_lock_student_attempt',
            'quizaccess_guard_unlock_student_attempt',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'quizaccess_guard_service',
    ],
];