<?php
namespace local_ocbsbcoursecreation;

use moodle_exception;
use moodle_url;

/**
 * Manager class for handling course template copy logic.
 *
 * @package     local_ocbsbcoursecreation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    public function replace_course_with_template(object $mdata): int {
        global $USER, $CFG, $PAGE, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $copyids = [];
        $mdata->startdate = time();
        $mdata->enddate = time() + (6 * 4 * 7 * 24 * 60 * 60);
        $mdata->keptroles = [];

        $templateid = $mdata->templateid;
        $targetcourseid = $mdata->targetcourseid;
        $targetcourse = get_course($targetcourseid, false);

        // Admin-ID für Backup/Restore.
        $adminids = get_admins();
        $adminid = array_pop($adminids)->id;

        // Backup des Templates.
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

        // Restore in neuen Kurs (anstelle des Zielkurses).
        [$fullname, $shortname] = \restore_dbops::calculate_course_names(
            0,
            $mdata->fullname ?? get_string('copyingcourse', 'backup'),
            $mdata->shortname ?? get_string('copyingcourseshortname', 'backup')
        );
        $categoryid = $DB->get_field('course', 'category', ['id' => $targetcourseid]);
        $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $categoryid);

        $newidnumber = !empty($targetcourse->idnumber) ? $targetcourse->idnumber : "";
        $DB->set_field('course', 'idnumber', $newidnumber, ['id' => $newcourseid]);
        $mdata->idnumber = $newidnumber;
        $mdata->visible = true;
        $mdata->id = $newcourseid;

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

        // Fortschrittsanzeige.
        $context = \context_course::instance($templateid);
        $courseurl = course_get_url($templateid);
        $restoreurl = new moodle_url('/backup/restorefile.php', ['contextid' => $newcontext->id]);

        echo $PAGE->get_renderer('core', 'backup')->render_from_template('core/async_backup_status', [
            'backupid'   => $rc->get_restoreid(),
            'contextid'  => $context->id,
            'courseurl'  => $courseurl->out(),
            'restoreurl' => $restoreurl->out(),
            'headingident' => 'copy',
        ]);

        // Task synchron ausführen.
        $asynctask = new \core\task\asynchronous_copy_task();
        $asynctask->set_blocking(false);
        $asynctask->set_custom_data($copyids);

        \restore_dbops::delete_course_content($newcourseid);
        $asynctask->execute();
        $bc->destroy();

        // idnumber nach Task erneut setzen.
        $DB->set_field('course', 'idnumber', $newidnumber, ['id' => $newcourseid]);

        // Metadaten aus $mdata anwenden.
        $newcourse = get_course($newcourseid, false);
        $newcourse->fullname  = $mdata->fullname ?? $targetcourse->fullname;
        $newcourse->shortname = $mdata->shortname ?? $targetcourse->shortname;
        $newcourse->idnumber  = $newidnumber;
        $newcourse->summary   = !empty($mdata->summary_editor['text']) ?
                                $mdata->summary_editor['text'] : $targetcourse->summary;
        update_course($newcourse);

        // Beschreibung + Bild.
        if (!empty($mdata->summary_editor['text'])) {
            $editoroptions = [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'context' => $newcontext,
            ];
            $mdata = file_postupdate_standard_editor($mdata, 'summary', $editoroptions, $newcontext, 'course', 'summary', 0);
            update_course($mdata);
        }

        // Nutzer aus Zielkurs migrieren.
        $targetcontext = \context_course::instance($targetcourseid);
        $users = get_enrolled_users($targetcontext, '', 0, 'u.id');
        foreach ($users as $user) {
            $roles = get_user_roles($targetcontext, $user->id);
            foreach ($roles as $role) {
                $this->check_enrol($newcourseid, $user->id, $role->roleid);
            }
        }

        // Zielkurs löschen.
        delete_course(get_course($targetcourseid), false);

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
}
