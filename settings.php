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

/**
 * Plugin administration pages are defined here.
 *
 * @package     local
 * @subpackage  local_occoursecreation
 * @category    admin
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    global $DB;
    $sql = "SELECT id,name From mdl_config_plugin WHERE plugin like 'format%' ";

    $courseFormats = $DB->get_records("config_plugins");
    $formatsAvailable = array();
    foreach ($courseFormats as $courseFormat) {
        if (substr($courseFormat->plugin, 0, strlen("format_")) === "format_")
            $formatsAvailable[$courseFormat->plugin] = $courseFormat->plugin;
    }

    $settings = new admin_settingpage('local_coursecreation',
        get_string('pluginname', 'occoursecreation'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_oc_course_creation/setting_check_formats';
    $title = get_string('course_formats', 'occoursecreation');
    $description = get_string('course_formats_desc', 'occoursecreation');
    $setting = new admin_setting_configmulticheckbox(
        $name,
        $visiblename = get_string('setting_check_formats', 'occoursecreation'),
        $description,
        $defaultsetting = array_slice($formatsAvailable,0,4),
        $choices = $formatsAvailable
    );
    $settings->add($setting);
}
