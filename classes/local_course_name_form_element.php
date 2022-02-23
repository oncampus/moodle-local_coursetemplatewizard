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
 * Local course name form element
 *
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @package   oc_course_creation
 * @copyright 2022 Laurenz Schindler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;


require_once($CFG->libdir . '/form/select.php');

class local_course_name_form_element extends \MoodleQuickForm_select {

    /**
     * Constructor
     *
     * @param string $elementName Element name
     * @param mixed $elementLabel Label(s) for an element
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array.
     */
    public function __construct($elementName=null, $elementLabel=null, $options=array(), $attributes=null) {
        if ($elementName == null) {
            // This is broken quickforms messing with the constructors.
            return;
        }

        $validoptions =null;
        parent::__construct($elementName, $elementLabel, $validoptions, $attributes);
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


