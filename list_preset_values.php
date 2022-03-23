<?php

/**
 * list preset values and also create new or delete them
 *
 * @package     local_oc_course_creation
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */
global $CFG, $DB;
require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

use local_oc_course_creation\manager;

//secure
redirect_if_major_upgrade_required();
require_login();
$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
$context = \context_system::instance();
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}
require_capability('local/oc_course_creation:handle_presets', $context);

$url = new moodle_url('/local/oc_course_creation/list_preset_values.php');
$action = optional_param('action', '', PARAM_ALPHA);
$rank = optional_param('rank', '', PARAM_INT);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('local_oc_course_creation/delete_preset_value');
$PAGE->set_heading( get_site()->fullname);

$manager = new manager();

if($action && $rank){
    switch ($action){
        case "moveup" :
        $manager->swap($rank, $rank - 1);
            redirect($url, null, 1);
        case "movedown" :
            $manager->swap($rank, $rank + 1);
            redirect($url, null, 0);
    }
}

//get's posted form data and creates new entry in DB
$string = optional_param('text',null,PARAM_TEXT);
$type = optional_param('type',null,PARAM_TEXT);
$id = optional_param('id',null,PARAM_INT);
$type_id = optional_param('type_id',null,PARAM_INT);
if ($string && !$id) {
    if (!$type_id) {
        $manager->create_value_and_type($string, $type);
    } else {
        $manager->create_value($string, $type_id);
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

// filles the entries with type and value
foreach ($types as $type) {
    $entries[$i]['type'] = $type->type;
    $entries[$i]['rank'] = $type->rank;
    $entries[$i]['btns'] =  get_spacer();
    //adds sorting arrows
    if ($type != $first_type) {
        $entries[$i]['btns'] .= get_action_icon($url . '?action=moveup&amp;rank=' . $type->rank, 'up', $strmoveup, $strmoveup);
    } else {
        $entries[$i]['btns'] .= get_spacer();
    }
    if ($type != $last_type) {
        $entries[$i]['btns'] .= get_action_icon($url . '?action=movedown&amp;rank=' . $type->rank, 'down', $strmovedown,
                $strmovedown);
    } else {
        $entries[$i]['btns'] .= get_spacer();
    }
    $entries[$i]['type_id'] = $type->id;
    //adds values
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

//die after printing footer
die;

//creates html for icon and link
function get_action_icon($url, $icon, $alt, $tooltip) {
    global $OUTPUT;
    return '<a title="' . $tooltip . '" href="' . $url . '">' .
            $OUTPUT->pix_icon('t/' . $icon, $alt) . '</a> ';
}
//spacer to align icons
function get_spacer() {
    global $OUTPUT;
    return $OUTPUT->spacer();
}