<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * form for presets
 *
 * @package    local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ocbsbcoursecreation\form;
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

use local_ocbsbcoursecreation\manager;

/**
 * string_form
 */
class string_form extends \moodleform {
    /**
     * definition()
     */
    public function definition() {

        $manager         = new manager();
        $types           = $manager->get_all_types();
        $typeskeyvalue = [];
        foreach ($types as $type) {
            $typeskeyvalue[$type->id] = $type->type;
            $typeskeyvalue[$type->id] = $type->type;
        }

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', 'type_id', get_string('forumtype', 'forum'), $typeskeyvalue, []);

        $mform->addElement('text', 'string', get_string('form:string:form_select', 'local_ocbsbcoursecreation'));
        $mform->setType('string', PARAM_TEXT);

        $this->add_action_buttons();
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

        return $errors;
    }
}
