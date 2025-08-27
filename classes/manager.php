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

namespace local_ocbsbcoursecreation;

use moodle_exception;
use moodle_url;

/**
 * Manager class for handling course template copy logic.
 *
 * @package     local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Replace a target course with a template while preserving the target's name/shortname.
     *
     * Ablauf:
     * 1) Template sichern (Backup).
     * 2) Neuen Kurs mit temporären, garantiert eindeutigen Namen anlegen.
     * 3) Restore des Templates in den neuen Kurs.
     * 4) Custom Fields und Nutzer/Rollen übernehmen.
     * 5) Alten Zielkurs löschen (macht seinen Shortname frei).
     * 6) Neuen Kurs final auf Fullname/Shortname des ehemaligen Zielkurses umbenennen.
     *
     * @param object $mdata  Erwartet mind. ->templateid, ->targetcourseid; evtl. summary_editor etc.
     * @return int           ID des neu erstellten/ersetzten Kurses
     * @throws \moodle_exception
     */
    public function replace_course_with_template(object $mdata): int {
        global $USER, $CFG, $PAGE, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Ignoriere externe Namensvorgaben: Wir wollen Name/Shortname des Zielkurses beibehalten.
        unset($mdata->fullname, $mdata->shortname);

        $copyids = [];
        $mdata->startdate = time();
        $mdata->enddate   = time() + (6 * 4 * 7 * 24 * 60 * 60);
        $mdata->keptroles = [];

        $templateid     = (int)$mdata->templateid;
        $targetcourseid = (int)$mdata->targetcourseid;
        $targetcourse   = get_course($targetcourseid, false);

        // Endgültige (gewünschte) Namen vom ZIELkurs übernehmen.
        $desiredfullname  = $targetcourse->fullname;
        $desiredshortname = $targetcourse->shortname;

        // Admin-ID für Backup/Restore.
        $adminids = get_admins();
        $adminid  = array_pop($adminids)->id;

        // 1) Backup des Template-Kurses.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $templateid,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_COPY,
            $adminid,
            \backup::RELEASESESSION_YES
        );
        $copyids['backupid'] = $bc->get_backupid();

        // 2) Temporäre, garantiert eindeutige Namen für den neuen Kurs berechnen.
        // (Suffix stellt sicher, dass keine shortname-Kollision entsteht.).
        [$tempfullname, $tempshortname] = \restore_dbops::calculate_course_names(
            0,
            $desiredfullname . ' [pending]',
            $desiredshortname . '-' . $targetcourseid . '-' . time()
        );

        // Kategorie vom Zielkurs übernehmen und neuen Kurs anlegen.
        $categoryid  = (int)$DB->get_field('course', 'category', ['id' => $targetcourseid]);
        $newcourseid = \restore_dbops::create_new_course($tempfullname, $tempshortname, $categoryid);

        // Idnumber möglichst früh setzen/mitnehmen.
        $newidnumber       = !empty($targetcourse->idnumber) ? $targetcourse->idnumber : "";
        $DB->set_field('course', 'idnumber', $newidnumber, ['id' => $newcourseid]);
        $mdata->idnumber   = $newidnumber;
        $mdata->visible    = true;
        $mdata->id         = $newcourseid;

        // 3) Restore in den neuen Kurs.
        $rc = new \restore_controller(
            $copyids['backupid'],
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_COPY,
            $adminid,
            \backup::TARGET_NEW_COURSE,
            null,
            \backup::RELEASESESSION_NO,
            $mdata
        );
        $copyids['restoreid'] = $rc->get_restoreid();

        $newcontext = \context_course::instance($newcourseid);
        $bc->set_status(\backup::STATUS_AWAITING);
        $rc->save_controller();

        // Fortschrittsanzeige rendern (optional visuelles Feedback).
        $context   = \context_course::instance($templateid);
        $courseurl = course_get_url($templateid);
        $restoreurl = new \moodle_url('/backup/restorefile.php', ['contextid' => $newcontext->id]);

        echo $PAGE->get_renderer('core', 'backup')->render_from_template('core/async_backup_status', [
            'backupid'     => $rc->get_restoreid(),
            'contextid'    => $context->id,
            'courseurl'    => $courseurl->out(),
            'restoreurl'   => $restoreurl->out(),
            'headingident' => 'copy',
        ]);

        // 4a) Custom-Course-Fields vom alten Zielkurs in den neuen Kurs übernehmen.
        $this->copy_custom_coursefields($targetcourseid, $newcourseid);

        // 3b) Restore-Task synchron ausführen (wie im bestehenden Code).
        $asynctask = new \core\task\asynchronous_copy_task();
        $asynctask->set_blocking(false);
        $asynctask->set_custom_data($copyids);

        \restore_dbops::delete_course_content($newcourseid);
        $asynctask->execute();
        $bc->destroy();

        // Idnumber nach Task ggf. erneut setzen (Sicherheit).
        $DB->set_field('course', 'idnumber', $newidnumber, ['id' => $newcourseid]);

        // 4b) Metadaten anwenden – KEINE Namensänderung an dieser Stelle!
        $newcourse = get_course($newcourseid, false);
        $newcourse->idnumber = $newidnumber;

        // Summary/Editor-Inhalt anwenden (optional).
        if (!empty($mdata->summary_editor['text'])) {
            // Editorverarbeitung mit Files.
            $editoroptions = [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'context'  => $newcontext,
            ];
            $mdata = file_postupdate_standard_editor(
                $mdata,
                'summary',
                $editoroptions,
                $newcontext,
                'course',
                'summary',
                0
            );
            // Comment:$mdata enthält nun summary und summaryformat.
            $mdata->id = $newcourseid;
            update_course($mdata);
        }

        // 4c) Nutzer/Rollen aus Zielkurs migrieren.
        $targetcontext = \context_course::instance($targetcourseid);
        $users = get_enrolled_users($targetcontext, '', 0, 'u.id');
        foreach ($users as $user) {
            $roles = get_user_roles($targetcontext, $user->id);
            foreach ($roles as $role) {
                $this->check_enrol($newcourseid, $user->id, $role->roleid);
            }
        }

        // 5) Zielkurs löschen – macht dessen Shortname frei.
        delete_course(get_course($targetcourseid), false);

        // 6) Final auf die gewünschten Namen (vom ehem. Zielkurs) umbenennen.
        $final = (object)[
            'id'        => $newcourseid,
            'fullname'  => $desiredfullname,
            'shortname' => $desiredshortname,
            'idnumber'  => $newidnumber,
        ];
        update_course($final);

        return $newcourseid;
    }


    /**
     * Kopiert Nutzer & Rollen vom alten in den neuen Kurs.
     */
    private function clone_enrolments(int $sourcecourseid, int $targetcourseid): void {
        $contextsource = \context_course::instance($sourcecourseid);
        $contexttarget = \context_course::instance($targetcourseid);

        $users = get_enrolled_users($contextsource);
        foreach ($users as $user) {
            $roles = get_user_roles($contextsource, $user->id);
            foreach ($roles as $role) {
                role_assign($role->roleid, $user->id, $contexttarget);
            }
        }
    }

    /**
     * Get's the course summary
     *
     * @param int $course_id
     * @return bool|\stored_file[]
     * @throws dml_exception
     */
    public function get_course_summary(int $courseid) {
        global $DB;
        return $DB->get_field("course", "summary", ["id" => $courseid]);
    }



    /**
     * Prüft die Einschreibung und meldet Nutzer mit entsprechender Rolle an.
     */
    public function check_enrol($courseid, $userid, $roleid, $enrolmethod = 'manual') {
        $enrolinstances = enrol_get_instances($courseid, false);
        $plugin = enrol_get_plugin($enrolmethod);

        if (!$plugin) {
            return false;
        }

        foreach ($enrolinstances as $instance) {
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
            $id = $plugin->add_instance(get_course($courseid), $fields);
            $enrolinstance = enrol_get_instance($id);
        }

        if (!is_enrolled(\context_course::instance($courseid), $userid)) {
            $plugin->enrol_user($enrolinstance, $userid, $roleid);
        }
    }

    /**
     * Meldet einen User ab.
     */
    public function unenrol($courseid, $userid, $enrolmethod = 'manual') {
        $enrolinstances = enrol_get_instances($courseid, false);
        $plugin = enrol_get_plugin($enrolmethod);

        if (!$plugin) {
            return false;
        }

        foreach ($enrolinstances as $instance) {
            if ($enrolmethod == $instance->enrol) {
                $plugin->unenrol_user($instance, $userid);
                break;
            }
        }
    }

    /**
     * Kopiert Custom-Course-Fields (core_customfield) vom Quellkurs in den Zielkurs.
     * Arbeitet schema-robust (mit/ohne 'value', mit/ohne Typspalten).
     *
     * @param int      $sourcecourseid
     * @param int      $targetcourseid
     * @param string[] $shortnames  Optional: nur diese Feld-Shortnames kopieren
     * @return void
     */
    private function copy_custom_coursefields(int $sourcecourseid, int $targetcourseid, array $shortnames = []): void {
        global $DB;

        // Tabellen vorhanden?
        $mgr = $DB->get_manager();
        foreach (['customfield_field', 'customfield_category', 'customfield_data'] as $t) {
            if (!$mgr->table_exists($t)) {
                return;
            }
        }

        // Ziel-Schema ermitteln (für kompatibles Insert/Update).
        $cols = $DB->get_columns('customfield_data');
        $hascontextid   = isset($cols['contextid']);
        $hasvalue       = isset($cols['value']);
        $hasvalueformat = isset($cols['valueformat']);
        $hasint         = isset($cols['intvalue']);
        $hasdec         = isset($cols['decvalue']);
        $hasshortchar   = isset($cols['shortcharvalue']);
        $haschar        = isset($cols['charvalue']);
        $hastext        = isset($cols['textvalue']);

        // Relevante Felder der Course-Area laden.
        $sql = "SELECT f.id, f.shortname
                FROM {customfield_field} f
                JOIN {customfield_category} c ON c.id = f.categoryid
                WHERE c.component = :component AND c.area = :area";
        $fields = $DB->get_records_sql($sql, ['component' => 'core_course', 'area' => 'course']);
        if (empty($fields)) {
            return;
        }

        if (!empty($shortnames)) {
            $allow = array_flip($shortnames);
            $fields = array_filter($fields, static fn($f) => isset($allow[$f->shortname]));
            if (empty($fields)) {
                return;
            }
        }

        // Quelldaten laden.
        $fieldids = array_map(static fn($f) => (int)$f->id, $fields);
        [$insql, $inparams] = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED, 'fid');
        $sourcedata = $DB->get_records_select(
            'customfield_data',
            "fieldid $insql AND instanceid = :src",
            $inparams + ['src' => $sourcecourseid]
        );
        if (empty($sourcedata)) {
            return;
        }

        $now = time();
        $targetctxid = \context_course::instance($targetcourseid)->id;

        foreach ($sourcedata as $record) {
            // Rohwert für legacy 'value' bestimmen (Fallback-Reihenfolge).
            $raw = '';
            if ($hastext && $record->textvalue !== null && $record->textvalue !== '') {
                $raw = (string)$record->textvalue;
            } else if ($haschar && $record->charvalue !== null && $record->charvalue !== '') {
                $raw = (string)$record->charvalue;
            } else if ($hasshortchar && $record->shortcharvalue !== null && $record->shortcharvalue !== '') {
                $raw = (string)$record->shortcharvalue;
            } else if ($hasint && $record->intvalue !== null) {
                $raw = (string)$record->intvalue;
            } else if ($hasdec && $record->decvalue !== null) {
                $raw = (string)$record->decvalue;
            }

            // Ziel-Datensatz vorhanden?
            $existing = $DB->get_record('customfield_data', [
                'fieldid'    => $record->fieldid,
                'instanceid' => $targetcourseid,
            ]);

            // Payload dynamisch je nach existierenden Spalten aufbauen.
            $payload = (object)[
                'fieldid'      => (int)$record->fieldid,
                'instanceid'   => (int)$targetcourseid,
                'timemodified' => $now,
            ];
            if ($hascontextid) {
                $payload->contextid    = $targetctxid;
            }
            if ($hasvalue) {
                $payload->value        = $raw;
            }
            if ($hasvalueformat) {
                $payload->valueformat  = isset($record->valueformat) ? (int)$record->valueformat : 0;
            }
            if ($hasint) {
                $payload->intvalue     = $record->intvalue;
            }
            if ($hasdec) {
                $payload->decvalue     = $record->decvalue;
            }
            if ($hasshortchar) {
                $payload->shortcharvalue = $record->shortcharvalue ?? null;
            }
            if ($haschar) {
                $payload->charvalue    = $record->charvalue ?? '';
            }
            if ($hastext) {
                $payload->textvalue    = $record->textvalue ?? null;
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
}
