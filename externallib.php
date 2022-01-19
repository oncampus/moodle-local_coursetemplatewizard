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
 * local_message externallib will specify additional functions
 *
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_oc_course_creation\manager;

class local_oc_course_creation_external extends external_api
{

    /**
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public static function get_course_semester()
    {
        $prefix = manager::getPrefixes();

        return $prefix;
    }

    /**
     * Returns the description of method result value
     * @return external_description
     */
    public static function get_course_semester_returns()
    {
        return new external_value(PARAM_ARRAY, 'Array of Prefixes');
    }

    /**
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public static function get_course_name()
    {
        $postfix = manager::getPostfixes();

        return $postfix;
    }

    /**
     * Returns the description of method result value
     * @return external_description
     */
    public static function get_course_name_returns()
    {
        return new external_value(PARAM_ARRAY, 'Array of Postfixes');
    }
    /**
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public static function get_course_year()
    {
        $postfix = manager::getPostfixes();

        return $postfix;
    }

    /**
     * Returns the description of method result value
     * @return external_description
     */
    public static function get_course_year_returns()
    {
        return new external_value(PARAM_ARRAY, 'Array of Postfixes');
    }


}