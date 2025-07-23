<?php

/**
 * Course copy form class.
 *
 * @package     local_ocbsbcoursecreation
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @modified_by Laurenz Schindler <laurenz.schindler@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $CFG
 */

namespace local_ocbsbcoursecreation\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use core_reportbuilder\local\aggregation\count;
use local_ocbsbcoursecreation\manager;

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
        $displayseperator = get_config('local_ocbsbcoursecreation', 'display_seperator');
        $charnumber = get_config('local_ocbsbcoursecreation', 'cshortname_charnumber');
        $async = get_config('local_ocbsbcoursecreation', 'async_process');
        $charnumber = (int)$charnumber ? $charnumber : 3;
        $params = ['seperator' => $displayseperator, 'charnumber' => $charnumber];
        $PAGE->requires->js_call_amd('local_ocbsbcoursecreation/form_control_copy', 'init', [$params]);

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
        $mform->addElement('html',
            \html_writer::div(get_string('form:copy:description', 'local_ocbsbcoursecreation'),
                'form-description mb-3'));

        // Form select image
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('html',
                '<h3 class="qheader">' . get_string('form:copy:image_header', 'local_ocbsbcoursecreation') . '</h3>');
            $mform->addElement('filemanager', 'overviewfiles_filemanager',
                get_string('form:copy:image_desc', "local_ocbsbcoursecreation"), null,
                $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }
        if (get_config('local_ocbsbcoursecreation', 'use_default_course_naming')) {

            $types = $manager->get_all_types();
            $values = $manager->get_all_values();
            $textfield_count = 0;
            $infix = explode('\'', get_config('local_ocbsbcoursecreation', 'textfield_seperator'));
            $infix = array_combine($infix, $infix);
            $infixcounter = 0;
            //group prefix
            if (get_config('local_ocbsbcoursecreation', 'toggle_prefix')) {
                $type_group = [];

                // Form add prefix checkbox
                $mform->addElement('html',
                    '<h3 class="qheader">' . get_string('form:copy:course_name_header', 'local_ocbsbcoursecreation') . '</h3>');
                $mform->addElement('checkbox', 'add_prefix',
                    get_string('form:copy:prefix_desc', 'local_ocbsbcoursecreation'));
                // Form add prefix
                $prefix = $mform->createElement('text', 'prefix', '',
                    ['class' => 'mr-2 h-100 modifying_type prefix input_type', 'placeholder' =>
                        get_config('local_ocbsbcoursecreation', 'prefix_text'),
                    ]);
                $mform->setType('prefix', PARAM_TEXT);

                $mform->hideIf("prefix", "add_prefix");
                $mform->hideIf("infix0", "add_prefix");
                $type_group[] = $prefix;
                $type_group[] = $mform->createElement('select', 'infix' . $infixcounter++, "", $infix
                    , ['class' => $displayseperator ? 'modifying_type select_type prefix' : 'd-none']);
            }

            // Form preset values
            $textfieldvalues = explode(',', get_config('local_ocbsbcoursecreation', 'textfield_values'));
            foreach ($textfieldvalues as $key => $textfield) {
                $textfieldisntance = $mform->createElement('text', 'textfield' . $textfield_count, '',
                    ['class' => 'mr-2 h-100 modifying_type input_type',
                        'placeholder' => $textfield,
                    ]);
                $mform->setType('textfield' . $textfield_count++, PARAM_TEXT);
                $type_group[] = $textfieldisntance;
                if (count($types) > 0 || $key !== array_key_last($textfieldvalues)) {
                    $type_group[] = $mform->createElement('select', 'infix' . $infixcounter++, "", $infix
                        , ['class' => $displayseperator ? 'modifying_type select_type' : 'd-none']);
                }
            }

            $types_key_value = [];
            foreach ($types as $key => $type) {
                foreach ($values as $value) {
                    if ($value->type_id === $type->id) {
                        $types_key_value[$type->id][$value->string] = $value->string;
                    }
                }
                $type_group[] = $mform->createElement('select', 'type_' . $type->type,
                    "", $types_key_value[$type->id]
                    , ['class' => 'modifying_type select_type']);
                if ($key !== array_key_last($types)) {
                    $type_group[] = $mform->createElement('select', 'infix' . $infixcounter++, "", $infix
                        , ['class' => $displayseperator ? 'modifying_type select_type' : 'd-none']);
                }
            }

            $mform->addGroup($type_group, "",
                get_string('form:copy:course_details', 'local_ocbsbcoursecreation'), ' ');
        }
        // Course fullname.
        $shortnamereadonly = get_config('local_ocbsbcoursecreation', 'course_shortname_readonly') ? 'readonly' : '';
        $coursenamereadonly = get_config('local_ocbsbcoursecreation', 'course_name_readonly') ? 'readonly' : '';
        $mform->addElement('text', 'fullname', get_string('fullnamecourse'),
            'maxlength="254" size="50" ' . $shortnamereadonly);
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);

        // Course shortname.
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'),
            'maxlength="100" size="20" ' . $coursenamereadonly);
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);

        // Course category.
        $displaylist = \core_course_category::make_categories_list('local/ocbsbcoursecreation:course_cat_copy_cap');
        if (!isset($displaylist[$course->category])) {
            // Always keep current category.
            $displaylist[$course->category] = \core_course_category::get($course->category, MUST_EXIST, true)->get_formatted_name();
        }

        // We never want to create new courses in the course creation category, so remove it from the list
        $coursecreationcategory = get_config('local_ocbsbcoursecreation', 'category');
        if (($courseid = array_search($coursecreationcategory, $displaylist)) !== false) {
            unset($displaylist[$courseid]);
        }

        $displaylist['default'] = get_string('form:copy:select_default', 'local_ocbsbcoursecreation');

        $select = $mform->addElement('select', 'category', get_string('coursecategory'), $displaylist);
        $mform->addRule('category', get_string('invalidcategory', 'error'), 'numeric', null, 'client');
        $mform->addHelpButton('category', 'coursecategory');
        $select->setSelected('default');
        // Course visibility.
        $choices = [];
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
                'aria-describedby' => 'relativedatesmode_warning',
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
        if(!$async) {
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
        }
        $buttonarray = [];
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
        $courseshortname = $DB->get_record('course', ['shortname' => $data['shortname']], 'fullname', IGNORE_MULTIPLE);
        if ($courseshortname) {
            $errors['shortname'] = get_string('shortnametaken', '', $courseshortname->fullname);
        }

        // Add field validation check for duplicate idnumber.
        if (!empty($data['idnumber'])) {
            $courseidnumber = $DB->get_record('course', ['idnumber' => $data['idnumber']], 'fullname', IGNORE_MULTIPLE);
            if ($courseidnumber) {
                $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $courseidnumber->fullname);
            }
        }

        return $errors;
    }

}
