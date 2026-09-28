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
use external_multiple_structure;
use external_single_structure;
use external_value;

class get_active_quizzes extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    public static function execute() {
        global $DB;

        $context = \context_system::instance();
        self::validate_context($context);

        $now = time();

        // Ambil kuis yang sedang dalam rentang waktu pengerjaan atau baru saja dimulai hari ini
        $sql = "
            SELECT q.id, q.course, c.fullname AS course_name, q.name, q.timeopen, q.timeclose
            FROM {quiz} q
            INNER JOIN {course} c ON c.id = q.course
            WHERE (q.timeopen <= :now1 AND (q.timeclose = 0 OR q.timeclose >= :now2))
               OR (q.timeopen >= :today_start AND q.timeopen <= :today_end)
            ORDER BY q.timeopen DESC
        ";

        $today_start = strtotime('today midnight');
        $today_end = strtotime('tomorrow midnight') - 1;

        $records = $DB->get_records_sql($sql, [
            'now1' => $now,
            'now2' => $now,
            'today_start' => $today_start,
            'today_end' => $today_end,
        ]);

        $results = [];
        foreach ($records as $row) {
            $results[] = [
                'quiz_id'     => (int)$row->id,
                'course_id'   => (int)$row->course,
                'course_name' => $row->course_name,
                'quiz_name'   => $row->name,
                'timeopen'    => (int)$row->timeopen,
                'timeclose'   => (int)$row->timeclose,
            ];
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'quiz_id'     => new external_value(PARAM_INT, 'ID Kuis Moodle'),
                'course_id'   => new external_value(PARAM_INT, 'ID Kursus'),
                'course_name' => new external_value(PARAM_TEXT, 'Nama Kursus'),
                'quiz_name'   => new external_value(PARAM_TEXT, 'Nama Kuis'),
                'timeopen'    => new external_value(PARAM_INT, 'Waktu Kuis Dimulai'),
                'timeclose'   => new external_value(PARAM_INT, 'Waktu Kuis Ditutup'),
            ])
        );
    }
}