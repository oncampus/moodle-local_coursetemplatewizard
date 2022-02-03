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
 * @subpackage  local_oc_course_creation
 * @category    admin
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/config.php');
require_admin();
if ($hassiteconfig) {

    $plugin_name = get_string('pluginname', 'local_oc_course_creation');
    $category_name = get_string('plugin_categoryname', 'local_oc_course_creation');

    $settings = new admin_settingpage('local_oc_course_creation',
            $plugin_name);

    $ADMIN->add('localplugins', $settings);

    $name = 'local_oc_course_creation/category';
    $description = get_string('template_course_desc', 'local_oc_course_creation');
    $selection = [];
    $categories = core_course_category::get_all(['returnhidden']);
    $default = null;
    foreach ($categories as $category) {
        $selection[$category->name] = $category->name;
        if ($category->name === $category_name) {
            $default = $selection[$category->name];
        }
    }
    $setting = new admin_setting_configselect(
            $name,
            $visiblename = get_string('setting_chose_course', 'local_oc_course_creation'),
            $description,
            $default,
            $selection,
    );
    $settings->add($setting);


    $name = 'local_oc_course_creation/prefix_desc';
    $description = get_string('template_prefix_checkbox_text_desc', 'local_oc_course_creation');
    $selection = [];
    $default = get_string('course_prefix', 'local_oc_course_creation');
    $setting = new admin_setting_configtext(
            $name,
            $visiblename = get_string('setting_edit_template_checkbox_desc', 'local_oc_course_creation'),
            $description,
            $default,
    );
    $settings->add($setting);


    $name = 'local_oc_course_creation/prefix_text';
    $description = get_string('template_prefix_desc', 'local_oc_course_creation');
    $selection = [];
    $default = get_string('template_prefix_default', 'local_oc_course_creation');;
    $setting = new admin_setting_configtext(
            $name,
            $visiblename = get_string('setting_edit_template_prefix', 'local_oc_course_creation'),
            $description,
            $default,
    );
    $settings->add($setting);

    $ADMIN->add('courses',
            new admin_category( 'courses_local_oc_course_creation',
                    get_string('pluginname','local_oc_course_creation')
            ));

    $ADMIN->add('courses_local_oc_course_creation',
            new admin_externalpage('list_courses_to_copy', get_string('setting_create_course', 'local_oc_course_creation'),
                    new moodle_url('/local/oc_course_creation/list_courses_to_copy.php', array()), array('moodle/category:manage')
            ));

    $ADMIN->add('courses_local_oc_course_creation',
            new admin_externalpage('list_preset_values', get_string('edit_presets', 'local_oc_course_creation'),
                    new moodle_url('/local/oc_course_creation/list_preset_values.php', array()), array('moodle/category:manage')
            ));

}
