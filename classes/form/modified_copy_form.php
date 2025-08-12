<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * copy form
 *
 * @package    local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ocbsbcoursecreation\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use local_ocbsbcoursecreation\manager;

/**
 * modified_copy_form
 */
class modified_copy_form extends \moodleform {
    /**
     * definition
     */
    public function definition() {
        global $PAGE;

        $manager        = new manager();
        $mform          = $this->_form;

        $courses        = $this->_customdata['courses'];            // Kurse, in denen der/die Nutzer:in Trainer ist.
        $course         = $this->_customdata['course'] ?? null;     // Für Overviewfiles-Optionen.
        $fixedtargetid  = (int)($this->_customdata['fixedtargetid'] ?? 0);
        $lockonload     = !empty($this->_customdata['locktarget_on_load']);

        // Optionales JS (falls später Logik nötig).
        $PAGE->requires->js_call_amd('local_ocbsbcoursecreation/form_control_copy', 'init', []);

        // Zielkurs-Auswahl oder gelockte Anzeige.
        if ($lockonload && $fixedtargetid && isset($courses[$fixedtargetid])) {
            // Anzeige fixiert (aus Navigation übergeben).
            $mform->addElement(
                'static',
                'targetcourseid_label',
                get_string('select_target_course', 'local_ocbsbcoursecreation'),
                format_string($courses[$fixedtargetid])
            );
            $mform->addElement('hidden', 'targetcourseid', $fixedtargetid);
            $mform->setType('targetcourseid', PARAM_INT);
        } else {
            // Normale Auswahl (auch bei POST-Resubmits nach Validation-Fehlern).
            $mform->addElement(
                'select',
                'targetcourseid',
                get_string('select_target_course', 'local_ocbsbcoursecreation'),
                $courses
            );
            $mform->addRule('targetcourseid', get_string('required'), 'required');
            $mform->setType('targetcourseid', PARAM_INT);

            // Falls aus GET ein Vorschlag kam, als Default setzen (nicht locken).
            if ($fixedtargetid && isset($courses[$fixedtargetid])) {
                $mform->setDefault('targetcourseid', $fixedtargetid);
            }
        }

        // Kursbild.
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement(
                'html',
                '<h3 class="qheader">' . get_string('form_copy_header', 'local_ocbsbcoursecreation') . '</h3>'
            );
            $mform->addElement(
                'filemanager',
                'overviewfiles_filemanager',
                get_string('form_copy_image_desc', 'local_ocbsbcoursecreation'),
                null,
                $overviewfilesoptions
            );
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

        // Vollständiger Kursname.
        $mform->addElement(
            'text',
            'fullname',
            get_string('fullnamecourse'),
            'maxlength="254" size="50"'
        );
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required');
        $mform->setType('fullname', PARAM_TEXT);

        // Kurzer Kursname.
        $mform->addElement(
            'text',
            'shortname',
            get_string('shortnamecourse'),
            'maxlength="100" size="20"'
        );
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required');
        $mform->setType('shortname', PARAM_TEXT);

        // Kursbeschreibung.
        $mform->addElement('header', 'descriptionhdr', get_string('description'));
        $mform->setExpanded('descriptionhdr');
        $mform->addElement('editor', 'summary_editor', get_string('coursesummary'), null);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        // Warnhinweis (rot hervorgehoben).
        $mform->addElement(
            'html',
            '<div class="alert alert-danger" role="alert" style="margin-top:12px;">' .
                get_string('confirm_overwrite_note', 'local_ocbsbcoursecreation') .
            '</div>'
        );

        // Pflicht-Checkbox.
        $mform->addElement(
            'advcheckbox',
            'confirmoverwrite',
            '',
            get_string('confirm_overwrite_checkbox', 'local_ocbsbcoursecreation'),
            ['group' => 1],
            [0, 1]
        );
        $mform->setType('confirmoverwrite', PARAM_BOOL);
        // Hinweis: addRule('required') ist bei Checkboxen unzuverlässig -> serverseitige validation().

        // Buttons.
        $this->add_action_buttons(true, get_string('apply_template', 'local_ocbsbcoursecreation'));
    }

    /**
     * validation
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['targetcourseid'])) {
            $errors['targetcourseid'] = get_string('required');
        }
        if (empty($data['confirmoverwrite'])) {
            $errors['confirmoverwrite'] = get_string('confirm_overwrite_required', 'local_ocbsbcoursecreation');
        }

        return $errors;
    }
}
