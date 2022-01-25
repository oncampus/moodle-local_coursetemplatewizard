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
 * local_oc_course_creation services
 *
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$functions = array(
        'local_occoursecreation_custom_preset_update' => array(                     //web service name (unique in all Moodle)
                'classname'   => 'local_oc_course_creation',                        //class containing the function implementation
                'methodname'  => 'edit_entry',                                      //name of the function into the class
                'classpath'   => 'local/oc_course_creation/externallib.php',        //file containing the class (only used for core external function, not needed if your file is 'component/externallib.php'),
                'description' => 'Update selected preset string of an course by its id',
                'type' => 'write',
                'ajax' => 'true',
                'capabilities' => 'true',
        )
);
/**
 *
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$functions = array(
        'local_oc_course_creation_custom_preset_delete' => array(                   //web service name (unique in all Moodle)
                'classname'   => 'local_oc_course_creation',                        //class containing the function implementation
                'methodname'  => 'delete_entry',                                    //name of the function into the class
                'classpath'   => 'local/oc_course_creation/externallib.php',        //file containing the class (only used for core external function, not needed if your file is 'component/externallib.php'),
                'description' => 'Delete selected preset string of an course by its id',
                'type' => 'write',
                'ajax' => 'true',
                'capabilities' => 'true',
        )
);/**
 *
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$functions = array(
        'local_oc_course_creation_custom_preset_insert' => array(               //web service name (unique in all Moodle)
                'classname'   => 'local_oc_course_creation',                    //class containing the function implementation
                'methodname'  => 'create_entry',                                //name of the function into the class
                'classpath'   => 'local/oc_course_creation/externallib.php',     //file containing the class (only used for core external function, not needed if your file is 'component/externallib.php'),
                'description' => 'Create preset string of an course',
                'type' => 'write',
                'ajax' => 'true',
                'capabilities' => 'true',
        )
);