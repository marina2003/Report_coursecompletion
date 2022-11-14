<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
// Set the URL for the page
$PAGE->set_url(new moodle_url('/report/coursecompletion/coursecompletion.php'));
// required login to view the page
require_login();
if (isguestuser()) {
    $url = $CFG->wwwroot . "/login/index.php";
    $message = 'Guest user cannot access, Please login';
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
// set the Page layout like standard,admin ,base,course,incourse .... used by theme
// $PAGE->set_pagelayout('standard');
// Setting an appropriate title
$PAGE->set_title("Report");
// setting a heading
$PAGE->set_heading("User Course Completion Report");
// set an additional capability able edit blocks on this page.
$strcapability = 'moodle/site:manageblocks';
$PAGE->set_blocks_editing_capability($strcapability);
// Adds a CSS class to the body tag 
$strcssclass = 'cus-report';
$PAGE->add_body_class($strcssclass);
// include css and js for this page
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/report/coursecompletion/javascript.js'));
// navigation
$nav_page_url = new moodle_url('/report/coursecompletion/index.php');
$previewnode = $PAGE->navigation->add(get_string('pluginname','report_coursecompletion'), $nav_page_url, navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('User Course Completion Report');
$thingnode->make_active();


// -------------------
$output = '';

$userid   = required_param('userid', PARAM_INT);
if (empty($userid)) {
    $url = $CFG->wwwroot . "/report/coursecompletion/index.php";
    $message = 'User id is missing';
    redirect($url, $message);
}
// required_param('userid', null, PARAM_INT);
// $output .= $userid ;

require_once($CFG->dirroot . '/report/coursecompletion/classes/AllUserDetails.php');
$allUserDetails = new AllUserDetails();
$usercoursedetails = $allUserDetails->getUserCourses($userid);

$userDetails = $allUserDetails->getUserDetail($userid);
foreach ($userDetails as $data) {
    $firstname = $data->firstname;
    $lastname = $data->lastname;
}

$user_profile_url = $CFG->wwwroot . "/user/profile.php?id=" . $userid;

$output = '<br><br><div>
   <p><strong> Student Name : <a href="'.$user_profile_url.'" target="_blank">' . $firstname . '  ' . $lastname . '  </a> </strong></p>
        <table class="report-table">
            <thead> 
                <tr> 
                    <th> Course Name</th>
                    <th> Course Completion Status</th>
                    <th> Course Completion Date</th>
                </tr>
            </thead>
            <tbody>
        ';
foreach ($usercoursedetails as $coursedetails) {
    $records = $allUserDetails->check_course_users_roles_student($coursedetails->id,$userid);
    if ($records == 1) {
        $output .= '
            <tr>
                <td> <a href="' . $coursedetails->link . '" target="_blank">' . $coursedetails->fullname . '</a></td>
                <td> ' . $coursedetails->completion_status . '</td>
                <td> ' . $coursedetails->completion_date . '</td>
            </tr>
        ';
    }
}
$output .= '
            </tbody>
        </table>
        </div>
    ';
if (empty($usercoursedetails)) {
    $output .= $username . ' is not enrolled in any course <br> <a href="/report/coursecompletion/index.php"> Return Back</a>';
}
// -----------------

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
