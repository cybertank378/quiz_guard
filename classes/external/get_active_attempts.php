<?php
namespace quizaccess_guard\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

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
                    qa.timemodified
                FROM {quiz_attempts} qa
                JOIN {user} u ON u.id = qa.userid
                WHERE qa.quiz = :quizid 
                  AND qa.state = 'inprogress'
                  AND u.deleted = 0
                ORDER BY qa.timemodified DESC";

        $records = $DB->get_records_sql($sql, ['quizid' => $params['quizid']]);

        // Cek status lock pada tabel guard lokal (jika tabel custom guard ada)
        $results = [];
        foreach ($records as $rec) {
            $fullname = fullname($rec);

            // Cek apakah attempt ini sedang dilock di tabel quizaccess_guard
            $islocked = false;
            if ($DB->get_manager()->table_exists('quizaccess_guard_locks')) {
                $islocked = $DB->record_exists('quizaccess_guard_locks', [
                    'attemptid' => $rec->attemptid,
                    'status' => 'locked',
                ]);
            }

            $results[] = [
                'attemptid'     => (int)$rec->attemptid,
                'quizid'        => (int)$rec->quizid,
                'userid'        => (int)$rec->userid,
                'username'      => $rec->username,
                'fullname'      => $fullname,
                'email'         => $rec->email,
                'state'         => $rec->state,
                'islocked'      => $islocked,
                'timestart'     => (int)$rec->timestart,
                'timemodified'  => (int)$rec->timemodified,
            ];
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'attemptid'     => new external_value(PARAM_INT, 'Attempt ID'),
                'quizid'        => new external_value(PARAM_INT, 'Quiz ID'),
                'userid'        => new external_value(PARAM_INT, 'User ID'),
                'username'      => new external_value(PARAM_RAW, 'Username siswa'),
                'fullname'      => new external_value(PARAM_RAW, 'Nama lengkap siswa'),
                'email'         => new external_value(PARAM_RAW, 'Email siswa'),
                'state'         => new external_value(PARAM_ALPHA, 'Status ujian (inprogress)'),
                'islocked'      => new external_value(PARAM_BOOL, 'Apakah kuis terkunci'),
                'timestart'     => new external_value(PARAM_INT, 'Waktu mulai ujian (timestamp)'),
                'timemodified'  => new external_value(PARAM_INT, 'Waktu modifikasi/heartbeat terakhir'),
            ])
        );
    }
}