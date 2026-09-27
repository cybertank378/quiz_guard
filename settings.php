<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'quizaccess_guard/runner_base_url',
        'Proctor Runner Base URL',
        'Masukkan URL aplikasi Proctor (contoh: http://localhost:3000 atau https://exam.sekolah.sch.id)',
        'http://localhost:3000',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'quizaccess_guard/secret_key',
        'HMAC Shared Secret Key',
        'Kunci rahasia bersama antara Moodle dan Next.js untuk validasi sesi',
        'default_exam_guard_secret_2026'
    ));
}