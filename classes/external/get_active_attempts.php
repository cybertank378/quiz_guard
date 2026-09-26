<?php
namespace quizaccess_guard\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use context_module;

class get_active_attempts extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'quizid' => new external_value(PARAM_INT, 'ID Quiz Moodle', VALUE_REQUIRED),
        ]);
    }

    public static function execute($quizid) {
        global $DB;

        // Validasi parameter masukan
        $params = self::validate_parameters(self::execute_parameters(), ['quizid' => $quizid]);
        $target_quiz_id = $params['quizid'];

        // Validasi kuis dan hak akses konteks modul
        $cm = get_coursemodule_from_instance('quiz', $target_quiz_id, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:grade', $context);

        // Kueri join tabel attempt, profil siswa (user), dan guard_locks
        $sql = "SELECT qa.id AS attemptid,
                       qa.quiz AS quizid,
                       qa.userid,
                       qa.state AS status,
                       qa.timestart,
                       qa.timefinish,
                       u.firstname,
                       u.lastname,
                       u.department,
                       u.institution,
                       COALESCE(gl.islocked, 0) AS islocked
                  FROM {quiz_attempts} qa
                  JOIN {user} u ON u.id = qa.userid
             LEFT JOIN {quizaccess_guard_locks} gl 
                    ON gl.quizid = qa.quiz 
                   AND gl.userid = qa.userid 
                   AND gl.attemptid = qa.id
                 WHERE qa.quiz = :quizid
                   AND qa.state = 'inprogress'
                   AND u.deleted = 0
              ORDER BY qa.timestart DESC";

        $records = $DB->get_records_sql($sql, ['quizid' => $target_quiz_id]);

        $results = [];
        foreach ($records as $row) {
            $fullname = fullname($row);
            
            // Kolom kelas siswa: memprioritaskan field department, fallback ke institution
            $classname = !empty($row->department) ? $row->department : (!empty($row->institution) ? $row->institution : '-');

            $results[] = [
                'attemptid'   => (int)$row->attemptid,
                'quizid'      => (int)$row->quizid,
                'userid'      => (int)$row->userid,
                'fullname'    => $fullname,
                'firstname'   => $row->firstname,
                'lastname'    => $row->lastname,
                'department'  => $classname,
                'status'      => $row->status,
                'islocked'    => (int)$row->islocked === 1,
                'timestart'   => (int)$row->timestart,
                'timefinish'  => (int)$row->timefinish,
            ];
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'attemptid'   => new external_value(PARAM_INT, 'ID attempt pengerjaan'),
                'quizid'      => new external_value(PARAM_INT, 'ID quiz'),
                'userid'      => new external_value(PARAM_INT, 'ID user siswa'),
                'fullname'    => new external_value(PARAM_TEXT, 'Nama lengkap siswa'),
                'firstname'   => new external_value(PARAM_TEXT, 'Nama depan'),
                'lastname'    => new external_value(PARAM_TEXT, 'Nama belakang'),
                'department'  => new external_value(PARAM_TEXT, 'Kelas / Departemen siswa'),
                'status'      => new external_value(PARAM_ALPHA, 'Status pengerjaan kuis (inprogress)'),
                'islocked'    => new external_value(PARAM_BOOL, 'Status terkunci guard'),
                'timestart'   => new external_value(PARAM_INT, 'Waktu mulai'),
                'timefinish'  => new external_value(PARAM_INT, 'Waktu selesai'),
            ])
        );
    }
}