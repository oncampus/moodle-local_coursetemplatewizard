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
 * @package     local_oc_course_creation
 * @category    admin
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        global $DB;
        $sql = "SELECT id,name From mdl_config_plugin WHERE plugin like 'format%' ";

        $courseFormats = $DB->get_records("config_plugins");


        $settings = new admin_settingpage('local_coursecreation', get_string('pluginname', 'oc_course_creation'));
        $ADMIN->add('localplugins', $settings);

        $name = 'local_oc_course_creation/setting_check_formats';
        $title = get_string('course_formats', 'oc_course_creation');
        $description = get_string('course_formats_desc', 'oc_course_creation');
        $setting = new admin_setting_configmulticheckbox(
            $name = "setting_check_formats",
            $visiblename = get_string('setting_check_formats', 'oc_course_creation'),
            $description = "Select all formats you want to be able to create",
            $defaultsetting = array(),
            $choices = array(
                "name"=>"1",
                "2"=>"2",
                "3"=>"3",
                "4"=>"4",
                "5"=>"5",
                "6"=>"6",
            )
        );

        $settings->add($setting);
    }
}
