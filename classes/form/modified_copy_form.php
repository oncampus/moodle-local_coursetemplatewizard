<?php
namespace local_ocbsbcoursecreation\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use local_ocbsbcoursecreation\manager;

class modified_copy_form extends \moodleform {

    public function definition() {
        global $CFG, $PAGE, $USER;

        $manager = new manager();
        $mform = $this->_form;
        $courses = $this->_customdata['courses']; // Trainerkurse
        $course = $this->_customdata['course'] ?? null;
        $courseconfig = get_config('moodlecourse');

        // JS (falls benötigt)
        $PAGE->requires->js_call_amd('local_ocbsbcoursecreation/form_control_copy', 'init', []);

        // Auswahl Zielkurs (ersetzt Kategorie-Auswahl)
        $mform->addElement(
            'select',
            'targetcourseid',
            get_string('select_target_course', 'local_ocbsbcoursecreation'),
            $courses
        );
        $mform->addRule('targetcourseid', get_string('required'), 'required');

        // Kursbild (wie bisher)
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('html', '<h3 class="qheader">' .
                get_string('form:copy:image_header', 'local_ocbsbcoursecreation') . '</h3>');
            $mform->addElement('filemanager', 'overviewfiles_filemanager',
                get_string('form:copy:image_desc', "local_ocbsbcoursecreation"), null,
                $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

        // Vollständiger Kursname
        $mform->addElement('text', 'fullname', get_string('fullnamecourse'),
            'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required');
        $mform->setType('fullname', PARAM_TEXT);

        // Kurzer Kursname
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'),
            'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required');
        $mform->setType('shortname', PARAM_TEXT);

        // Kursbeschreibung
        $mform->addElement('header', 'descriptionhdr', get_string('description'));
        $mform->setExpanded('descriptionhdr');

        $mform->addElement('editor', 'summary_editor', get_string('coursesummary'), null);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        // Buttons
        /*$buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'apply', get_string('apply_template', 'local_ocbsbcoursecreation'),
            ['class' => 'btn btn-primary js-confirm-submit']);
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'actionbuttons', '', [' '], false);*/
        $this->add_action_buttons(true, get_string('apply_template', 'local_ocbsbcoursecreation'));

    }
}
