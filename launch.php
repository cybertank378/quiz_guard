<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once(__DIR__ . '/../../../../config.php');

$quizid = required_param('quizid', PARAM_INT);
$cmid   = optional_param('cmid', 0, PARAM_INT);
$uid    = required_param('uid', PARAM_INT);
$sig    = required_param('sig', PARAM_ALPHANUM);

// 1. Verifikasi Signature HMAC SHA-256
$secret = get_config('quizaccess_guard', 'secret_key') ?: 'exam_guard_secret';
$expected_sig = hash_hmac('sha256', "{$quizid}:{$uid}", $secret);

if (!hash_equals($expected_sig, $sig)) {
    throw new moodle_exception('invalidsignature', 'quizaccess_guard');
}

// 2. Jika user belum login atau sesi berbeda di dalam konteks iframe ini, lakukan login otomatis
global $USER, $DB;
if (!isloggedin() || (int)$USER->id !== (int)$uid) {
    $user = $DB->get_record('user', ['id' => $uid, 'deleted' => 0], '*', MUST_EXIST);
    complete_user_login($user);
}

// 3. Tentukan Course Module ID jika belum disediakan
if (!$cmid) {
    $cm = get_coursemodule_from_instance('quiz', $quizid, 0, false, MUST_EXIST);
    $cmid = (int)$cm->id;
}

// 4. Redirect langsung ke halaman kuis Moodle dengan parameter runner
$target_url = new moodle_url('/mod/quiz/view.php', [
    'id' => $cmid,
    'guard_runner' => 1
]);

redirect($target_url);
