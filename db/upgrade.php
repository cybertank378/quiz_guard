<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_quizaccess_guard_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Template logika migrasi versi lanjutan bila diperlukan di masa mendatang
    return true;
}