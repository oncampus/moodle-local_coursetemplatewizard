<?php
/**
 * handle copy form
 *
 * @package    local/ocbsbcoursecreation
 * @author     Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 * @var $USER
 */

use local_ocbsbcoursecreation\form\modified_copy_form;
use local_ocbsbcoursecreation\manager;

define('NO_OUTPUT_BUFFERING', true);
global $CFG, $DB;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
$courslist = new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php');

$url = new moodle_url('/local/ocbsbcoursecreation/handle_copy_form.php', ['id' => $courseid]);
$manager = new manager();
// Security and access checks.

$copycaps = [
        'moodle/course:create',
        'local/ocbsbcoursecreation:ocbsbcoursecreation_access_capability',
];
require_all_capabilities($copycaps, context_system::instance());

$title = get_string("addnewcourse");

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context(\context_system::instance());
$PAGE->set_title($title);
$PAGE->requires->js_call_amd('core_backup/async_backup', 'asyncBackupAllStatus', [context_course::instance($course->id)]);
// Get data ready for mform.
$editoroptions =
        ['maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true];
$editoroptions['context'] = $coursecontext;
$editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);

$manager->check_enrol($courseid, $USER->id, 1);
$mform = new modified_copy_form($url, [
                'editoroptions' => $editoroptions,
                'course' => $course,
        ]
);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
if ($mform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    $manager->unenrol($courseid, $USER->id);

    redirect($courslist);

} else if ($mdata = $mform->get_data()) {
    $context = context_course::instance($courseid);
    $copycaps = \core_course\management\helper::get_course_copy_capabilities();
    require_all_capabilities($copycaps, $context);
    // Submit the form data.
    $course = get_course($courseid);
    $async = get_config('local_ocbsbcoursecreation', 'async_process');
    $newcourseid = $manager->create_copy($mdata, $async);

    // Trigger a course created event.
    $course = get_course($newcourseid);
    $event = \core\event\course_created::create([
            'objectid' => $course->id,
            'context' => context_course::instance($course->id),
            'other' => ['shortname' => $course->shortname,
                    'fullname' => $course->fullname,
            ],
    ]);
    $event->trigger();
    $manager->unenrol($courseid, $USER->id);
    if (!empty($mdata->submitdisplay) && !$async) {
        // Redirect to the copy progress overview.
        $course_view_url = new moodle_url('/course/view.php', ['id' => $newcourseid]);
        redirect($course_view_url);
    } else if(!$async) {
        // Redirect to the course view page.
        redirect($courslist);
    }

} else {
    // This branch is executed if the form is submitted but the data doesn't validate,
    // or on the first display of the form.

    // Build the page output.
    $mform->display();
    $manager->unenrol($courseid, $USER->id);
}
echo $OUTPUT->footer();
