<?php

require('../../config.php');

use local_oc_course_creation\form\modified_copy_form;
use local_oc_course_creation\manager;


global $CFG, $DB;


$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);

$url = new moodle_url('/local/oc_course_creation/handle_copy_form.php', array('id' => $courseid));
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM);
$manager = new manager();

// Security and access checks.
require_login($course, false);
$copycaps = \core_course\management\helper::get_course_copy_capabilities();
require_all_capabilities($copycaps, $coursecontext);


if ($returnto == 'copylist') {
    // Redirect to category list_courses_to_copy page.
    $returnurl = new moodle_url('/local/oc_course_creation/list_courses_to_copy.php', array('categoryid' => $course->category));
} else {
    // Redirect back to course page if we came from there.
    $returnurl = new moodle_url('/course/view.php', array('id' => $courseid));
}

$title = get_string('copycoursetitle', 'backup', $course->shortname);

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context(\context_system::instance());
$PAGE->set_title($title);

// Get data ready for mform.
$mform = new modified_copy_form(
        $url,
        array('course' => $course, 'returnto' => $returnto, 'returnurl' => $returnurl));

if ($mform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect($returnurl);

} else if ($mdata = $mform->get_data()) {

    // Process the form and create the copy task.
    $mdata->startdate = new \DateTime(); // Integer timestamp of the start of the destination course.
    $mdata->enddate =  new \DateTime(); // Integer timestamp of the start of the destination course.
    $backupcopy = new \core_backup\copy\copy($mdata);
    $backupcopy->create_copy();

    if (!empty($mdata->submitdisplay)) {
        // Redirect to the copy progress overview.
        $progressurl = new moodle_url('/backup/copyprogress.php', array('id' => $courseid));
        redirect($progressurl);
    } else {
        // Redirect to the course view page.
        $coursesurl = new moodle_url('/course/view.php', array('id' => $courseid));
        redirect($coursesurl);
    }

} else {
    // This branch is executed if the form is submitted but the data doesn't validate,
    // or on the first display of the form.

    // Build the page output.
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    $mform->display();
    echo $OUTPUT->footer();
}

