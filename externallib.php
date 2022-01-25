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

class local_oc_course_creation extends external_api {
    /**
     * Returns the description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_entry_parameters() {
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
    public static function delete_entry($id) {
        self::validate_parameters(self::delete_message_parameters(), array('id' => $id));

        $manager = new manager();
        return $manager->delete($id);

    }

    /**
     * Returns the description of method result value
     *
     * @return external_description
     */
    public static function delete_entry_returns() {
        return new external_value(PARAM_BOOL, 'Boolean if the message got deleted');
    }
    //___________________________________________________________________________________________________

    /**
     * Returns the description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_entry_parameters() {
        return new external_function_parameters(
                [
                        'string' => new external_value(PARAM_TEXT, 'string value'),
                        'type' => new external_value(PARAM_TEXT, 'string value')
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
    public static function create_entry($string, $type) {
        self::validate_parameters(self::create_entry_parameters(), array('string' => $string, 'type' => $type));

        $manager = new manager();
        return $manager->create($string, $type);

    }

    /**
     * Returns the description of method result value
     *
     * @return external_description
     */
    public static function create_entry_returns() {
        return new external_value(PARAM_BOOL, 'Boolean if the entry got inserted.');
    }
    //___________________________________________________________________________________________________

    /**
     * Returns the description of method parameters
     *
     * @return external_function_parameters
     */
    public static function edit_entry_parameters() {
        return new external_function_parameters(
                [
                        'id' => new external_value(PARAM_INT, 'id of the message'),
                        'string' => new external_value(PARAM_TEXT, 'id of the message'),
                        'type' => new external_value(PARAM_TEXT, 'id of the message')
                ]
        );
    }

    /**
     * @param int $id
     * @param String $string
     * @param String $type
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     */
    public static function edit_entry($id, $string, $type) {
        self::validate_parameters(self::edit_entry_parameters(), array('id' => $id, 'string' => $string, 'type' => $type));

        $manager = new manager();
        return $manager->update($id, $string, $type);

    }

    /**
     * Returns the description of method result value
     *
     * @return external_description
     */
    public static function edit_entry_returns() {
        return new external_value(PARAM_BOOL, 'Boolean if the entry got updated.');
    }

}