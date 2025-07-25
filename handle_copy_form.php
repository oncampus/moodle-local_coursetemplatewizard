<?php
/**
 * handle copy form
 *
 * @package    local_ocbsbcoursecreation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ocbsbcoursecreation\form\modified_copy_form;
use local_ocbsbcoursecreation\manager;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');

$templateid = required_param('id', PARAM_INT); // ID der Vorlage
$templatecourse = get_course($templateid);
require_login($templatecourse);

$context = context_system::instance();
require_capability('moodle/course:create', $context);

// Zielkurse, in denen der aktuelle User Trainer ist.
$usercourses = enrol_get_users_courses($USER->id, true, 'id, fullname');
$courselist = [];
foreach ($usercourses as $uc) {
    $courselist[$uc->id] = format_string($uc->fullname);
}

$url = new moodle_url('/local/ocbsbcoursecreation/handle_copy_form.php', ['id' => $templateid]);

// Formular anlegen
$mform = new modified_copy_form($url, [
    'courses' => $courselist,
    'course' => $templatecourse
]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title(get_string("copy_template_title", "local_ocbsbcoursecreation"));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('copy_template_title', 'local_ocbsbcoursecreation'));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php'));
}else if ($mdata = $mform->get_data()) {
    $mdata->templateid = $templateid;
    $targetcourseid = $mdata->targetcourseid;
    $mdata->targetcourseid = $targetcourseid;

    $manager = new manager();
    $newcourseid = $manager->replace_course_with_template($mdata);
    redirect(new moodle_url('/course/view.php', ['id' => $newcourseid]));
} else {
    $mform->display();
}

echo $OUTPUT->footer();
