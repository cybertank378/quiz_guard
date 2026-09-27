<?php

namespace quizaccess_guard\tests;

use advanced_testcase;
use external_api;
use external_multiple_structure;
use external_single_structure;
use quizaccess_guard\external\get_active_attempts;

/**
 * @covers \quizaccess_guard\external\get_active_attempts
 */
class external_get_active_attempts_test extends advanced_testcase {

    public function test_execute_returns_structure() {
        // Load the external class file manually if autoloader is not configured during basic tests
        global $CFG;
        require_once($CFG->dirroot . '/mod/quiz/accessrule/guard/classes/external/get_active_attempts.php');

        $returns = get_active_attempts::execute_returns();

        // Ensure that it returns an external_multiple_structure
        $this->assertInstanceOf(external_multiple_structure::class, $returns);
        
        // Ensure that the content structure inside is external_single_structure
        $content = $returns->content;
        $this->assertInstanceOf(external_single_structure::class, $content);
        
        $keys = $content->keys;
        
        // Verify the expected keys from the frontend contract are present
        $expected_keys = [
            'attemptId',
            'quizId',
            'userId',
            'studentName',
            'className',
            'roomNumber',
            'status',
            'islocked',
            'timestart',
            'timefinish'
        ];

        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $keys, "Return structure should contain the '$key' key to match frontend MoodleActiveAttemptItem.");
        }
    }

    public function test_execute_data_mapping() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // 1. Setup Data: Course, Quiz, and User
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = $generator->create_module('quiz', ['course' => $course->id]);
        $user = $generator->create_user(['firstname' => 'Student', 'lastname' => 'One']);
        
        // Enrol user as student
        $studentrole = $this->getDataGenerator()->get_role_id('student');
        $generator->enrol_user($user->id, $course->id, $studentrole);

        // Simulate an in-progress attempt for the student
        global $DB;
        $attempt = new \stdClass();
        $attempt->quiz = $quiz->id;
        $attempt->userid = $user->id;
        $attempt->attempt = 1;
        $attempt->state = 'inprogress';
        $attempt->timestart = time();
        $attempt->timefinish = 0;
        $attempt->timemodified = time();
        $attempt->layout = '';
        $attempt->uniqueid = 123456;
        $attemptid = $DB->insert_record('quiz_attempts', $attempt);

        // 2. Execute the method
        $result = get_active_attempts::execute($quiz->id);
        
        // Ensure result is converted according to the returns structure
        $result = \external_api::clean_returnvalue(get_active_attempts::execute_returns(), $result);

        // 3. Assertions
        $this->assertCount(1, $result);
        $this->assertEquals($attemptid, $result[0]['attemptId']);
        $this->assertEquals($quiz->id, $result[0]['quizId']);
        $this->assertEquals($user->id, $result[0]['userId']);
        $this->assertEquals('Student One', $result[0]['studentName']);
        $this->assertEquals('', $result[0]['className']);
        $this->assertEquals('', $result[0]['roomNumber']);
        $this->assertEquals('inprogress', $result[0]['status']);
        $this->assertFalse($result[0]['islocked']);
        $this->assertEquals($attempt->timestart, $result[0]['timestart']);
        $this->assertEquals($attempt->timefinish, $result[0]['timefinish']);
    }
}
