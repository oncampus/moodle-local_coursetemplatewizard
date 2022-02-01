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
$action = optional_param('action', '', PARAM_ALPHA);
$type_id = optional_param('type_id', '', PARAM_ALPHA);
$PAGE->set_url($url);
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('local_oc_course_creation/delete_preset_value');

$manager = new manager();

if($action && $type_id){
    switch ($action){
        case "moveup" :
        $manager->
            redirect($url, null, 1);
        case "movedown" :

            redirect($url, null, 1);
    }
}

//get's posted form data and creates new entry in DB
if ($_POST && array_key_exists('text', $_POST) && !array_key_exists('id', $_POST)) {
    $string = $_POST['text'];
    if (!array_key_exists('type_id', $_POST)) {
        $manager->create_value_and_type($string, $_POST['type']);
    } else {
        $manager->create_value($string, $_POST['type_id']);
    }
    redirect($url, null, 1);
}

$types = $manager->get_all_types();
$values = $manager->get_all_values();

$strmoveup = get_string('moveup');
$strmovedown = get_string('movedown');
$first_type = reset($types);
$last_type = end($types);

$entries = array();
//filles each type in one array -> display as one table
$i = 0;

foreach ($types as $type) {
    $entries[$i]['type'] = $type->type;
    $entries[$i]['rank'] = $type->rank;
    $entries[$i]['btns'] =  get_spacer();
    if ($type != $first_type) {
        $entries[$i]['btns'] .= get_action_icon($url . '?action=moveup&amp;type_id=' . $type->rank, 'up', $strmoveup, $strmoveup);
    } else {
        $entries[$i]['btns'] .= get_spacer();
    }
    if ($type != $last_type) {
        $entries[$i]['btns'] .= get_action_icon($url . '?action=movedown&amp;type_id=' . $type->rank, 'down', $strmovedown,
                $strmovedown);
    } else {
        $entries[$i]['btns'] .= get_spacer();
    }
    $entries[$i]['type_id'] = $type->id;
    foreach ($values as $value) {
        if ($value->type_id === $type->id) {
            $entries[$i]['values'][] = $value;
        }
    }
    $i++;
}
//returns values to page
$templatecontext = (object) [
        'entries' => $entries,
        'editURL' => new moodle_url('/local/oc_course_creation/edit_preset_value.php'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_oc_course_creation/values_list_view', $templatecontext);
echo $OUTPUT->footer();

die;

function get_action_icon($url, $icon, $alt, $tooltip) {
    global $OUTPUT;
    return '<a title="' . $tooltip . '" href="' . $url . '">' .
            $OUTPUT->pix_icon('t/' . $icon, $alt) . '</a> ';
}

function get_spacer() {
    global $OUTPUT;
    return $OUTPUT->spacer();
}