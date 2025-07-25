<?php
/**
 * List courses to copy (Kursvorlagenübersicht)
 *
 * @package     local_ocbsbcoursecreation
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */

require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

use local_ocbsbcoursecreation\manager;
use core_course\external\course_summary_exporter;

require_login();

// Check: Nur Trainer und Kursersteller dürfen zugreifen.
$systemcontext = context_system::instance();
if (!has_capability('moodle/course:create', $systemcontext) && !has_capability('moodle/course:update', $systemcontext)) {
    throw new required_capability_exception($systemcontext, 'moodle/course:create', 'nopermissions', '');
}

// Aus Plugin-Settings: Kategorie für Vorlagen laden.
$setCourseCategory = get_config('local_ocbsbcoursecreation', 'category');
$categories        = core_course_category::get_all(['returnhidden' => true]);
$category          = null;

foreach ($categories as $item) {
    if ($item->name === $setCourseCategory) {
        $category = $item;
        break;
    }
}

if (is_null($category)) {
    redirect(new moodle_url('/admin/search.php'), 'Selected category missing.', 1);
}

// Seiteneinstellungen.
$PAGE->set_url(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php'));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_ocbsbcoursecreation'));
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_pagelayout('standard');

$manager = new manager();

// Alle Vorlagenkurse in der definierten Kategorie.
$courseIds = $category->get_courses(['idonly' => true]);
$courses   = [];
$i         = 0;

foreach ($courseIds as $courseId) {
    $course = $DB->get_record('course', ['id' => $courseId]);
    $course->fullname = format_text($course->fullname);

    // Bild
    $img = html_writer::img(
        course_summary_exporter::get_course_image($course),
        "",
        ["width" => "100%", 'style' => "max-width: 350px;"]
    );

    // Zusammenfassung
    $summary = format_text($manager->get_course_summary((int)$courseId));
    $offset  = 500;
    $end     = '</p>';
    if (strlen($summary) > $offset && strpos($summary, $end, $offset)) {
        $result = substr($summary, 0, strlen($end) + (strpos($summary, $end, $offset)));
    } else {
        $result = $summary;
    }

    $courses[$i] = (object)[
        'id'          => $courseId,
        'fullname'    => $course->fullname,
        'img'         => $img,
        'desc'        => $result,
        'course_url'  => new moodle_url('/course/view.php', ['id' => $courseId]),
        'copy_url'    => new moodle_url('/local/ocbsbcoursecreation/handle_copy_form.php', ['id' => $courseId])
    ];

    $i++;
}

$boolCoursesInCat = $category->has_courses();

$templatecontext = (object)[
    'courses'            => $courses,
    'courseCategoryName' => $category->name,
    'coursesInCat'       => $boolCoursesInCat,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_ocbsbcoursecreation/course_list_view', $templatecontext);
echo $OUTPUT->footer();
