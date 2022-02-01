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

    public function create_value($string, $type_id) {
        global $DB;
        $value = new stdClass();
        $value->string = $string;
        $transaction = $DB->start_delegated_transaction();
        $insert_type = true;
        $value->type_id = $type_id;
        $insert_value = $DB->insert_record("oc_course_creation_value", $value);
        if ($insert_value && $insert_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    public function create_value_and_type($string, $type) {
        global $DB;
        $value = new stdClass();
        $value->string = $string;
        $transaction = $DB->start_delegated_transaction();
        $insert_type = $this->create_type($type);
        $value->type_id = $this->get_last_rank()->id;
        $insert_value = $DB->insert_record("oc_course_creation_value", $value);
        if ($insert_value && $insert_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    public function update_value($id, $string, $type_id) {
        global $DB;
        $value = $this->get_value_by_id($id);
        $value->string = $string;
        $value->type_id = $type_id;
        return $DB->update_record('oc_course_creation_value', $value);
    }

    /**
     * Deletes a string and records for an id
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function delete_value($id) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $value = $DB->get_record('oc_course_creation_value', ['id' => $id]);
        $delete_value = $DB->delete_records('oc_course_creation_value', ['id' => $id]);
        $types = $DB->get_records('oc_course_creation_value', ['type_id' => $value->type_id]);
        $delete_type = true;
        if (count($types) === 0) {
            $delete_type = $this->update_delete_sort($value->type_id);
            var_dump($types);
        }
        if ($delete_value && $delete_type) {
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
        $new_type = new stdClass();
        $new_type->type = $string;
        $new_type->rank = $this->get_last_rank()->rank + 1;
        return $DB->insert_record('oc_course_creation_type', $new_type);
    }

    /**
     * Inserts a type at highest rank
     *
     * @param $string String
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_last_rank() {
        global $DB;
        $sql = "SELECT * from {oc_course_creation_type} ORDER BY rank DESC LIMIT 1";
        return $DB->get_record_sql($sql);
    }

    /**
     * Get A type by its id
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_type_by_id($id) {
        global $DB;
        return $DB->get_record('oc_course_creation_type', ['id' => $id]);
    }

    /**
     * Get a value by id
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_value_by_id($id) {
        global $DB;
        return $DB->get_record('oc_course_creation_value', ['id' => $id]);
    }

    /**
     * Get all preset values
     *
     * @return array DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_all_values() {
        global $DB;
        return $DB->get_records('oc_course_creation_value');
    }

    /**
     * Get all preset types ascending by rank
     *
     * @return array DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_all_types() {
        global $DB;
        $sql = "SELECT * from {oc_course_creation_type} ORDER BY rank ASC";
        return $DB->get_records_sql($sql);
    }

    /**
     * gets all types where rank is same or greater than given
     *
     * @param $rank int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_types_lower_and_equal_ranked($rank) {
        global $DB;
        $sql = "SELECT * FROM {oc_course_creation_type} WHERE 'rank' <= ?";
        try {
            return $DB->get_records_sql($sql, [$rank]);
        } catch (dml_exception $e) {
            return false;
        }
    }
}