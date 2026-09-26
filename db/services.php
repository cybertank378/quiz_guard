<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'quizaccess_guard_lock_student' => [
        'classname'   => 'quizaccess_guard\external\lock_student_attempt',
        'methodname'  => 'execute',
        'description' => 'Membekukan attempt siswa yang dicurigai mencontek',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities'=> 'mod/quiz:grade',
    ],
    'quizaccess_guard_unlock_student' => [
        'classname'   => 'quizaccess_guard\external\unlock_student_attempt',
        'methodname'  => 'execute',
        'description' => 'Membuka kunci pengerjaan kuis siswa oleh pengawas',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities'=> 'mod/quiz:grade',
    ],
    'quizaccess_guard_get_teachers' => [
        'classname'   => 'quizaccess_guard\external\get_teachers',
        'methodname'  => 'execute',
        'description' => 'Mengambil daftar guru aktif (editingteacher dan teacher) untuk sinkronisasi pengawas',
        'type'        => 'read',
        'ajax'        => false,
        'capabilities'=> 'mod/quiz:grade',
    ],
    'quizaccess_guard_get_active_quizzes' => [
        'classname'   => 'quizaccess_guard\external\get_active_quizzes',
        'methodname'  => 'execute',
        'description' => 'Mengambil daftar kuis yang sedang aktif/berjalan saat ini',
        'type'        => 'read',
        'ajax'        => false,
        'capabilities'=> 'mod/quiz:grade',
    ],
    // --- FUNGSI BARU: Mengambil siswa yang sedang aktif ujian ---
    'quizaccess_guard_get_active_attempts' => [
        'classname'   => 'quizaccess_guard\external\get_active_attempts',
        'methodname'  => 'execute',
        'description' => 'Mengambil daftar siswa yang sedang aktif mengerjakan kuis beserta nama dan kelasnya',
        'type'        => 'read',
        'ajax'        => false,
        'capabilities'=> 'mod/quiz:grade',
    ],
];