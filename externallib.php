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
 * local_oc_course_creation externallib will specify additional functions
 *
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_oc_course_creation\manager;

class local_oc_course_creation_external extends external_api {
    /**
     * Returns the description of method parameters
     *
     * @return external_function_parameters
     */
    public static function custom_preset_delete_parameters() {
        return new external_function_parameters(
                [
                        'id' => new external_value(PARAM_INT, 'id of the message')
                ]
        );
    }

    /**
     * @param int $id
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     */
    public static function custom_preset_delete($id) {
        self::validate_parameters(self::custom_preset_delete_parameters(), array('id' => $id));

        $manager = new manager();
        return $manager->delete_value($id);

    }

    /**
     * Returns the description of method result value
     *
     * @return external_description
     */
    public static function custom_preset_delete_returns() {
        return new external_value(PARAM_BOOL, 'Boolean if the message got deleted');
    }
}