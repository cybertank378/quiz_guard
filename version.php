<?php
// mod/quiz/accessrule/guard/version.php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'quizaccess_guard';
$plugin->version   = 2026092000;  // Naikkan ke build 2026092000 untuk sinkronisasi fungsi quizaccess_guard_get_active_attempts
$plugin->requires  = 2020061500;  // Minimum versi Moodle 3.9 (LTS)
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v1.3.0';    // Rilis fitur sinkronisasi nama dan kelas siswa aktif real-time