<?php
namespace quizaccess_guard\external;

defined('MOODLE_INTERNAL') || die();

if (file_exists($CFG->libdir . '/externallib.php')) {
    require_once($CFG->libdir . '/externallib.php');
}
if (!class_exists('external_api') && class_exists('core_external\external_api')) {
    class_alias('core_external\external_api', 'external_api');
    class_alias('core_external\external_function_parameters', 'external_function_parameters');
    class_alias('core_external\external_value', 'external_value');
    class_alias('core_external\external_single_structure', 'external_single_structure');
    class_alias('core_external\external_multiple_structure', 'external_multiple_structure');
    class_alias('core_external\external_warnings', 'external_warnings');
}

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class lock_student_attempt extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'quizid'     => new external_value(PARAM_INT, 'ID Kuis Moodle'),
            'attemptid'  => new external_value(PARAM_INT, 'ID Attempt Siswa'),
            'userid'     => new external_value(PARAM_INT, 'ID Siswa'),
            'lockreason' => new external_value(PARAM_TEXT, 'Alasan penguncian attempt', VALUE_DEFAULT, 'Pelanggaran batas toleransi ujian'),
        ]);
    }

    public static function execute($quizid, $attemptid, $userid, $lockreason = '') {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'quizid'     => $quizid,
            'attemptid'  => $attemptid,
            'userid'     => $userid,
            'lockreason' => $lockreason,
        ]);

        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:grade', $context);

        $now = time();
        $record = $DB->get_record('quizaccess_guard_locks', [
            'quizid'    => $params['quizid'],
            'userid'    => $params['userid'],
            'attemptid' => $params['attemptid'],
        ]);

        if ($record) {
            $record->islocked     = 1;
            $record->lockreason   = $params['lockreason'];
            $record->timemodified = $now;
            $DB->update_record('quizaccess_guard_locks', $record);
        } else {
            $DB->insert_record('quizaccess_guard_locks', (object)[
                'quizid'       => $params['quizid'],
                'userid'       => $params['userid'],
                'attemptid'    => $params['attemptid'],
                'islocked'     => 1,
                'lockreason'   => $params['lockreason'],
                'unlockedby'   => null,
                'timecreated'  => $now,
                'timemodified' => $now,
            ]);
        }

        return [
            'status'  => true,
            'message' => 'Attempt siswa berhasil dikunci di Moodle.',
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status'  => new external_value(PARAM_BOOL, 'Status keberhasilan'),
            'message' => new external_value(PARAM_TEXT, 'Pesan detail eksekusi'),
        ]);
    }
}