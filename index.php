<?php

/**
 * @package      report_coursecompletion
 */

// use moodle_url;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Set the URL for the page
$page_url = new moodle_url('/report/coursecompletion/coursecompletion.php');
$PAGE->set_url($page_url);
// required login to view the page
require_login();
if (isguestuser()) {
    $url = $CFG->wwwroot . "/login/index.php";
    $message = 'Guest user cannot access this page, Please login';
    redirect($url, $message);
}
if (!is_siteadmin()) {
    $url = $CFG->wwwroot . "/login/index.php";
    $message = 'Only admin can access this page, Please login as site admin';
    redirect($url, $message);
}
// ----
admin_externalpage_setup('reportcoursecompletion');
// set the context for the page
$PAGE->set_context(context_system::instance());



// Setting an appropriate title
$PAGE->set_title("Report");
// setting a heading
$PAGE->set_heading("Student User List");


// -----------------------------
$output = '';

require_once($CFG->dirroot . '/report/coursecompletion/classes/AllUserDetails.php');
$allusers = new AllUserDetails();
$allusersdetails = $allusers->getAllUsers();

$output = '<br><br><div>
<p><strong> List of students</strong></p>
        <table class="report-table">
            <thead> 
                <tr> 
                    <th> First Name</th>
                    <th> Last Name</th>
                </tr>
            </thead>
            <tbody>
        ';
foreach ($allusersdetails as $userdetail) {
    $checkUserAsStudent = $allusers->checkUserAsStudent($userdetail->id);
    if ($checkUserAsStudent == 1) {
        $url = new moodle_url('/report/coursecompletion/coursecompletion.php', ['userid' => $userdetail->id]);
        $output .= '
            <tr> 
                <td> <a href="' . $url . '">' . $userdetail->firstname . ' </a></td>
                <td> <a href="' . $url . '">' . $userdetail->lastname . ' </a></td>

            </tr>
        ';
    }
}
$output .= '
            </tbody>
        </table>
        </div>
    ';

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
