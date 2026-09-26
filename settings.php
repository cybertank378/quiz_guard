<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'quizaccess_guard/nextjs_url',
        get_string('nextjs_url', 'quizaccess_guard'),
        get_string('nextjs_url_desc', 'quizaccess_guard'),
        'https://exam.domain-anda.com',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'quizaccess_guard/shared_secret',
        get_string('shared_secret', 'quizaccess_guard'),
        get_string('shared_secret_desc', 'quizaccess_guard'),
        ''
    ));
}