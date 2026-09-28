<?php
define('CLI_SCRIPT', true);
require('D:\Project\Website\moodle.local\moodle\config.php');
global $DB;

echo "=== QUIZ ATTEMPTS ===\n";
$records = $DB->get_records_sql("SELECT id, quiz, userid, state, timestart, timefinish FROM {quiz_attempts} ORDER BY id DESC LIMIT 5");
print_r($records);

echo "=== MOCK API CALL ===\n";
require_once(__DIR__ . '/classes/external/get_active_attempts.php');
try {
    $res = \quizaccess_guard\external\get_active_attempts::execute(10); // Assume quizid = 10, or change it
    print_r($res);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
