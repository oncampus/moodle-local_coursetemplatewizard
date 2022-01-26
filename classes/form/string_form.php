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
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//moodleform is defined in formslib.php

namespace local_oc_course_creation\form;
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
use local_oc_course_creation\manager;
class string_form extends  \moodleform {

    //Add elements to form
    public function definition() {

        $manager = new manager();
        $types = $manager->get_diff_types_string();
        $types_key_value = array();
        foreach ($types as $type){
            $types_key_value[$type] = $type;
        }


        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        $mform->addElement('select', 'type', get_string('forumtype', 'forum'), $types_key_value, []);


        $mform->addElement('text', 'string', get_string('form_select', 'local_oc_course_creation'));
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