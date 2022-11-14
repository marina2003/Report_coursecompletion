<?php

/**
 * @package    report_coursecompletion  

 */


class AllUserDetails
{
    public function getAllUsers()
    {
        global $DB;
        $query = '  SELECT * from {user} user 
            WHERE user.id <> :guest_id AND 
                user.suspended =:suspended_val AND 
                user.deleted =:deleted_val';
        $params = [
            'guest_id' => 1,
            'suspended_val' => 0,
            'deleted_val' => 0
        ];
        $allusers = $DB->get_records_sql($query, $params);
        return $allusers;
    }

    public function getUserDetail($userid)
    {
        global $DB;
        $query = '  SELECT * from {user} user 
            WHERE user.id =:userid ';
        $params = [
            'userid' => $userid
        ];
        $userDetail = $DB->get_records_sql($query, $params);
        return array_values($userDetail);
    }

    public function checkUserAsStudent($userid)
    {
        global $DB;
        $query = '  SELECT * from {role_assignments} role_assignments 
            WHERE role_assignments.userid = :userid AND 
            role_assignments.roleid =:role_assignments_val ';
        $params = [
            'userid' => $userid,
            'role_assignments_val' => 5
        ];
        $isStudentInAnyCourse = $DB->get_records_sql($query, $params);
        if($isStudentInAnyCourse){
            return 1;
        }else{
            return null;
        }
        
    }

    public function check_course_users_roles_student(int $courseid, int $userid) {
        global $DB;
    
        $context = context_course::instance($courseid);
        
        $query = '  SELECT * from {role_assignments} role_assignments 
            WHERE role_assignments.userid = :userid AND 
            role_assignments.roleid =:role_assignments_val AND
            role_assignments.contextid = :contextid_val';
        $params = [
            'userid' => $userid,
            'role_assignments_val' => 5,
            'contextid_val' => $context->id
        ];
        $records = $DB->get_records_sql($query, $params);
    
        if($records){
            return 1;
        }else{
            return null;
        }
    }

    public function getUserCourses($userid)
    {
        global $USER, $CFG, $DB;

        require_once($CFG->dirroot . '/course/renderer.php');
        require_once($CFG->dirroot . '/course/renderer.php');
        include_once($CFG->dirroot . '/course/lib.php');

        $chelper = new \coursecat_helper();

        $courses = enrol_get_users_courses($userid, true, '*', 'visible DESC, fullname ASC, sortorder ASC');
        
        foreach ($courses as $course) {
            $course->fullname = strip_tags($chelper->get_course_formatted_name($course));

            $courseobj = new \core_course_list_element($course);
            $completion = new \completion_info($course);

            // make sure completion is enabled.
            if ($completion->is_enabled()) {
                $percentage = \core_completion\progress::get_course_progress_percentage($course, $userid);

                if (!is_null($percentage)) {
                    $percentage = floor($percentage);
                }

                if (is_null($percentage)) {
                    $percentage = 0;
                }

                // Add completion data in course object.
                $course->completed = $completion->is_course_complete($userid);
                $course->progress  = $percentage;
            }

            $categoryId = $course->category;
            $course_categories = $DB->get_record('course_categories', ['id' => $categoryId], '*', MUST_EXIST);
            $course->categoryInfo = $course_categories;

            $course->link = $CFG->wwwroot . "/course/view.php?id=" . $course->id;

            // check course user completion
            $completionDate = $this->getUsersCourseModule($course->id,$userid);
            if ($percentage == 100){
                $course->completion_status="COMPLETE 100%";
                $course->completion_date = userdate($completionDate);
            }else{
                $course->completion_status="NOT-COMPLETE ".$percentage.'%';
                $course->completion_date = '';
            }
        }

        return array_values($courses);
    }


    public function getUsersCourseModule($courseid,$userid)
    {
        global $DB;
        $query = '  SELECT  course_modules.course courseid,
                            course_modules_completion.coursemoduleid cmid,
                            course_modules_completion.userid  userid, 
                            course_modules_completion.timemodified comp_updatetimedate
                    From {course_modules_completion} course_modules_completion
                    JOIN {course_modules} course_modules 
                    WHERE course_modules.id = course_modules_completion.coursemoduleid AND 
                        course_modules_completion.completionstate =:completionstate_val AND
                        course_modules_completion.userid =:userid AND 
                        course_modules.course =:course_id
                    ORDER BY course_modules_completion.timemodified  DESC';
        $params = [
            'userid' => $userid,
            'course_id' => $courseid,
            'completionstate_val'=>'1'
        ];
        $getUsersCourseModule = $DB->get_records_sql($query, $params,$limitfrom=0, $limitnum=1);
        foreach($getUsersCourseModule as $data){
            $returndate = $data->comp_updatetimedate;
        }
        return $returndate;
    }
}

