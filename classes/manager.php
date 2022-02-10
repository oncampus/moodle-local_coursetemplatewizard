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
     * Creates an value
     *
     * @param $string
     * @param $type_id
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
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

    /**
     * Creates an type and the value with new type_id
     *
     * @param $string
     * @param $type
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function create_value_and_type($string, $type) {
        global $DB;
        $value = new stdClass();
        $value->string = $string;
        $transaction = $DB->start_delegated_transaction();
        $insert_type = $this->create_type($type);
        $value->type_id = $this->get_last_type_by_rank()->id;
        $insert_value = $DB->insert_record("oc_course_creation_value", $value);
        if ($insert_value && $insert_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Updates an value with
     *
     * @param $id
     * @param $string
     * @param $type_id
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function update_value($id, $string, $type_id) {
        global $DB;
        $value = new stdClass();
        $value->id = $id;
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
        }
        if ($delete_value && $delete_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Deletes a type at new rank and changes other ranks down
     *
     * @param $id int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function update_delete_sort($id) {
        global $DB;
        $record_to_delete = $this->get_type_by_id($id);
        $new_rank = $record_to_delete->rank;
        $transactions = array();

        $records = $this->get_types_higher_and_equal_rank($new_rank);

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
        $new_type->rank = $this->get_last_type_by_rank()->rank + 1;
        return $DB->insert_record('oc_course_creation_type', $new_type);
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
        return $DB->get_records('oc_course_creation_type', [], "rank ASC");
    }

    /**
     * Swaps two ranks of type
     *
     * @param $type_rank1 int
     * @param $type_rank2 int
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function swap($type_rank1, $type_rank2) {
        global $DB;
        $record_to_swap1 = $this->get_type_by_rank($type_rank1);
        $record_to_swap2 = $this->get_type_by_rank($type_rank2);

        $tmp_rank = $record_to_swap1->rank;

        $record_to_swap1->rank = $record_to_swap2->rank;
        $record_to_swap2->rank = $tmp_rank;

        $transaction = $DB->start_delegated_transaction();

        $tr1 = $DB->update_record('oc_course_creation_type', $record_to_swap1);
        $tr2 = $DB->update_record('oc_course_creation_type', $record_to_swap2);

        if ($tr1 && $tr2) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * gets all types where rank is same or greater than given
     *
     * @param $rank int
     * @return mixed DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_types_higher_and_equal_rank($rank) {
        global $DB;
        $sql = "SELECT * FROM {oc_course_creation_type} WHERE rank >= ?";
        try {
            return $DB->get_records_sql($sql, [$rank]);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * Get A type by its id
     *
     * @param $id int
     * @return mixed DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_type_by_id($id) {
        global $DB;
        return $DB->get_record('oc_course_creation_type', ['id' => $id]);
    }

    /**
     * Get A type by its rank
     *
     * @param $rank int
     * @return mixed DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_type_by_rank($rank) {
        global $DB;
        return $DB->get_record('oc_course_creation_type', ['rank' => $rank]);
    }

    /**
     * Returns the
     *
     * @return mixed DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    private function get_last_type_by_rank() {
        global $DB;
        $sql = "SELECT * from {oc_course_creation_type} ORDER BY rank DESC LIMIT 1";
        if ($rank = $DB->get_record_sql($sql)) {
            return $rank;
        } else {
            $rank = new stdClass();
            $rank->rank = 0;
            $rank->id = 0;
            $rank->type = "";
            return $rank;
        }
    }

    /**
     * Run the adhoc task and preform the backup.
     */
    public function execute($backupid, $restoreid, $user) {

        global $CFG, $DB;

        $backuprecord = $DB->get_record('backup_controllers', array('backupid' => $backupid), 'id, itemid', MUST_EXIST);
        $restorerecord = $DB->get_record('backup_controllers', array('backupid' => $restoreid), 'id, itemid', MUST_EXIST);

        // First backup the course.
        try {
            $bc = \backup_controller::load_controller($backupid); // Get the backup controller by backup id.
        } catch (\backup_dbops_exception $e) {
            delete_course($restorerecord->itemid, false); // Clean up partially created destination course.
            return; // Return early as we can't continue.
        }
        $bc->set_progress(new \core\progress\db_updater($backuprecord->id, 'backup_controllers', 'progress'));
        $copyinfo = $bc->get_copy();
        $backupplan = $bc->get_plan();


            $bc->execute_plan();

        $results = $bc->get_results();
        $backupbasepath = $backupplan->get_basepath();
        $file = $results['backup_destination'];
        $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        // Start the restore process.
        $rc = \restore_controller::load_controller($restoreid);  // Get the restore controller by restore id.
        $rc->set_progress(new \core\progress\db_updater($restorerecord->id, 'backup_controllers', 'progress'));
        $rc->prepare_copy();

        // Set the course settings we can do now (the remaining settings will be done after restore completes).
        $plan = $rc->get_plan();

        $startdate = $plan->get_setting('course_startdate');
        $startdate->set_value($copyinfo->startdate);
        $fullname = $plan->get_setting('course_fullname');
        $fullname->set_value($copyinfo->fullname);
        $shortname = $plan->get_setting('course_shortname');
        $shortname->set_value($copyinfo->shortname);

        // Do some preflight checks on the restore.
        $rc->execute_precheck();
        $status = $rc->get_status();
        // Check that the restore is in the correct status and
        // that is set for asynchronous execution.
        if ($status == \backup::STATUS_AWAITING) {
            // Execute the restore.
            $rc->execute_plan();

        } else {
            // If status isn't 700, it means the process has failed.
            // Retrying isn't going to fix it, so marked operation as failed.
            $rc->set_status(\backup::STATUS_FINISHED_ERR);
            delete_course($restorerecord->itemid, false); // Clean up partially created destination course.
            $file->delete();
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }
            $rc->destroy();
            return; // Return early as we can't continue.

        }

        // Set up remaining course settings.
        $course = $DB->get_record('course', array('id' => $restorerecord->itemid), '*', MUST_EXIST);
        $course->visible = $copyinfo->visible;
        $course->idnumber = $copyinfo->idnumber;
        $course->enddate = $copyinfo->enddate;
        $course->category = $copyinfo->category;
        $DB->update_record('course', $course);

        $copyinfo->id = $restorerecord->itemid;

        $editoroptions =
                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
        $context = \context_course::instance($restorerecord->itemid);
        $editoroptions['context'] = $context;
        $editoroptions['subdirs'] = file_area_contains_subdirs($context, 'course', 'summary', 0);
        if ($editoroptions) {
            $data = file_postupdate_standard_editor($copyinfo, 'summary', $editoroptions, $context, 'course', 'summary', 0);
        }
        if ($overviewfilesoptions = course_overviewfiles_options($restorerecord->itemid)) {
            $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course',
                    'overviewfiles', 0);
        }

        update_course($data, $editoroptions);

        // Cleanup.
        $bc->destroy();
        $rc->destroy();
        $file->delete();
        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        enrol_try_internal_enrol($course->id, $user->id, $CFG->creatornewroleid);

        return $course->id;
    }

}