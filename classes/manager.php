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
 * @package     local_oc_course_creation
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @auther      schindlerl
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oc_course_creation;

use coding_exception;
use core\event\course_created;
use core\task\scheduled_task;
use core_analytics\user;
use dml_transaction_exception;
use moodle_exception;
use moodle_url;
use restore_controller;
use restore_controller_exception;
use stdClass;
use dml_exception;

class manager {

    /**
     * Get's the course summary
     *
     * @param int $course_id
     * @return bool|\stored_file[]
     * @throws dml_exception
     */
    public function get_course_summary(int $course_id) {
        global $DB;
        return $DB->get_field("course", "summary", ["id" => $course_id]);
    }

    /**
     * Creates an value
     *
     * @param string $string
     * @param int $type_id
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function create_value(string $string, int $type_id): bool {
        if (is_Null($string) || $string === "" || !$this->get_type_by_id($type_id)) {
            return false;
        }
        global $DB;
        $value = new stdClass();
        $value->string = $string;
        $value->type_id = $type_id;
        return $DB->insert_record("oc_course_creation_value", $value, false);
    }

    /**
     * Creates a type and the value with new type_id
     *
     * @param string $string
     * @param string $type
     * @return bool
     * @throws dml_exception
     * @throws dml_transaction_exception
     */
    public function create_value_and_type(string $string, string $type): bool {
        global $DB;
        if (!$string || !$type) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();

        $new_type = new stdClass();
        $new_type->type = $type;
        $new_type->rank = $this->get_last_type_by_rank()->rank + 1;
        $insert_type = $DB->insert_record('oc_course_creation_type', $new_type);

        $value = new stdClass();
        $value->string = $string;
        $value->type_id = $this->get_last_type_by_rank()->id;
        $insert_value = $DB->insert_record("oc_course_creation_value", $value);

        if ($insert_value && $insert_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Updates a value with
     *
     * @param int $id
     * @param string $string
     * @param int $type_id
     * @return bool
     * @throws dml_exception
     */
    public function update_value(int $id, string $string, int $type_id): bool {
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
     * @param int $id
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function delete_value(int $id): bool {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $value = $DB->get_record('oc_course_creation_value', ['id' => $id]);
        if (!$value) {
            return false;
        }
        $types = $DB->get_records('oc_course_creation_value', ['type_id' => $value->type_id]);
        $delete_type = true;
        if (count($types) === 1) {
            $delete_type = $this->update_delete_sort($value->type_id);
        }
        $delete_value = $DB->delete_records('oc_course_creation_value', ['id' => $id]);
        if ($delete_value && $delete_type) {
            $DB->commit_delegated_transaction($transaction);
            return true;
        }
        return false;
    }

    /**
     * Deletes a type and changes other ranks down
     *
     * @param int $id
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function update_delete_sort(int $id): bool {
        global $DB;
        $record_to_delete = $this->get_type_by_id($id);
        if (!$record_to_delete) {
            return $record_to_delete;
        }
        $new_rank = $record_to_delete->rank;
        $transactions = [];

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
     * @param string $string
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function create_type(string $string): bool {
        global $DB;
        $new_type = new stdClass();
        $new_type->type = $string;
        if ($string && $string !== "") {
            $new_type->rank = $this->get_last_type_by_rank()->rank + 1;
            return $DB->insert_record('oc_course_creation_type', $new_type);
        }
        return false;
    }

    /**
     * Get a value by id
     *
     * @param int $id
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_value_by_id(int $id): bool {
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
     * @param int $type_rank1
     * @param int $type_rank2
     * @return bool DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function swap($type_rank1, $type_rank2) {
        global $DB;
        $record_to_swap1 = $this->get_type_by_rank($type_rank1);
        $record_to_swap2 = $this->get_type_by_rank($type_rank2);

        if ($record_to_swap1 && $record_to_swap2) {
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
        }
        return false;
    }

    /**
     * gets all types where rank is same or greater than given
     *
     * @param int $rank
     * @return array|false DB transaction successful
     */
    public function get_types_higher_and_equal_rank($rank) {
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
     * @param int $id
     * @return mixed DB transaction successful
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function get_type_by_id(int $id): mixed {
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
    public function get_type_by_rank($rank) {
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
    public function get_last_type_by_rank() {
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
     * Create the copy
     *
     * @param object $mdata
     * @param $course
     * @return int courseid
     * @throws \coding_exception
     * @throws \moodle_exception
     * @throws dml_exception
     * @throws \backup_controller_exception
     */
    public function create_copy(object $mdata, bool $async) {
        global $USER, $DB, $CFG, $PAGE;
        $copyids = [];
        $mdata->startdate = time();
        $mdata->enddate = time() + (6 * 4 * 7 * 24 * 60 * 60);
        $mdata->keptroles = [];
        $adminIDs = get_admins();
        $adminid = array_pop($adminIDs)->id;
        // Create the initial backupcontoller.
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $mdata->courseid, \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO, \backup::MODE_COPY, $adminid, \backup::RELEASESESSION_YES);
        $copyids['backupid'] = $bc->get_backupid();

        // Create the initial restore contoller.
        [$fullname, $shortname] = \restore_dbops::calculate_course_names(
                0, get_string('copyingcourse', 'backup'), get_string('copyingcourseshortname', 'backup'));
        $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $mdata->category);
        $rc = new \restore_controller($copyids['backupid'], $newcourseid, \backup::INTERACTIVE_NO,
                \backup::MODE_COPY, $adminid, \backup::TARGET_NEW_COURSE, null,
                \backup::RELEASESESSION_NO, $mdata);
        $copyids['restoreid'] = $rc->get_restoreid();
        $newcorusecontext = \context_course::instance($newcourseid);
        $bc->set_status(\backup::STATUS_AWAITING);
        $rc->save_controller();

        $context = \context_course::instance($mdata->courseid);
        $courseurl = course_get_url($mdata->courseid);

        $restoreurl = new moodle_url('/backup/restorefile.php', array('contextid' => $newcorusecontext->id));
        $progresssetup = array(
                'backupid' => $rc->get_restoreid(),
                'contextid' => $context->id,
                'courseurl' => $courseurl->out(),
                'restoreurl' => $restoreurl->out(),
                'headingident' => 'copy'
        );
        echo $PAGE->get_renderer('core', 'backup')->render_from_template('core/async_backup_status', $progresssetup);

        // Create the ad-hoc task to perform the course copy.
        $asynctask = new \core\task\asynchronous_copy_task();
        $asynctask->set_blocking(false);
        $asynctask->set_custom_data($copyids);

        \restore_dbops::delete_course_content($newcourseid);
        if (!$async) {
            $asynctask->execute();
            // Clean up the controller.
            $bc->destroy();
        } else {
            \core\task\manager::queue_adhoc_task($asynctask);
        }

        $editoroptions =
                ['maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true];

        $editoroptions['context'] = $newcorusecontext;
        $editoroptions['subdirs'] = file_area_contains_subdirs($newcorusecontext, 'course', 'summary', 0);
        if ($editoroptions) {
            $data = file_postupdate_standard_editor($mdata, 'summary', $editoroptions, $newcorusecontext, 'course', 'summary', 0);
        }
        if ($overviewfilesoptions = course_overviewfiles_options($newcourseid)) {
            $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $newcorusecontext, 'course',
                    'overviewfiles', 0);
        }
        $data->id = $newcourseid;
        update_course($data, $editoroptions);
        $this->check_enrol($newcourseid, $USER->id, 3);
        return $newcourseid;
    }

    /**
     * Create the copy
     *
     * @param stdClass $mdata
     * @return int courseid
     * @throws coding_exception
     * @throws moodle_exception
     * @throws restore_controller_exception
     */
    public function create_copy_async(object $mdata) {
        global $USER, $DB, $CFG, $PAGE;
        $mdata->startdate = time();
        $mdata->enddate = time() + (6 * 4 * 7 * 24 * 60 * 60); // Integer timestamp of the start of the destination course.
        $mdata->keptroles = [];
        $adminIDs = get_admins();
        $copyids = [];
        $userid = array_pop($adminIDs)->id;
        // Create the initial backupcontoller.
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $mdata->courseid, \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO, \backup::MODE_COPY, $userid, \backup::RELEASESESSION_YES);
        $copyids['backupid'] = $bc->get_backupid();

        // Create the initial restore contoller.
        list($fullname, $shortname) = \restore_dbops::calculate_course_names(
                0, get_string('copyingcourse', 'backup'), get_string('copyingcourseshortname', 'backup'));
        $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $mdata->category);
        $rc = new \restore_controller($copyids['backupid'], $newcourseid, \backup::INTERACTIVE_NO,
                \backup::MODE_COPY, $userid, \backup::TARGET_NEW_COURSE, null,
                \backup::RELEASESESSION_NO, $mdata);
        $copyids['restoreid'] = $rc->get_restoreid();

        $bc->set_status(\backup::STATUS_AWAITING);
        $bc->get_status();
        $rc->save_controller();

        // Create the ad-hoc task to perform the course copy.
        $asynctask = new \core\task\asynchronous_copy_task();
        $asynctask->set_blocking(false);
        $asynctask->set_custom_data($copyids);

        \restore_dbops::delete_course_content($newcourseid);
        // Configure the controllers based on the submitted data.
        $mdata->copyids = $copyids;
        $mdata->id = $newcourseid;
        $mdata->originalcourseid = $mdata->courseid;

        /* test */
        $context = \context_course::instance($mdata->courseid);

        $courseurl = course_get_url($mdata->courseid);
        // Add ajax progress bar and initiate ajax via a template.
        $restoreurl = new \moodle_url('/backup/restorefile.php', array('contextid' => $context->id));
        $progresssetup = array(
                'backupid' => $rc->get_restoreid(),
                'contextid' => $context->id,
                'courseurl' => $courseurl->out(),
                'restoreurl' => $restoreurl->out()
        );

        echo $PAGE->get_renderer('core', 'backup')->render_from_template('core/async_backup_status', $progresssetup);

        \core\task\manager::queue_adhoc_task($asynctask);
        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->visible = $mdata->visible;
        $course->idnumber = $mdata->idnumber;
        $course->enddate = time() + (6 * 4 * 7 * 24 * 60 * 60); // Integer timestamp of the start of the destination course.
        $course->category = $mdata->category;
        $DB->update_record('course', $course);

        $editoroptions =
                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

        $context = \context_course::instance($newcourseid);
        $editoroptions['context'] = $context;
        $editoroptions['subdirs'] = file_area_contains_subdirs($context, 'course', 'summary', 0);
        if ($editoroptions) {
            $data = file_postupdate_standard_editor($mdata, 'summary', $editoroptions, $context, 'course', 'summary', 0);
        }
        if ($overviewfilesoptions = course_overviewfiles_options($newcourseid)) {
            $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course',
                    'overviewfiles', 0);
        }
        $data->id = $newcourseid;
        update_course($data, $editoroptions);
        $this->check_enrol($newcourseid, $USER->id, 3);
        return $newcourseid;
    }

    function unenrol($courseid, $userid, $enrolmethod = 'manual') {
        $enrolinstances = enrol_get_instances($courseid, false);
        $plugin = enrol_get_plugin($enrolmethod);

        if (is_null($plugin)) {
            return false;
        }

        foreach ($enrolinstances as $instance) {
            // Check enrolment.
            if ($enrolmethod == $instance->enrol) {
                $enrolinstance = $instance;
                break;
            }
        }
        $plugin->unenrol_user($enrolinstance, $userid);
    }

    function check_enrol($courseid, $userid, $roleid, $enrolmethod = 'manual') {
        global $DB;
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $enrolinstances = enrol_get_instances($courseid, false);
        $plugin = enrol_get_plugin($enrolmethod);

        if (is_null($plugin)) {
            return false;
        }

        foreach ($enrolinstances as $instance) {
            // Check enrolment.
            if ($enrolmethod == $instance->enrol) {
                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                }
                $enrolinstance = $instance;
                break;
            }
        }
        if (empty($enrolinstance)) {
            $fields = $plugin->get_instance_defaults();
            $id = $plugin->add_instance($course, $fields);

            $enrolinstance = $DB->get_record('enrol', ['id' => $id]);
            $enrolinstance->expirynotify = $plugin->get_config('expirynotify');
            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
            $enrolinstance->roleid = $plugin->get_config('roleid');
            $enrolinstance->timemodified = time();
            $DB->update_record('enrol', $enrolinstance);
        } // Enrol user in course.

        // Get the course context.
        $coursecontext = \context_course::instance($courseid);

        // Check if user is already enrolled with another enrolment method.
        $userisenrolled = is_enrolled($coursecontext, $userid, "", false);

        // If the user is already enrolled, continue to avoid a second enrolment for the user.
        if (!$userisenrolled) {
            $plugin->enrol_user($enrolinstance, $userid, $roleid);
        }
    }
}
