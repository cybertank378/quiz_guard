<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_quiz_access_subplugin.class.php');

class backup_quizaccess_guard_subplugin extends backup_quiz_access_subplugin {

    protected function define_quiz_subplugin_structure() {
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        $guardsettings = new backup_nested_element('quizaccess_guard', ['id'], [
            'guardenabled',
        ]);

        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($guardsettings);

        $guardsettings->set_source_table('quizaccess_guard', [
            'quizid' => backup::VAR_ACTIVITYID,
        ]);

        return $subplugin;
    }
}