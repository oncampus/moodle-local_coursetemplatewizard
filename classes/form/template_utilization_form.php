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
 * @package    local_coursetemplatewizard
 * @copyright   2025 Oncampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursetemplatewizard\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use moodle_exception;

/**
 * modified_copy_form
 */
class template_utilization_form extends \moodleform {
    /**
     * definition
     */
    public function definition() {
        $mform = $this->_form;
        $templatecourse = $this->_customdata['templatecourse'];
        $targetcourse = $this->_customdata['targetcourse'];
        if (!$templatecourse) {
            throw new moodle_exception(
                'error',
                'local_coursetemplatewizard',
                '',
                null,
                'Target course not found.'
            );
        }
        if (!$targetcourse) {
            throw new moodle_exception(
                'error',
                'local_coursetemplatewizard',
                '',
                null,
                'Target course not found.'
            );
        }
        $mform->addElement(
            'static',
            'targetcourseid_label',
            get_string('select_target_course', 'local_coursetemplatewizard'),
            format_string($targetcourse->fullname)
        );
        $mform->addElement('hidden', 'targetcourseid', $targetcourse->id);
        $mform->setType('targetcourseid', PARAM_INT);

        // Kursbild.
        $summaryfields = 'summary_editor';
        if ($overviewfilesoptions = course_overviewfiles_options($templatecourse)) {
            $mform->addElement(
                'html',
                '<h3 class="qheader">' . get_string('form_copy_header', 'local_coursetemplatewizard') . '</h3>'
            );
            $mform->addElement(
                'filemanager',
                'overviewfiles_filemanager',
                get_string('form_copy_image_desc', 'local_coursetemplatewizard'),
                null,
                $overviewfilesoptions
            );
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

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
                get_string('confirm_overwrite_note', 'local_coursetemplatewizard') .
            '</div>'
        );

        // Pflicht-Checkbox.
        $mform->addElement(
            'advcheckbox',
            'confirmoverwrite',
            '',
            get_string('confirm_overwrite_checkbox', 'local_coursetemplatewizard'),
            ['group' => 1],
            [0, 1]
        );
        $mform->setType('confirmoverwrite', PARAM_BOOL);
        // Hinweis: addRule('required') ist bei Checkboxen unzuverlässig -> serverseitige validation().

        // Buttons.
        $this->add_action_buttons(true, get_string('apply_template', 'local_coursetemplatewizard'));
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
            $errors['confirmoverwrite'] = get_string('confirm_overwrite_required', 'local_coursetemplatewizard');
        }

        return $errors;
    }
}
