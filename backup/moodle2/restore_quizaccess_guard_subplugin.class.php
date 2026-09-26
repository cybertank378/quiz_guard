<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_quiz_access_subplugin.class.php');

class restore_quizaccess_guard_subplugin extends restore_quiz_access_subplugin {

    protected function define_quiz_subplugin_structure() {
        return [
            new restore_path_element('quizaccess_guard', $this->get_pathfor('/quizaccess_guard')),
        ];
    }

    public function process_quizaccess_guard($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->quizid = $this->get_new_parentid('quiz');

        if (empty($data->quizid)) {
            return;
        }

        $newitemid = $DB->insert_record('quizaccess_guard', $data);
        $this->set_mapping('quizaccess_guard', $oldid, $newitemid);
    }
}