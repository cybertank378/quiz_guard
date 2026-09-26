<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');

class quizaccess_guard extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        global $DB;

        $record = $DB->get_record('quizaccess_guard', ['quizid' => $quizobj->get_quizid()]);
        if (!$record || !$record->guardenabled) {
            return null; // Aturan tidak aktif pada kuis ini
        }

        return new self($quizobj, $timenow);
    }

    public function prevent_access() {
        global $USER, $DB;

        // 1. Cek lockout status di database
        $lock = $DB->get_record('quizaccess_guard_locks', [
            'quizid' => $this->quiz->id,
            'userid' => $USER->id,
            'islocked' => 1,
        ]);

        if ($lock) {
            $reason = s($lock->lockreason ?? 'Indikasi pelanggaran integritas');
            return "
                <div class='alert alert-danger text-center p-4'>
                    <h4>" . get_string('student_locked_heading', 'quizaccess_guard') . "</h4>
                    <p>{$reason}</p>
                    <p>" . get_string('student_locked_desc', 'quizaccess_guard') . "</p>
                </div>
            ";
        }

        // 2. Cek token verifikasi dari Next.js
        $token = optional_param('guard_token', '', PARAM_RAW);
        $timestamp = optional_param('guard_ts', 0, PARAM_INT);
        $nextjs_url = get_config('quizaccess_guard', 'nextjs_url');
        $secret = get_config('quizaccess_guard', 'shared_secret');

        if (empty($token) || empty($timestamp)) {
            return $this->render_runner_launcher($nextjs_url);
        }

        if (abs(time() - $timestamp) > 60) {
            return "<div class='alert alert-warning'>Token kedaluwarsa. Silakan muat ulang runner.</div>";
        }

        $payload = "{$this->quiz->id}:{$USER->id}:{$timestamp}";
        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $token)) {
            return "<div class='alert alert-danger'>Token autentikasi runner tidak valid.</div>";
        }

        return false; // Lolos verifikasi
    }

    private function render_runner_launcher(string $nextjs_url): string {
        $quiz_id = $this->quiz->id;
        $url = rtrim($nextjs_url, '/') . "/exam/{$quiz_id}";

        return "
            <div class='alert alert-warning text-center p-4'>
                <h4>" . get_string('quiz_locked_notice', 'quizaccess_guard') . "</h4>
                <p>" . get_string('quiz_locked_desc', 'quizaccess_guard') . "</p>
                <div class='mt-3'>
                    <a href='{$url}' class='btn btn-primary btn-lg' target='_blank'>
                        " . get_string('open_in_runner_btn', 'quizaccess_guard') . "
                    </a>
                </div>
            </div>
        ";
    }

    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $mform->addElement('header', 'guardheader', get_string('pluginname', 'quizaccess_guard'));
        $mform->addElement('advcheckbox', 'guardenabled', get_string('enabled', 'quizaccess_guard'));
        $mform->addHelpButton('guardenabled', 'enabled', 'quizaccess_guard');
        $mform->setDefault('guardenabled', 0);
    }

    public static function save_settings($quiz) {
        global $DB;

        $enabled = !empty($quiz->guardenabled) ? 1 : 0;
        $record = $DB->get_record('quizaccess_guard', ['quizid' => $quiz->id]);

        if ($record) {
            $record->guardenabled = $enabled;
            $DB->update_record('quizaccess_guard', $record);
        } else {
            $DB->insert_record('quizaccess_guard', (object)[
                'quizid' => $quiz->id,
                'guardenabled' => $enabled,
            ]);
        }
    }

    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_guard', ['quizid' => $quiz->id]);
        $DB->delete_records('quizaccess_guard_locks', ['quizid' => $quiz->id]);
    }
}