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

$PAGE->set_url(new moodle_url('/local/oc_course_creation/list_preset_values.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('local_oc_course_creation/delete_preset_value');

$manager = new manager();
$mform = new string_form();

$url = new moodle_url('/local/oc_course_creation/list_preset_values.php');

if ($_POST && $_POST['text']) {
    $type = $_POST['type'];
    $string = $_POST['text'];
    $manager->create($type, $string);
    redirect($url, null, 1);
} else {
    //nothing happens
}
$types = array();
$entries = $manager->get_all();
foreach ($entries as $entry) {
    if(!in_array($entry->type,array_column($types,'type'))){
        $types[] = ['type'=>$entry->type, 'entries'=>array($entry)];
    } else{
        $key = array_search($entry->type,array_column($types,'type'));
        $types[$key]['entries'][] = $entry;
    }
}

$templatecontext = (object) [
        'types' => $types
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_oc_course_creation/values_list_view', $templatecontext);

echo $OUTPUT->footer();

