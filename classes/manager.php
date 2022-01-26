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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/->.

/**
 * @package    local_oc_course_creation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il->
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oc_course_creation;

use dml_transaction_exception;
use stdClass;
use dml_exception;

class manager {

    /**
     * @return mixed single string
     */
    public static function get_by_id($id): stdClass {
        global $DB;
        try {
            return $DB->get_record('local_oc_course_creation', ['id'=>$id]);
        } catch (dml_exception $e) {
            return array();
        }
    }
    /**
     * @return mixed Array of string
     */
    public static function get_by_type($type): array {
        global $DB;
        try {
            return $DB->get_records('local_oc_course_creation', $type);
        } catch (dml_exception $e) {
            return array();
        }
    }
    /**
     * @return mixed Array of stdclass
     */
    public static function get_diff_types(): array {
        global $DB;
        $sql = "Select DISTINCT type from {local_oc_course_creation}";
        try {
            return $DB->get_records_sql($sql);
        } catch (dml_exception $e) {
            return array();
        }
    }
    /**
     * @return mixed Array of string
     */
    public static function get_diff_types_string(): array {
        global $DB;
        $sql = "Select DISTINCT type from {local_oc_course_creation}";
        try {
            $arr=array();
            foreach ($DB->get_records_sql($sql) as $value){
                $arr[] = $value->type;
            }
            return $arr;
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of all strings
     */
    public static function get_all(): array {
        global $DB;
        try {
            return $DB->get_records('local_oc_course_creation', []);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * Updates an entry by it's id
     *
     * @return bool DB transaction successful
     */
    public static function update($id, $type, $string): bool {
        global $DB;
        $recordToUpdate = new stdClass();
        $recordToUpdate->id = $id;
        $recordToUpdate->type = $type;
        $recordToUpdate->string = $string;
        try {
            return $DB->update_record('local_oc_course_creation', $recordToUpdate, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * Creats an entry
     *
     * @return bool DB transaction successful
     */
    public static function create($type, $string): bool {
        global $DB;
        $recordToInsert = new stdClass();
        $recordToInsert->type = $type;
        $recordToInsert->string = $string;
        try {
            return $DB->insert_record('local_oc_course_creation', $recordToInsert, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * Deletes a string and records for an id
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function delete($id): bool {
        global $DB;
        return $DB->delete_records('local_oc_course_creation', ['id' => $id]);
    }
}