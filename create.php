<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
/**
 * @package     local_occoursecreation
 * @category    manager
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $CFG, $DB;
require_once($CFG->dirroot . '/course/classes/category.php');

use local_occoursecreation\form\stringForm;
use local_occoursecreation\manager;

$PAGE->set_url(new moodle_url('/local/occoursecreation/create.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_occoursecreation'));

$mform = new stringForm();
$manager = new manager();

$setCourseCategory = get_config('local_occoursecreation', 'category');
$categories = core_course_category::get_all(array('returnhidden' => true));
$category = null;

foreach ($categories as $item) {
    if ($item->name == $setCourseCategory) {
        $category = $item;
    }
}

$courseIds = $category->get_courses(array('idonly' => true));

$context = context_coursecat::instance($category->id);
$PAGE->requires->js_call_amd('local_occoursecreation/create_course_copy_modal', 'init', array($context->id));
$PAGE->requires->js_call_amd('local_occoursecreation/formControl');

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

echo $OUTPUT->render_from_template('local_occoursecreation/courselistview', $templatecontext);

echo "<br>";

$mform->display();

echo $OUTPUT->footer();
