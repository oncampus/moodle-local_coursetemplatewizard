<?php
/**
 * handle copy form
 *
 * @package    local/oc_course_creation
 * @author     Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 * @var $USER
 */

use local_oc_course_creation\form\modified_copy_form;
use local_oc_course_creation\manager;

global $CFG, $DB;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
$courslist = new moodle_url('/local/oc_course_creation/list_courses_to_copy.php');

$url = new moodle_url('/local/oc_course_creation/handle_copy_form.php', array('id' => $courseid));
$manager = new manager();

// Security and access checks.
require_login($course, false);

$copycaps = [
        'moodle/course:create',
];
$categorycontext = context_coursecat::instance($course->category);
require_all_capabilities($copycaps, $categorycontext);

$title = get_string("addnewcourse");

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context(\context_system::instance());
$PAGE->set_title($title);
// Get data ready for mform.
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$editoroptions['context'] = $coursecontext;
$editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
$mform = new modified_copy_form($url, array(
                'editoroptions' => $editoroptions,
                'course' => $course)
);

if ($mform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect($courslist);

} else if ($mdata = $mform->get_data()) {

    // Process the form and create the copy task.
    $mdata->startdate = time(); // Integer timestamp of the start of the destination course.
    $mdata->enddate = time() + (6 * 4 * 7 * 24 * 60 * 60); // Integer timestamp of the start of the destination course.
    $mdata->keptroles = []; // Integer timestamp of the start of the destination course.

    $manager->create_copy($mdata, $course);

    if (!empty($mdata->submitdisplay)) {
        // Redirect to the copy progress overview.
        $course_view_url = new moodle_url('/course/view.php', array('id' => $newcourseid));
        redirect($course_view_url);
    } else {
        // Redirect to the course view page.
        redirect($courslist);
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