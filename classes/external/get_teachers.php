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

class get_teachers extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Opsional filter Course ID', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute($courseid = 0) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
        ]);

        // Cek autentikasi dan konteks sistem
        $context = \context_system::instance();
        self::validate_context($context);

        $coursefilter = "";
        $queryparams = [];

        if (!empty($params['courseid'])) {
            $coursefilter = " AND c.id = :courseid ";
            $queryparams['courseid'] = $params['courseid'];
        }

        $sql = "
            SELECT DISTINCT 
                u.id AS user_id,
                u.username,
                u.firstname,
                u.lastname,
                u.email,
                r.shortname AS role_name,
                c.fullname AS course_name
            FROM {user} u
            INNER JOIN {role_assignments} ra ON ra.userid = u.id
            INNER JOIN {role} r ON r.id = ra.roleid
            INNER JOIN {context} cx ON cx.id = ra.contextid
            INNER JOIN {course} c ON c.id = cx.instanceid
            WHERE cx.contextlevel = 50
              AND r.shortname IN ('editingteacher', 'teacher')
              AND u.deleted = 0
              AND u.suspended = 0
              {$coursefilter}
            ORDER BY u.lastname, c.fullname
        ";

        $records = $DB->get_records_sql($sql, $queryparams);

        $results = [];
        foreach ($records as $row) {
            $results[] = [
                'user_id'     => (int)$row->user_id,
                'username'    => $row->username,
                'firstname'   => $row->firstname,
                'lastname'    => $row->lastname,
                'email'       => $row->email,
                'role_name'   => $row->role_name,
                'course_name' => $row->course_name,
            ];
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'user_id'     => new external_value(PARAM_INT, 'Moodle User ID'),
                'username'    => new external_value(PARAM_TEXT, 'Username akun Moodle'),
                'firstname'   => new external_value(PARAM_TEXT, 'Nama depan'),
                'lastname'    => new external_value(PARAM_TEXT, 'Nama belakang'),
                'email'       => new external_value(PARAM_EMAIL, 'Email'),
                'role_name'   => new external_value(PARAM_TEXT, 'editingteacher atau teacher'),
                'course_name' => new external_value(PARAM_TEXT, 'Nama mata pelajaran / course'),
            ])
        );
    }
}