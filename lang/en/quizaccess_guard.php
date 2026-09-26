<?php
// mod/quiz/accessrule/guard/lang/en/quizaccess_guard.php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Anti-Cheating Guard Access Rule';
$string['enabled'] = 'Enable Anti-Cheating Guard';
$string['enabled_help'] = 'If enabled, students must use the external Secure Runner to take this exam.';
$string['nextjs_url'] = 'Proctor Runner URL';
$string['nextjs_url_desc'] = 'Base URL of the secure exam application runner.';
$string['shared_secret'] = 'HMAC Shared Secret';
$string['shared_secret_desc'] = 'Shared secret token used to sign launch tokens and requests.';
$string['quiz_locked_notice'] = 'Exam Guard Protection Active';
$string['quiz_locked_desc'] = 'This quiz requires access through the secure exam proctoring runner.';
$string['open_in_runner_btn'] = 'Open in Secure Exam Runner';
$string['student_locked_heading'] = 'Exam Suspended (Cheating Indication)';
$string['student_locked_desc'] = 'Your exam session has been locked due to detected violations. Please wait for a proctor to review your log.';

// String Deskripsi Web Service & Kapabilitas Pengawas
$string['guard:manageoverrides'] = 'Manage and unlock exam guard attempt overrides';
$string['ws_get_active_attempts'] = 'Retrieve active exam attempts with student profiles and class metadata';
$string['ws_unlock_student'] = 'Unlock suspended student exam attempt';

// Metadata Privasi Data (GDPR / Privacy API)
$string['privacy:metadata:quizaccess_guard_locks'] = 'Records proctoring locks, reasons, and proctor overrides for exam attempts.';
$string['privacy:metadata:quizaccess_guard_locks:quizid'] = 'The ID of the quiz.';
$string['privacy:metadata:quizaccess_guard_locks:userid'] = 'The ID of the student.';
$string['privacy:metadata:quizaccess_guard_locks:attemptid'] = 'The ID of the quiz attempt.';
$string['privacy:metadata:quizaccess_guard_locks:islocked'] = 'Lockout status (1 for locked, 0 for unlocked).';
$string['privacy:metadata:quizaccess_guard_locks:lockreason'] = 'The logged reason for the lockout.';
$string['privacy:metadata:quizaccess_guard_locks:unlockedby'] = 'The ID of the proctor who unlocked the student.';
$string['privacy:metadata:quizaccess_guard_locks:timemodified'] = 'Timestamp of when the lockout status was updated.';