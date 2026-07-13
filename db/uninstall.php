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

defined('MOODLE_INTERNAL') || die();

/**
 * Uninstall the plugin and the plugin settings
 * Won't remove course category to ensure course templates are sustained
 *
 * @package     local_coursetemplatewizard
 * @category    admin
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/user/lib.php');

/**
 * Uninstall hook: remove plugin settings and dedicated service user (including role) only.
 * Do NOT delete any course categories or courses.
 *
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @package local_coursetemplatewizard
 */
function xmldb_local_coursetemplatewizard_uninstall(): bool {
    global $DB;

    // Deletes all configurations of this plugin from config_plugins.
    $DB->delete_records('config_plugins', ['plugin' => 'local_coursetemplatewizard']);

    // Deletes the dedicated service user and the service user role for this plugin.
    $templatewizardserviceuser = $DB->get_record('user', ['username' => 'coursetemplateserviceuser']);
    if ($templatewizardserviceuser) {
        delete_user($templatewizardserviceuser);
    }
    $templatewizardserviceuserrole = $DB->get_record('role', ['shortname' => 'coursetemplateserviceuser']);
    if ($templatewizardserviceuserrole) {
        delete_role($templatewizardserviceuserrole->id);
    }

    // If you have your own tables, you can define them here via xmldb.
    // and manage them via install.xml - then no manual DROP is needed.
    // If legacy tables 'ocbsbcoursecreation_type' / 'ocbsbcoursecreation_value' exist,
    // they should be managed via XMLDB (install.xml) and upgrade/uninstall.

    return true;
}
