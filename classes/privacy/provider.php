<?php
namespace quizaccess_guard\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('quizaccess_guard_locks', [
            'quizid'       => 'privacy:metadata:quizaccess_guard_locks:quizid',
            'userid'       => 'privacy:metadata:quizaccess_guard_locks:userid',
            'attemptid'    => 'privacy:metadata:quizaccess_guard_locks:attemptid',
            'islocked'     => 'privacy:metadata:quizaccess_guard_locks:islocked',
            'lockreason'   => 'privacy:metadata:quizaccess_guard_locks:lockreason',
            'unlockedby'   => 'privacy:metadata:quizaccess_guard_locks:unlockedby',
            'timemodified' => 'privacy:metadata:quizaccess_guard_locks:timemodified',
        ], 'privacy:metadata:quizaccess_guard_locks');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = 'quiz'
                  JOIN {quizaccess_guard_locks} qgl ON qgl.quizid = cm.instance
                 WHERE qgl.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'userid'       => $userid,
        ]);

        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        // Implementasi export GDPR jika ada permintaan download data akun
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_MODULE) {
            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if ($cm) {
                $DB->delete_records('quizaccess_guard_locks', ['quizid' => $cm->instance]);
            }
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $cm = get_coursemodule_from_id('quiz', $context->instanceid);
                if ($cm) {
                    $DB->delete_records('quizaccess_guard_locks', [
                        'quizid' => $cm->instance,
                        'userid' => $userid,
                    ]);
                }
            }
        }
    }
}