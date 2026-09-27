<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\mod_quiz\event\attempt_submitted',
        'callback'    => 'quizaccess_guard\observer::attempt_submitted',
    ],
];
