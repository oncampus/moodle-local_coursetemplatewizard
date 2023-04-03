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
 * @var $systemcontext
 * @var $hassiteconfig
 * @var $ADMIN
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/config.php');

$context = context_system::instance();

$capuse    = 'local/oc_course_creation:oc_course_creation_access_capability';
$capedit   = 'local/oc_course_creation:handle_presets';
$component = 'local_oc_course_creation';

if (has_any_capability([$capuse, $capedit], $context)) {
    $ADMIN->add('courses',
            new admin_category('courses_local_oc_course_creation',
                    get_string('pluginname', $component)
            ));
}

if (has_capability($capuse, $context)) {

    $plugin_name   = get_string('pluginname', $component);
    $category_name = get_string('plugin_categoryname', $component);

    $settingspage = new admin_settingpage($component,
            $plugin_name);

    //list plugin pages

    $ADMIN->add('courses_local_oc_course_creation',
            new admin_externalpage('list_courses_to_copy', get_string('settings:create_course', $component),
                    new moodle_url('/local/oc_course_creation/list_courses_to_copy.php', []),
                    [$capuse]
            ));
}

if (has_capability($capedit, $context)) {
    $ADMIN->add('courses_local_oc_course_creation',
            new admin_externalpage('list_preset_values', get_string('settings:edit_presets', $component),
                    new moodle_url('/local/oc_course_creation/list_preset_values.php', []),
                    [$capedit]
            ));

}

if ($hassiteconfig) {

    $name        = 'local_oc_course_creation/category';
    $description = get_string('settings:template_course_desc', $component);
    $selection   = [];
    $categories  = core_course_category::get_all(['returnhidden']);
    $default     = NULL;
    foreach ($categories as $category) {
        $selection[$category->name] = $category->name;
        if ($category->name === $category_name) {
            $default = $selection[$category->name];
        }
    }
    $setting = new admin_setting_configselect(
            $name,
            $visiblename = get_string('settings:choose_course', $component),
            $description,
            $default,
            $selection,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/use_default_course_naming';
    $description = get_string('settings:use_default_course_naming_desc', $component);
    $default     = false;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:use_default_course_naming', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/course_name_readonly';
    $description = get_string('settings:template_course_name_readonly_desc', $component);
    $default     = false;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:template_course_name_readonly', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/course_shortname_readonly';
    $description = get_string('settings:template_course_shortname_readonly_desc', $component);
    $default     = false;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:template_course_shortname_readonly', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/textfield_values';
    $description = get_string('settings:template_textfield_values_desc', $component);
    $default     = get_string('settings:template_textfield_values_default', $component);
    $setting     = new admin_setting_configtextarea(
            $name,
            $visiblename = get_string('settings:template_textfield_values', $component),
            $description,
            $default,
            PARAM_RAW,
    );
    $settingspage->add($setting);

    $settingspage->add($setting);
    $name        = 'local_oc_course_creation/display_seperator';
    $description = get_string('settings:template_display_seperator_desc', $component);
    $default     = true;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:template_display_seperator', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/textfield_seperator';
    $description = get_string('settings:template_textfield_seperator_desc', $component);
    $default     = " ': ' - '_'";
    $setting     = new admin_setting_configtext(
            $name,
            $visiblename = get_string('settings:template_textfield_seperator', $component),
            $description,
            $default,
            PARAM_RAW,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/course_shortname_readonly';
    $description = get_string('settings:template_course_shortname_readonly_desc', $component);
    $default     = false;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:template_course_shortname_readonly', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/toggle_prefix';
    $description = get_string('settings:template_prefix_checkbox_toggle_desc', $component);
    $default     = false;
    $setting     = new admin_setting_configcheckbox(
            $name,
            $visiblename = get_string('settings:template_prefix_checkbox_toggle', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $name        = 'local_oc_course_creation/prefix_text';
    $description = get_string('settings:template_prefix_desc', $component);
    $selection   = [];
    $default     = get_string('settings:template_prefix_default', $component);
    $setting     = new admin_setting_configtext(
            $name,
            $visiblename = get_string('settings:edit_template_prefix', $component),
            $description,
            $default,
    );
    $settingspage->add($setting);

    $ADMIN->add('localplugins', $settingspage);
}

