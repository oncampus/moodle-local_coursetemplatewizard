<?php

/**
 * @package     local_oc_course_creation
 * @category    manager
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $DB;
require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

//use local_oc_course_creation\form\string_form;
use local_oc_course_creation\form\string_form;
use local_oc_course_creation\manager;

$PAGE->set_url(new moodle_url('/local/oc_course_creation/create.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));

$manager = new manager();
$mform = new string_form();

$setCourseCategory = get_config('local_oc_course_creation', 'category');
$categories = core_course_category::get_all(array('returnhidden' => true));
$category = null;

foreach ($categories as $item) {
    if ($item->name == $setCourseCategory) {
        $category = $item;
    }
}

$courseIds = $category->get_courses(array('idonly' => true));

$context = context_coursecat::instance($category->id);
$PAGE->requires->js_call_amd('local_oc_course_creation/create_course_copy_modal', 'init', array($context->id));
$PAGE->requires->js_call_amd('local_oc_course_creation/formControl');

$url = new moodle_url('/backup/copy.php');
$url->param('categoryid', $category->id);

$courses = array();
$i = 0;
foreach ($courseIds as $courseId) {
    $courses[$i++] = $DB->get_record('course', array('id' => $courseId));
}

$boolCoursesInCat = $category->has_courses();

if ($mform->is_cancelled()) {
    //nothing happens
} else if ($fromform = $mform->get_data()) {
    //insert the data in the db
    echo $fromform->id;
    if ($fromform->id && $fromform->string && $fromform->string !== '') {
      $manager->update($fromform->id, $fromform->type, $fromform->string);
    } else if ($fromform->string && $fromform->string !== '' ) {
        $manager->create($fromform->type, $fromform->string);
    }
}

$templatecontext = (object) [
        'courses' => $courses,
        'courseCategoryName' => $category->name,
        'coursesInCat' => $boolCoursesInCat,
        'url' => $url
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_oc_course_creation/courselistview', $templatecontext);
echo "<div class='mr-5'>";
$mform->display();
echo "</div>";
echo $OUTPUT->footer();
