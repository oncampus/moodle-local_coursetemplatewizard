<?php

/**
 * Course copy form class.
 *
 * @package     local_oc_course_creation
 * @copyright   2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author      Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oc_course_creation\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Course copy form class.
 *
 * @package     core_backup
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
        $PAGE->requires->js_call_amd('local_oc_course_creation/form_control_copy', null, []);

        global $CFG, $OUTPUT, $USER;
        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $coursecontext = \context_course::instance($course->id);
        $courseconfig = get_config('moodlecourse');
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];
        $course = $this->_customdata['course']; // this contains the data of this form

        if (empty($course->category)) {
            $course->category = $course->categoryid;
        }

        // Course ID.
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'id', $course->id);
        $mform->setType('id', PARAM_INT);

        // Keep source course user data.
        $mform->addElement('hidden', 'userdata', 0);
        $mform->setType('userdata', PARAM_INT);

        // Return to type.
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        // Course ID number (default to the current course ID number; blank for users who can't change ID numbers).
        $mform->addElement('hidden', 'idnumber', $course->idnumber);
        $mform->setType('idnumber', PARAM_RAW);

        // Notifications of current copies.
        $copies = \core_backup\copy\copy::get_copies($USER->id, $course->id);
        if (!empty($copies)) {
            $progresslink = new \moodle_url('/backup/copyprogress.php?', array('id' => $course->id));
            $notificationmsg = get_string('copiesinprogress', 'backup', $progresslink->out());
            $notification = $OUTPUT->notification($notificationmsg, 'notifymessage');
            $mform->addElement('html', $notification);
        }

        // Return to URL.
        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);

        // Form heading.
        $mform->addElement('html', \html_writer::div(get_string('copycoursedesc', 'backup'), 'form-description mb-3'));

        // Form select image
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null,
                    $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

        // Form preset values
        $types_key_value = array();
        $type_group = array();
        $teacher = $mform->createElement('text', 'teacher_name', '',
                array('class' => 'mr-2 h-100 modifying_type', 'placeholder' =>
                        get_string('teacher_name_placeholder', 'local_oc_course_creation')));
        $course_type = $mform->createElement('text', 'course_type', '',
                array('class' => 'mr-2 h-100 modifying_type', 'placeholder' =>
                        get_string('teacher_course_type_placeholder', 'local_oc_course_creation')));
        $teacher->setType('teacher_name', PARAM_TEXT);
        $course_type->setType('course_type', PARAM_TEXT);
        foreach ($manager->get_all() as $preset) {
            $types_key_value[$preset->type][] = $preset->string;
        }
        $type_group[] = $teacher;
        $type_group[] = $course_type;
        $types = $manager->get_diff_types_string();
        for ($i = count($types) - 1; $i >= 0; $i--) {
            $type_group[] = $mform->createElement('select', 'type_' . $types[$i], "", $types_key_value[$types[$i]]
                    , ['class' => 'modifying_type select_type']);
        }

        $mform->addGroup($type_group, '', '', ' ', false);
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
        $displaylist = \core_course_category::make_categories_list(\core_course\management\helper::get_course_copy_capabilities());
        if (!isset($displaylist[$course->category])) {
            // Always keep current category.
            $displaylist[$course->category] = \core_course_category::get($course->category, MUST_EXIST, true)->get_formatted_name();
        }
        $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $displaylist);
        $mform->addRule('category', null, 'required', null, 'client');
        $mform->addHelpButton('category', 'coursecategory');

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

        $requiredcapabilities = array(
                'moodle/restore:createuser', 'moodle/backup:userinfo', 'moodle/restore:userinfo'
        );
        if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
            $mform->hardFreeze('userdata');
            $mform->setConstant('userdata', 0);
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

        $role = new \stdClass();
        $role->id  = 2;
        $role->roleid = 2;
        $role->contextid = $coursecontext;
        $role->localname  = 'None';
        $role->userid = $USER->id;
        $role->component = '';
        $role->itemid = 0;
        $role->timemodified = time();
        $roles[] = $role;
        // Only add the option if there are roles in this course.
        if (!empty($roles) && has_capability('moodle/restore:createuser', $coursecontext)) {
            $rolearray = array();
            foreach ($roles as $role) {
                $roleid = 'role_' . $role->id;
                $rolearray[] = $mform->createElement('advcheckbox', $roleid,
                        $role->localname, '', array('group' => 2), array(0, $role->id));
            }

            $mform->addGroup($rolearray, 'rolearray', get_string('keptroles', 'backup'), ' ', false);
            $mform->addHelpButton('rolearray', 'keptroles', 'backup');
            $this->add_checkbox_controller(2);
        }


        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitreturn', get_string('copyreturn', 'backup'));
        $buttonarray[] = $mform->createElement('submit', 'submitdisplay', get_string('copyview', 'backup'));
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

        // Validate the dates (make sure end isn't greater than start).
        if ($errorcode = course_validate_dates($data)) {
            $errors['enddate'] = get_string($errorcode, 'error');
        }

        return $errors;
    }

}
