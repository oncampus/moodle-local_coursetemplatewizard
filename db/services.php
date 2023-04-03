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
 * Defines the api for deleting an preset value
 *
 * @package    local_oc_course_creation
 * @auther     schindlerl
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$functions = [
        'local_oc_course_creation_custom_preset_delete' => [
            //web service name (unique in all Moodle)
            'classname'    => 'local_oc_course_creation_external',
            //class containing the function implementation
            'methodname'   => 'custom_preset_delete',
            //name of the function into the class
            'classpath'    => 'local/oc_course_creation/externallib.php',
            //file containing the class (only used for core external function, not needed if your file is 'component/externallib.php'),
            'description'  => 'Delete selected preset string by its id',
            'component'    => 'local_oc_course_creation',
            'capabilities' => 'local/oc_course_creation:handle_presets',
            'type'         => 'write',
            'ajax'         => 'true',
            'capabilities' => 'true',
        ],
];