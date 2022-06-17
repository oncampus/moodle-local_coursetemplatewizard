<?php


/**
 * Course copy form class.
 *
 * @package     local_oc_course_creation
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @modified_by Laurenz Schindler <laurenz.schindler@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oc_course_creation\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use local_oc_course_creation\manager;

class modified_copy_form extends \moodleform {
    /**
     * Build form for the course copy settings.
     *
     * {@inheritDoc}
     * @see \moodleform::definition()
     */
    public function definition() {
        $manager = new manager();
        global $PAGE;
        $PAGE->requires->js_call_amd('local_oc_course_creation/form_control_copy');

        global $CFG, $OUTPUT, $USER;
        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $coursecontext = \context_course::instance($course->id);
        $courseconfig = get_config('moodlecourse');

        if (empty($course->category)) {
            $course->category = $course->categoryid;
        }

        // Course ID.
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        // Keep source course user data.
        $mform->addElement('hidden', 'userdata', 0);
        $mform->setType('userdata', PARAM_INT);


        // Course ID number (default to the current course ID number; blank for users who can't change ID numbers).
        $mform->addElement('hidden', 'idnumber', $course->idnumber);
        $mform->setType('idnumber', PARAM_RAW);

        // Form heading.
        $mform->addElement('html', \html_writer::div(get_string('form:copy:description', 'local_oc_course_creation'), 'form-description mb-3'));

        // Form select image
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('html', '<h3 class="qheader">' . get_string('form:copy:image_header','local_oc_course_creation') . '</h3>');
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('form:copy:image_desc',"local_oc_course_creation"),null,
                    $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }
        //group prefix
        $type_group = array();
        // Form add prefix checkbox
        $mform->addElement('html', '<h3 class="qheader">' . get_string('form:copy:course_name_header','local_oc_course_creation') . '</h3>');
        $mform->addElement('checkbox', 'add_prefix', get_config('local_oc_course_creation', 'prefix_desc'));
        // Form add prefix
        $prefix = $mform->createElement('text', 'prefix', '',
                array('class' => 'mr-2 h-100 modifying_type prefix input_type', 'placeholder' =>
                        get_config('local_oc_course_creation', 'prefix_text')));
        $mform->setType('prefix', PARAM_TEXT);

        $mform->hideIf("prefix", "add_prefix");

        // Form preset values
        $teacher = $mform->createElement('text', 'teacher_name', '',
                array('class' => 'mr-2 h-100 modifying_type input_type', 'placeholder' =>
                        get_string('form:copy:teacher_name_placeholder', 'local_oc_course_creation')));
        $mform->setType('teacher_name', PARAM_TEXT);

        $course_type = $mform->createElement('text', 'course_type', '',
                array('class' => 'mr-2 h-100 modifying_type input_type', 'placeholder' =>
                        get_string('form:copy:teacher_course_type_placeholder', 'local_oc_course_creation')));
        $mform->setType('course_type', PARAM_TEXT);

        $type_group[] = $prefix;
        $type_group[] = $teacher;
        $type_group[] = $course_type;

        $types_key_value = array();
        $type_names = "";
        $types = $manager->get_all_types();
        $values = $manager->get_all_values();
        foreach ($types as $type) {
            foreach ($values as $value) {
                if ($value->type_id === $type->id) {
                    $types_key_value[$type->id][] = $value->string;
                }
            }
            $type_group[] = $mform->createElement('select', 'type_' . $type->type,
                    "", $types_key_value[$type->id]
                    , ['class' => 'modifying_type select_type']);
            $type_names .= $type->type . "<br>";
        }



        $mform->addGroup($type_group, "",
                get_string('form:copy:course_details', 'local_oc_course_creation'), ' ');

        // Course fullname.
        $mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);

        // Course shortname.
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);

        // Course category.
        $displaylist = \core_course_category::make_categories_list('local/oc_course_creation:course_cat_copy_cap');
        if (!isset($displaylist[$course->category])) {
            // Always keep current category.
            $displaylist[$course->category] = \core_course_category::get($course->category, MUST_EXIST, true)->get_formatted_name();
        }
        $displaylist['default']= get_string('form:copy:select_default','local_oc_course_creation');

        $select=$mform->addElement('select', 'category', get_string('coursecategory'), $displaylist );
        $mform->addRule('category',  get_string('invalidcategory', 'error'), 'numeric', null, 'client');
        $mform->addHelpButton('category', 'coursecategory');
        $select->setSelected('default');
        // Course visibility.
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('coursevisibility'), $choices);
        $mform->addHelpButton('visible', 'coursevisibility');
        $mform->setDefault('visible', $courseconfig->visible);
        if (!has_capability('moodle/course:visibility', $coursecontext)) {
            $mform->hardFreeze('visible');
            $mform->setConstant('visible', $course->visible);
        }

        if (!empty($CFG->enablecourserelativedates)) {
            $attributes = [
                    'aria-describedby' => 'relativedatesmode_warning'
            ];
            if (!empty($course->id)) {
                $attributes['disabled'] = true;
            }
            $relativeoptions = [
                    0 => get_string('no'),
                    1 => get_string('yes'),
            ];
            $relativedatesmodegroup = [];
            $relativedatesmodegroup[] = $mform->createElement('select', 'relativedatesmode', get_string('relativedatesmode'),
                    $relativeoptions, $attributes);
            $relativedatesmodegroup[] = $mform->createElement('html', \html_writer::span(get_string('relativedatesmode_warning'),
                    '', ['id' => 'relativedatesmode_warning']));
            $mform->addGroup($relativedatesmodegroup, 'relativedatesmodegroup', get_string('relativedatesmode'), null, false);
            $mform->addHelpButton('relativedatesmodegroup', 'relativedatesmode');
        }

        // Description.
        $mform->addElement('header', 'descriptionhdr', get_string('description'));
        $mform->setExpanded('descriptionhdr');

        $mform->addElement('editor', 'summary_editor', get_string('coursesummary'), null);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        if (!empty($course->id) and !has_capability('moodle/course:changesummary', $coursecontext)) {
            // Remove the description header it does not contain anything any more.
            $mform->removeElement('descriptionhdr');
            $mform->hardFreeze($summaryfields);
        }

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitreturn', get_string('savechangesandreturn'));
        $buttonarray[] = $mform->createElement('submit', 'submitdisplay', get_string('savechangesanddisplay'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

    /**
     * Validation of the form.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        $courseshortname = $DB->get_record('course', array('shortname' => $data['shortname']), 'fullname', IGNORE_MULTIPLE);
        if ($courseshortname) {
            $errors['shortname'] = get_string('shortnametaken', '', $courseshortname->fullname);
        }

        // Add field validation check for duplicate idnumber.
        if (!empty($data['idnumber'])) {
            $courseidnumber = $DB->get_record('course', array('idnumber' => $data['idnumber']), 'fullname', IGNORE_MULTIPLE);
            if ($courseidnumber) {
                $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $courseidnumber->fullname);
            }
        }

        return $errors;
    }

}
