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
use external_value;
use external_multiple_structure;
use external_single_structure;
use context_module;

class get_active_attempts extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'quizid' => new external_value(PARAM_INT, 'ID dari Kuis Moodle', VALUE_REQUIRED),
        ]);
    }

    public static function execute($quizid) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'quizid' => $quizid,
        ]);

        // Validasi konteks dan permission kuis
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:viewreports', $context);

        // Ambil data attempt siswa yang statusnya 'inprogress'
        $sql = "SELECT 
                    qa.id AS attemptid,
                    qa.quiz AS quizid,
                    qa.userid,
                    u.username,
                    u.firstname,
                    u.lastname,
                    u.email,
                    qa.attempt,
                    qa.state,
                    qa.timestart,
                    qa.timefinish,
                    qa.timemodified
                FROM {quiz_attempts} qa
                JOIN {user} u ON u.id = qa.userid
                INNER JOIN (
                    SELECT userid, MAX(id) AS max_attemptid
                    FROM {quiz_attempts}
                    WHERE quiz = :quizid_sub AND state = 'inprogress'
                    GROUP BY userid
                ) latest ON latest.max_attemptid = qa.id
                WHERE qa.quiz = :quizid 
                  AND qa.state = 'inprogress'
                  AND u.deleted = 0
                ORDER BY qa.timemodified DESC";

        $records = $DB->get_records_sql($sql, ['quizid_sub' => $params['quizid'], 'quizid' => $params['quizid']]);

        // Cek status lock pada tabel guard lokal (jika tabel custom guard ada)
        $results = [];
        foreach ($records as $rec) {
            $fullname = fullname($rec);

            // Cek apakah attempt ini sedang dilock di tabel quizaccess_guard
            $islocked = false;
            if ($DB->get_manager()->table_exists('quizaccess_guard_locks')) {
                $islocked = $DB->record_exists('quizaccess_guard_locks', [
                    'attemptid' => $rec->attemptid,
                    'islocked' => 1,
                ]);
            }

            $results[] = [
                'attemptId'     => (int)$rec->attemptid,
                'quizId'        => (int)$rec->quizid,
                'userId'        => (int)$rec->userid,
                'studentName'   => $fullname,
                'className'     => '',
                'roomNumber'    => '',
                'status'        => $rec->state,
                'islocked'      => $islocked,
                'timestart'     => (int)$rec->timestart,
                'timefinish'    => (int)$rec->timefinish,
            ];
        }

        

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'attemptId'     => new external_value(PARAM_INT, 'Attempt ID'),
                'quizId'        => new external_value(PARAM_INT, 'Quiz ID'),
                'userId'        => new external_value(PARAM_INT, 'User ID'),
                'studentName'   => new external_value(PARAM_RAW, 'Nama lengkap siswa'),
                'className'     => new external_value(PARAM_RAW, 'Nama kelas siswa'),
                'roomNumber'    => new external_value(PARAM_RAW, 'Nomor ruangan', VALUE_OPTIONAL),
                'status'        => new external_value(PARAM_ALPHA, 'Status ujian (inprogress)'),
                'islocked'      => new external_value(PARAM_BOOL, 'Apakah kuis terkunci'),
                'timestart'     => new external_value(PARAM_INT, 'Waktu mulai ujian (timestamp)'),
                'timefinish'    => new external_value(PARAM_INT, 'Waktu selesai (timestamp)'),
            ])
        );
    }
}