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

namespace local_occoursecreation\form;

use moodleform;
use local_occoursecreation\manager;

require_once("$CFG->libdir/formslib.php");

class stringForm extends moodleform {
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $manager = new manager();
        $choices = array();
        foreach ($manager->getAll() as $record) {
            $choices[$record->id] = $record->string;
        }
        $options = array(
                'tags' => true,
                'noselectionstring' => get_string('allareas', 'search'),
                'showsuggestions' => true,
                'showstandard' => true,
                'class' => 'notation-form-edit',
        );
        $autocomplete = $mform->createElement('autocomplete', 'string', get_string('searcharea', 'search'), $choices, $options);
        $mform->addElement($autocomplete);
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'type', '', 'prefix', 0);
        $radioarray[] = $mform->createElement('radio', 'type', '', 'postfix', 1);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);

        $this->add_action_buttons();
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}