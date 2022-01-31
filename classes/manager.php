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
    public static function get_by_id($id) {
        global $DB;
        try {
            return $DB->get_record('oc_course_creation', ['id' => $id]);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of string
     */
    public static function get_by_type($type) {
        global $DB;
        try {
            return $DB->get_records('oc_course_creation', $type);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of stdclass
     */
    public static function get_diff_types() {
        global $DB;
        $sql = "Select DISTINCT type from {oc_course_creation}";
        try {
            return $DB->get_records_sql($sql);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of string
     */
    public static function get_diff_types_string() {
        global $DB;
        $sql = "Select DISTINCT type from {oc_course_creation}";
        try {
            $arr = array();
            foreach ($DB->get_records_sql($sql) as $value) {
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
    public static function get_all() {
        global $DB;
        try {
            return $DB->get_records('oc_course_creation', []);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * Updates an entry by it's id
     *
     * @return bool DB transaction successful
     */
    public static function update($id, $type, $string) {
        global $DB;
        $recordToUpdate = new stdClass();
        $recordToUpdate->id = $id;
        $recordToUpdate->type = $type;
        $recordToUpdate->string = $string;
        try {
            return $DB->update_record('oc_course_creation', $recordToUpdate, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * Creats an entry
     *
     * @return bool DB transaction successful
     */
    public static function create($type, $string) {
        global $DB;
        $recordToInsert = new stdClass();
        $recordToInsert->type = $type;
        $recordToInsert->string = $string;
        try {
            return $DB->insert_record('oc_course_creation', $recordToInsert, false);
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
    public function delete($id) {
        global $DB;
        return $DB->delete_records('oc_course_creation', ['id' => $id]);
    }

    /**
     * Inserts a type at new rank and changes other ranks down
     *
     * @param $id int
     * @param $new_rank int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function update_insert_sort($id, $new_rank) {
        global $DB;
        $highest_record = $this->get_type_by_id($id);
        $highest_record->rank = $new_rank;
        $transactions = array();

        $records = $this->get_types_lower_and_equal_ranked($new_rank);

        $transaction = $DB->start_delegated_transaction();
        $transactions[] = $DB->update_record('oc_course_creation_type', $highest_record, true);
        foreach ($records as $record) {
            $record->rank += 1;
            $transactions[] = $DB->update_record('oc_course_creation_type', $record, true);
        }
        if (!in_array(false, $transactions, true)) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Inserts a type at new rank and changes other ranks down
     *
     * @param $id int
     * @param $new_rank int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function update_delete_sort($id) {
        global $DB;
        $record_to_delete = $this->get_type_by_id($id);
        $new_rank = $record_to_delete->rank;
        $transactions = array();

        $records = $this->get_types_lower_and_equal_ranked($new_rank);

        $transaction = $DB->start_delegated_transaction();
        $transactions[] = $DB->delete_records('oc_course_creation_type', ['id' => $id]);
        foreach ($records as $record) {
            if ($record->id != $id) {
                $record->rank -= 1;
                $transactions[] = $DB->update_record('oc_course_creation_type', $record, true);
            }
        }
        if (!in_array(false, $transactions, true)) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Inserts a type at highest rank
     *
     * @param $string String
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function create_type($string) {
        global $DB;
        $sql = "SELECT rank from {oc_course_creation_type} ORDER BY rank DESC LIMIT 1";
        $new_type = new stdClass();
        $new_type->type = $string;
        $new_type->rank = $DB->get_record_sql($sql) + 1;
        return $DB->insert_record('oc_course_creation_type',$new_type);
    }
    /**
     * Inserts a type at highest rank
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_type_by_id($id) {
        global $DB;
        return $DB->get_record('oc_course_creation_type',['id'=> $id]);
    }

    /**
     * gets all types where rank is same or greater than given
     *
     * @param $rank int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_types_lower_and_equal_ranked($rank) {
        global $DB;
        $sql = "SELECT * FROM {oc_course_creation_type} WHERE 'rank' <= ?";
        try {
            return $DB->get_records_sql($sql, [$rank]);
        } catch (dml_exception $e) {
            return false;
        }
    }
}