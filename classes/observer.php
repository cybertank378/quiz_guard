<?php
namespace quizaccess_guard;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Observer triggered when a quiz attempt is submitted.
     * Webhooks to Next.js API.
     *
     * @param \mod_quiz\event\attempt_submitted $event
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        $nextjs_url = get_config('quizaccess_guard', 'runner_base_url');
        if (empty($nextjs_url)) {
            $nextjs_url = 'http://localhost:3000';
        }

        $quizid = $event->other['quizid'];
        $userid = $event->relateduserid;
        $attemptid = $event->objectid;

        // Create secure payload
        $secret = get_config('quizaccess_guard', 'secret_key') ?: 'exam_guard_secret';
        $timestamp = time();
        $payload_str = "{$quizid}:{$userid}:{$timestamp}";
        $signature = hash_hmac('sha256', $payload_str, $secret);
        
        $token = base64_encode("{$payload_str}:{$signature}");

        $webhook_url = $nextjs_url . '/api/proctoring/webhook/submitted';

        $data = [
            'quizId' => $quizid,
            'userId' => $userid,
            'attemptId' => $attemptid,
            'timestamp' => date('c', $timestamp)
        ];

        // Perform curl request to webhook
        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $token);
        $curl->setHeader('Content-Type: application/json');
        
        // Asynchronous / non-blocking if possible, but basic post works for now
        $curl->post($webhook_url, json_encode($data));
    }
}
