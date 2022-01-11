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
 * @package    local_occoursecreation
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il->
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_occoursecreation;

use dml_transaction_exception;
use stdClass;
use dml_exception;

class manager {

    /**
     * @return mixed Array of prefix strings
     */
    public static function getPrefixes(): array {
        global $DB;
        try {
            return $DB->get_records('local_occoursecreation', ["type" => false]);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of postfix strings
     */
    public static function getPostfixes(): array {
        global $DB;
        try {
            return $DB->get_records('local_occoursecreation', ["type" => true]);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of postfix strings
     */
    public static function getAll(): array {
        global $DB;
        try {
            return $DB->get_records('local_occoursecreation', []);
        } catch (dml_exception $e) {
            return array();
        }
    }

    /**
     * @return mixed Array of postfix strings
     */
    public static function update($id, $type, $string): bool {
        global $DB;
        $recordToUpdate = new stdClass();
        $recordToUpdate->id = $id;
        $recordToUpdate->type = $type;
        $recordToUpdate->string = $string;
        try {
            return $DB->update_record('local_occoursecreation', $recordToUpdate, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * @return mixed Array of postfix strings
     */
    public static function create($type, $string): bool {
        global $DB;
        $recordToInsert = new stdClass();
        $recordToInsert->type = $type;
        $recordToInsert->string = $string;
        try {
            return $DB->insert_record('local_occoursecreation', $recordToInsert, false);
        } catch (dml_exception $e) {
            return false;
        }
    }
}