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
require_admin();
if ($hassiteconfig && $ADMIN->fulltree) {
    $pluginman = core_plugin_manager::instance();

    //load all enabled format plugins
    $formatsEnabled = $pluginman->get_enabled_plugins('format');
    //load all default format plugins
    $stdFormats = core_plugin_manager::standard_plugins_list('format');
    $default = array();
    //create default array
    foreach ($stdFormats as $key => $value) {
        $default[$value] = $value;
    }
echo json_encode($formatsEnabled);
    //add format checkbox settings
    $settings = new admin_settingpage('local_coursecreation',
        get_string('pluginname', 'local_occoursecreation'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_oc_course_creation/setting_check_formats';
    $title = get_string('course_formats', 'local_occoursecreation');
    $description = get_string('course_formats_desc', 'local_occoursecreation');
    $setting = new admin_setting_configmulticheckbox(
        $name,
        $visiblename = get_string('setting_check_formats', 'local_occoursecreation'),
        $description,
        $defaultsetting = $default,
        $choices = $formatsEnabled
    );
    $settings->add($setting);
}
