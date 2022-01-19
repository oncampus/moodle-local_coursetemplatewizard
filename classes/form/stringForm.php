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
 * @package    local_occoursecreation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//moodleform is defined in formslib.php

namespace local_oc_course_creation\form;

use moodleform;
use local_occoursecreation\manager;

require_once("$CFG->libdir/formslib.php");

class string_form extends moodleform {
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $manager = new manager();
        $choices[-1] = ""; // empty choice to add new
        foreach ($manager->getAll() as $record) {
            $choices[$record->id . '_' .$record->type] = $record->string;
        }
        $mform->addElement('select', 'dropSelect', get_string('form_choose', 'local_occoursecreation'),$choices, array('onchange' => 'javascript:selectChanged();'));
        $mform->addElement('text', 'string', get_string('form_select', 'local_occoursecreation'));
        $mform->setType('string', PARAM_TEXT);
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'type', '', get_string('form_type_course_name', 'local_occoursecreation'), 0);
        $radioarray[] = $mform->createElement('radio', 'type', '', get_string('form_type_course_semester', 'local_occoursecreation'), 1);
        $radioarray[] = $mform->createElement('radio', 'type', '', get_string('form_type_course_year', 'local_occoursecreation'), 2);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);


        $this->add_action_buttons();
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}