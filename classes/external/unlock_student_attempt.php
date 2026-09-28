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
use invalid_parameter_exception;

class unlock_student_attempt extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'quizid'    => new external_value(PARAM_INT, 'ID Kuis Moodle'),
            'attemptid' => new external_value(PARAM_INT, 'ID Attempt Siswa'),
            'userid'    => new external_value(PARAM_INT, 'ID Siswa', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute($quizid, $attemptid, $userid = 0) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'quizid'    => $quizid,
            'attemptid' => $attemptid,
            'userid'    => $userid,
        ]);

        // Cek konteks kuis dan hak akses pengawas/guru
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:grade', $context);

        // Cari record penguncian di tabel quizaccess_guard_locks
        $conditions = [
            'quizid'    => $params['quizid'],
            'attemptid' => $params['attemptid'],
        ];
        if (!empty($params['userid'])) {
            $conditions['userid'] = $params['userid'];
        }

        $lock = $DB->get_record('quizaccess_guard_locks', $conditions);

        if (!$lock) {
            return [
                'status'  => false,
                'message' => 'Data penguncian attempt siswa tidak ditemukan.',
            ];
        }

        // Buka status kunci
        $lock->islocked     = 0;
        $lock->unlockedby   = $USER->id;
        $lock->timemodified = time();

        $DB->update_record('quizaccess_guard_locks', $lock);

        return [
            'status'  => true,
            'message' => 'Berhasil membuka kunci attempt siswa.',
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status'  => new external_value(PARAM_BOOL, 'Status keberhasilan operasi'),
            'message' => new external_value(PARAM_TEXT, 'Pesan keterangan respons'),
        ]);
    }
}