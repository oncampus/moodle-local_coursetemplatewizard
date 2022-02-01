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

$url = new moodle_url('/local/oc_course_creation/list_preset_values.php');

$PAGE->set_url($url);
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('local_oc_course_creation/delete_preset_value');

$manager = new manager();

//get's posted form data and creates new entry in DB
if ($_POST && $_POST['text'] && !array_key_exists('id', $_POST)) {
    $string = $_POST['text'];
    if (!$_POST['type_id']) {
        $manager->create_value_and_type($string, $_POST['type']);
    } else {
        $manager->create_value($string,$_POST['type_id']);
    }
    redirect($url, null, 1);
}

$types = $manager->get_all_types();
$values = $manager->get_all_values();
$entries = array();
//filles each type in one array -> display as one table
$i = 0;
foreach ($types as $type) {
    $entries[$i]['type'] = $type->type;
    $entries[$i]['rank'] = $type->rank;
    $entries[$i]['type_id'] = $type->id;
    foreach ($values as $value) {
        if ($value->type_id === $type->id) {
            $entries[$i]['values'][] = $value;
        }
    }
    $i++;
}
$templatecontext = (object) [
        'entries' => $entries,
        'editURL' => new moodle_url('/local/oc_course_creation/edit_preset_value.php'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_oc_course_creation/values_list_view', $templatecontext);
echo $OUTPUT->footer();

