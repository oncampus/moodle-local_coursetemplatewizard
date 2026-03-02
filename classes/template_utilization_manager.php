<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_coursetemplatewizard;

use backup;
use backup_controller;
use coding_exception;
use context_course;
use core\context\course;
use core\task\asynchronous_copy_task;
use ddl_exception;
use dml_exception;
use moodle_exception;
use restore_controller;
use restore_controller_exception;
use restore_dbops;
use stdClass;
use stored_file;

/**
 * Manager class for handling course template copy logic.
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 Oncampus GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_utilization_manager {
    /**
     * Gets the course summary.
     *
     * @param int $courseid
     * @return false|string|null
     * @throws dml_exception
     */
    public function get_course_summary(int $courseid): false|string|null {
        global $DB;
        return $DB->get_field("course", "summary", ["id" => $courseid]);
    }

    /**
     * Transfers the summary provided in the summary_editor from the form
     *
     * @param object $formdata
     * @param int $courseid
     * @param bool|course $targetcoursecontext
     * @return void
     * @throws moodle_exception
     */
    public function transfer_summary_from_form_to_course(object $formdata, int $courseid, bool|course $targetcoursecontext): void {
        global $CFG;
        if (!empty($formdata->summary_editor['text'])) {
            // Editorverarbeitung mit Files.
            $editoroptions = [
                    'maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $CFG->maxbytes,
                    'context' => $targetcoursecontext,
            ];
            $coursedata = file_postupdate_standard_editor(
                $formdata,
                'summary',
                $editoroptions,
                $targetcoursecontext,
                'course',
                'summary',
                0
            );
            $coursedata->id = $courseid;
            update_course($coursedata);
        }
    }

    /**
     * Transfers the picture defined in the copy form.
     * If no picture has been defined, the target course picture will be kept.
     *
     * @param object $formdata
     * @param bool|course $targetcoursecontext
     * @return void
     * @throws coding_exception
     */
    public function transfer_picture_from_form_to_course(object $formdata, bool|course $targetcoursecontext): void {
        $fs = get_file_storage();
        if (empty($formdata->hasoverview)) {
            $fs->delete_area_files($targetcoursecontext->id, 'course', 'overviewfiles', 0);
        } else {
            // Es wurde ein Bild gewählt -> Draft nach overviewfiles speichern (wie im Kursformular).
            $fileoptions = [
                    'subdirs' => 0,
                    'maxfiles' => 1,
                    'accepted_types' => '*',
            ];
            file_save_draft_area_files(
                (int) $formdata->overviewdraftid,
                $targetcoursecontext->id,
                'course',
                'overviewfiles',
                0,
                $fileoptions
            );
        }
    }

    /**
     * Replaces the target course with a course template.
     * Preserves following data from the target course:
     * - shortname
     * - fullname
     * - idnumber
     * - startdate
     * - enddate
     * - visibility
     * - custom field data
     * - enrolments and roles
     * - groups and groupings
     *
     * 1) Backup of the course template.
     * 2) Restore course template into the target course.
     * 3) Restore preservable data from the target course.
     *
     * @param false|course $targetcoursecontext
     * @param int $targetcourseid
     * @param int $templateid
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws coding_exception
     * @throws ddl_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws restore_controller_exception
     */
    public function replace_course_with_template_and_render_progress(
        false|course $targetcoursecontext,
        int $targetcourseid,
        int $templateid
    ): void {
        global $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $this->check_if_template_course_category_contains_template($templateid);

        // Gets all pre-existing custom field data from the target course, which has to be kept.
        $coursecustomfielddata = $this->get_course_customfields_data($targetcourseid);

        // Retrieving the service user id, which presumably has all required permissions.
        $serviceuserid = $this->get_backup_serviceuserid();
        // 1) Backup of the template course.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $templateid,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_COPY,
            $serviceuserid,
            backup::RELEASESESSION_YES
        );

        $copyids = [];
        $copyids['backupid'] = $bc->get_backupid();

        // 2) Restore into the target course.
        $targetcourse   = get_course($targetcourseid, false);
        $coursecopydata = $this->create_course_copy_data_object($targetcourse);
        $rc = new restore_controller(
            $copyids['backupid'],
            $targetcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_COPY,
            $serviceuserid,
            backup::TARGET_EXISTING_ADDING,
            null,
            backup::RELEASESESSION_NO,
            $coursecopydata
        );
        $copyids['restoreid'] = $rc->get_restoreid();

        $bc->set_status(backup::STATUS_AWAITING);
        $rc->save_controller();

        $this->render_overwrite_process($templateid, $targetcoursecontext, $copyids['restoreid']);

        // 2a) Executing Restore-Task.
        $asynctask = new asynchronous_copy_task();
        $asynctask->set_custom_data($copyids);
        $deleteoptions = ['keep_roles_and_enrolments' => true, 'keep_groups_and_groupings' => true];
        restore_dbops::delete_course_content($targetcourseid, $deleteoptions);
        $asynctask->execute();
        $bc->destroy();

        // 3) Restore custom field data for the target course.
        if (!empty($coursecustomfielddata)) {
            $this->copy_course_customfield_data_into_course($targetcourseid, $coursecustomfielddata);
        }
    }

    /**
     * Determines the id of the user which has the capability to back up and restore a course.
     *
     * @return int Backup service user id.
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_backup_serviceuserid(): int {
        global $DB;
        $serviceuserid = (int) get_config('local_coursetemplatewizard', 'serviceuserid');
        $serviceuserrecord = null;
        if ($serviceuserid > 0) {
            $serviceuserrecord = $DB->get_record('user', ['id' => $serviceuserid, 'deleted' => 0], 'id');
        }
        if ($serviceuserrecord) {
            $copyuserid = (int) $serviceuserrecord->id;
        } else {
            $adminids = get_admins();
            if (empty($adminids)) {
                throw new moodle_exception(
                    'error',
                    'local_coursetemplatewizard',
                    '',
                    null,
                    'No site admin available for backup execution'
                );
            }
            $copyuserid = (int) reset($adminids)->id;
        }
        return $copyuserid;
    }

    /**
     * Checks, if the configured course category for course templates contains the given template.
     *
     * @param int $templateid ID of the template course.
     * @return void
     * @throws dml_exception
     * @throws moodle_exception Thrown, if template course category is not configured or template
     * course category does not contain the given template id.
     */
    private function check_if_template_course_category_contains_template(int $templateid): void {
        $templatecoursecategoryid = get_config('local_coursetemplatewizard', 'templatecoursecategoryid');
        if (!empty($templatecoursecategoryid)) {
            $allowedcatid = (int) $templatecoursecategoryid;
        } else {
            throw new \moodle_exception(
                'error',
                'local_coursetemplatewizard',
                '',
                null,
                'Template category missing or misconfigured.'
            );
        }
        $templatecourse = get_course($templateid);
        if ((int) $templatecourse->category !== $allowedcatid) {
            throw new moodle_exception(
                'error',
                'local_coursetemplatewizard',
                '',
                null,
                'Template not in allowed category'
            );
        }
    }

    /**
     * Copies the custom field data from a course into the target course.
     *
     * @param int $targetcourseid Id of the target course.
     * @param array $coursecustomfielddata Custom course data to copy into the target course.
     * @return void
     * @throws dml_exception
     */
    private function copy_course_customfield_data_into_course(int $targetcourseid, array $coursecustomfielddata): void {
        global $DB;
        $now = time();
        $targetctxid = context_course::instance($targetcourseid)->id;
        // Ziel-Schema ermitteln (für kompatibles Insert/Update).
        $cols = $DB->get_columns('customfield_data');
        $hascontextid = isset($cols['contextid']);
        $hasvalue = isset($cols['value']);
        $hasvalueformat = isset($cols['valueformat']);
        $hasint = isset($cols['intvalue']);
        $hasdec = isset($cols['decvalue']);
        $hasshortchar = isset($cols['shortcharvalue']);
        $haschar = isset($cols['charvalue']);
        $hastext = isset($cols['textvalue']);
        foreach ($coursecustomfielddata as $record) {
            // Rohwert für legacy 'value' IMMER aus der QUELLE ableiten – unabhängig vom Ziel-Schema.
            // Fallback-Reihenfolge deckt beide Welten ab (typisierte Spalten und legacy 'value').
            $raw = '';
            foreach (['textvalue', 'charvalue', 'shortcharvalue', 'intvalue', 'decvalue', 'value'] as $prop) {
                if (property_exists($record, $prop) && $record->$prop !== null && $record->$prop !== '') {
                    $raw = (string) $record->$prop;
                    break;
                }
            }

            // Ziel-Datensatz vorhanden?
            $existing = $DB->get_record('customfield_data', [
                    'fieldid' => $record->fieldid,
                    'instanceid' => $targetcourseid,
            ]);

            // Payload dynamisch je nach existierenden Spalten aufbauen.
            $payload = (object) [
                    'fieldid' => (int) $record->fieldid,
                    'instanceid' => (int) $targetcourseid,
                    'timemodified' => $now,
            ];
            if ($hascontextid) {
                $payload->contextid = $targetctxid;
            }
            if ($hasvalue) {
                $payload->value = $raw;
            }
            if ($hasvalueformat) {
                // Falls Quelle kein valueformat hat, Standard 0 (FORMAT_MOODLE).
                $payload->valueformat = (int) ($record->valueformat ?? 0);
            }
            if ($hasint) {
                $payload->intvalue = $record->intvalue ?? null;
            }
            if ($hasdec) {
                $payload->decvalue = $record->decvalue ?? null;
            }
            if ($hasshortchar) {
                $payload->shortcharvalue = $record->shortcharvalue ?? null;
            }
            if ($haschar) {
                // Bei char-Feldern lieber leerer String statt null.
                $payload->charvalue = $record->charvalue ?? '';
            }
            if ($hastext) {
                $payload->textvalue = $record->textvalue ?? null;
            }

            if ($existing) {
                $payload->id = $existing->id;
                $DB->update_record('customfield_data', $payload);
            } else {
                $payload->timecreated = $now;
                $DB->insert_record('customfield_data', $payload);
            }
        }
    }

    /**
     * Gets the custom field data from the course and the fields.
     *
     * @param array $fields
     * @param int $sourcecourseid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    private function get_customfield_data_from(array $fields, int $sourcecourseid): array {
        global $DB;
        $fieldids = array_map(static fn($f) => (int) $f->id, $fields);
        [$insql, $inparams] = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED, 'fid');
        $sourcedata = $DB->get_records_select(
            'customfield_data',
            "fieldid $insql AND instanceid = :src",
            $inparams + ['src' => $sourcecourseid]
        );
        return $sourcedata;
    }

    /**
     * Gets relevant course area fields
     *
     * @return array
     * @throws dml_exception
     */
    private function get_course_area_customfields(): array {
        global $DB;
        $sql = "SELECT f.id, f.shortname
                FROM {customfield_field} f
                JOIN {customfield_category} c ON c.id = f.categoryid
                WHERE c.component = :component AND c.area = :area";
        $fields = $DB->get_records_sql($sql, ['component' => 'core_course', 'area' => 'course']);
        return $fields;
    }

    /**
     * Gets data of the course's custom fields.
     *
     * @param int $courseid
     * @return array|null
     * @throws ddl_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    private function get_course_customfields_data(int $courseid): ?array {
        global $DB;
        // Tabellen vorhanden?
        $mgr = $DB->get_manager();
        foreach (['customfield_field', 'customfield_category', 'customfield_data'] as $t) {
            if (!$mgr->table_exists($t)) {
                return null;
            }
        }

        // Relevante Felder der Course-Area laden.
        $fields = $this->get_course_area_customfields();
        if (empty($fields)) {
            return null;
        }

        // Quelldaten laden.
        $coursecustomfielddata = $this->get_customfield_data_from($fields, $courseid);
        if (empty($coursecustomfielddata)) {
            return null;
        }
        return $coursecustomfielddata;
    }

    /**
     * Creates an object containing data from the target course, which have to be preserved and used in the restore process.
     *
     * @param stdClass $targetcourse
     * @return stdClass
     */
    private function create_course_copy_data_object(stdClass $targetcourse): stdClass {
        $coursecopydata = new stdClass();
        $coursecopydata->keptroles = [];
        $coursecopydata->userdata = false;
        // Startdate darf nicht 0 sein (UI/Tasks erwarten eine Epoche) – fallback: jetzt.
        $coursecopydata->startdate =
                isset($targetcourse->startdate) && $targetcourse->startdate > 0 ? $targetcourse->startdate : time();
        // Enddate darf NULL nicht sein – 0 bedeutet „kein Kursende“.
        $coursecopydata->enddate = !empty($targetcourse->enddate) ? (int) $targetcourse->enddate : 0;
        // Sichtbarkeit mitgeben (einige Tasks übernehmen das Feld direkt).
        $coursecopydata->id = $targetcourse->id;
        $coursecopydata->fullname = $targetcourse->fullname;
        $coursecopydata->shortname = $targetcourse->shortname;
        $coursecopydata->idnumber = $targetcourse->idnumber;
        $coursecopydata->visible = isset($targetcourse->visible) ? (int) $targetcourse->visible : 1;
        return $coursecopydata;
    }

    /**
     * Renders the overwrite process for the target course with the course template.
     *
     * @param int $templateid
     * @param bool|course $targetcoursecontext
     * @param string $restoreid
     * @return void
     * @throws \core\exception\moodle_exception
     */
    public function render_overwrite_process(int $templateid, bool|course $targetcoursecontext, string $restoreid): void {
        global $PAGE;
        $context = context_course::instance($templateid);
        $courseurl = course_get_url($templateid);
        $restoreurl = new \moodle_url('/backup/restorefile.php', ['contextid' => $targetcoursecontext->id]);
        echo $PAGE->get_renderer('core', 'backup')->render_from_template('core/async_backup_status', [
                'backupid' => $restoreid,
                'contextid' => $context->id,
                'courseurl' => $courseurl->out(),
                'restoreurl' => $restoreurl->out(),
                'headingident' => 'copy',
        ]);
    }
}
