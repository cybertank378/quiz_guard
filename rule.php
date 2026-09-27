<?php
defined('MOODLE_INTERNAL') || die();

use quizaccess_guard\rule;

class quizaccess_guard extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        // Cek apakah guard aktif pada kuis ini
        if (empty($quizobj->get_quiz()->guard_enabled)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    public function prevent_access() {
        global $USER;

        // Cek header khusus jika request datang dari Next.js WebView / Iframe runner
        $is_runner = optional_param('guard_runner', 0, PARAM_INT);
        if ($is_runner) {
            return false; // Berikan akses jika dibuka melalui Runner Next.js
        }

        // Jika siswa membuka via browser biasa langsung ke Moodle, blokir dan tampilkan tombol Runner
        $nextjs_url = get_config('quizaccess_guard', 'runner_base_url');
        if (empty($nextjs_url)) {
            $nextjs_url = 'https://proktor.smpn29jkt.sch.id'; // URL App Next.js Anda
        }

        $quizid = $this->quiz->id;
        $cmid   = $this->quizobj->get_cmid();
        $userid = $USER->id;

        // Buat signature verifikasi keamanan agar link tidak dipalsukan
        $secret = get_config('quizaccess_guard', 'secret_key') ?: 'exam_guard_secret';
        $signature = hash_hmac('sha256', "{$quizid}:{$userid}", $secret);

        $launch_url = "{$nextjs_url}/exam/{$quizid}?cmid={$cmid}&uid={$userid}&sig={$signature}";

        $html = '<div class="alert alert-warning text-center my-4" style="background:#fff3cd; border:1px solid #ffeeba; padding:20px; border-radius:8px;">';
        $html .= '<h4 class="alert-heading font-weight-bold">Exam Guard Protection Active</h4>';
        $html .= '<p>Ujian ini wajib dikerjakan melalui antarmuka <strong>Exam Proctor Runner</strong> resmi (bukan Safe Exam Browser).</p>';
        $html .= '<a href="' . s($launch_url) . '" class="btn btn-primary btn-lg mt-3" style="padding:10px 24px;">Open in Secure Exam Runner</a>';
        $html .= '</div>';

        return $html;
    }

    public function description() {
        return get_string('guard_protection_desc', 'quizaccess_guard');
    }
}