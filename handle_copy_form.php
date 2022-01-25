<?php

require('../../config.php');

use local_oc_course_creation\form\modified_copy_form;
use local_oc_course_creation\manager;

global $CFG, $DB;

$id = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/oc_course_creation/handle_copy_form.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));


$manager = new manager();

$course = $DB->get_record('course', ['id' => $id]);



if ($returnto == 'catmanage') {
    // Redirect to category management page.
    $returnurl = new moodle_url('/local/oc_course_creation/list_courses_to_copy.php', array('categoryid' => $course->category));
} else {
    // Redirect back to course page if we came from there.
    $returnurl = new moodle_url('/course/view.php', array('id' => $id));
}

$mform = new modified_copy_form(
        null,
        array('course' => $course,
                'id' => $id,
                'returnto' => new moodle_url('/local/oc_course_creation/create.php'),
                'returnurl' => new moodle_url('/local/oc_course_creation/create.php'),
                'post',
                '',
                [],
                true
            )
        );

if ($mform->is_cancelled()) {
    //nothing happens
} else if ($fromform = $mform->get_data()) {
    //insert the data in the db
    echo $fromform->id;
    if ($fromform->id && $fromform->string && $fromform->string !== '') {
        echo $fromform->id;
    } else if ($fromform->string && $fromform->string !== '' ) {
        echo $fromform->id;
    }
}

$mform->set_data($course);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
