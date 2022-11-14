<?php
/**
 * @package      report_coursecompletion

 */


if ($hassiteconfig) {
    $url = $CFG->wwwroot . '/report/coursecompletion/index.php';
    $ADMIN->add('reports', new admin_externalpage('reportcoursecompletion', get_string('pluginname', 'report_coursecompletion'), $url));

    // No report settings.
    $settings = new admin_settingpage('report_coursecompletion_settings', new lang_string('pluginname', 'report_coursecompletion'));

}
