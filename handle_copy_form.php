<?php
/**
 * @package    local/oc_course_creation
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
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
$copycaps = \core_course\management\helper::get_course_copy_capabilities();
require_all_capabilities($copycaps, $coursecontext);

$title = get_string('copycoursetitle', 'backup', $course->shortname);

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

    global $USER;
    $copyids = array();

    // Create the initial backupcontoller.
    $bc = new \backup_controller(\backup::TYPE_1COURSE, $courseid, \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_COPY, $USER->id, \backup::RELEASESESSION_NO);
    $copyids['backupid'] = $bc->get_backupid();

    // Create the initial restore contoller.
    list($fullname, $shortname) = \restore_dbops::calculate_course_names(
            0, get_string('copyingcourse', 'backup'), get_string('copyingcourseshortname', 'backup'));
    $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $course->category);
    $rc = new \restore_controller($copyids['backupid'], $newcourseid,
            \backup::INTERACTIVE_NO, \backup::MODE_COPY, $USER->id,
            \backup::TARGET_NEW_COURSE);
    $copyids['restoreid'] = $rc->get_restoreid();

    // Configure the controllers based on the submitted data.
    $mdata->copyids = $copyids;
    $mdata->id = $newcourseid;

    $bc->set_copy($mdata);
    $bc->set_status(\backup::STATUS_AWAITING);

    $rc->set_copy($mdata);
    $rc->save_controller();

    $asynctask = new \core\task\asynchronous_copy_task();
    $asynctask->set_blocking(false);
    $asynctask->set_custom_data($copyids);
    $asynctask->execute();

    $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
    $course->visible = $mdata->visible;
    $course->idnumber = $mdata->idnumber;
    $course->enddate = $mdata->enddate;
    $course->category = $mdata->category;
    $DB->update_record('course', $course);

    $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
    $context = \context_course::instance($newcourseid);
    $editoroptions['context'] = $context;
    $editoroptions['subdirs'] = file_area_contains_subdirs($context, 'course', 'summary', 0);
    if ($editoroptions) {
        $data = file_postupdate_standard_editor($mdata, 'summary', $editoroptions, $context, 'course', 'summary', 0);
    }
    if ($overviewfilesoptions = course_overviewfiles_options($newcourseid)) {
        $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course',
                'overviewfiles', 0);
    }
    update_course($data, $editoroptions);

    enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);

    // Clean up the controller.
    $bc->destroy();

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