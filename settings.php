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
 * Plugin administration settings.
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @category    admin
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Only admin is allowed to view the settings.
if (!$hassiteconfig) {
    return;
}

$component = 'local_coursetemplatewizard';
$settings  = new admin_settingpage($component, get_string('pluginname', $component));

// Category-selection: choose existing course-category (ID-based, complete path).
$catoptions = [];
$catlist = core_course_category::make_categories_list();
foreach ($catlist as $id => $path) {
    $catoptions[$id] = $path;
}

$settings->add(new admin_setting_configselect(
    'local_coursetemplatewizard/templatecoursecategoryid',
    get_string('settings_choose_course', $component),
    get_string('template_course_desc', $component),
    0,
    $catoptions
));

$settings->add(new admin_setting_configtext(
    'local_coursetemplatewizard/serviceuserid',
    get_string('settings_serviceuserid', $component),
    get_string('settings_serviceuserid_desc', $component),
    0,
    PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'local_coursetemplatewizard/templatetargetcourseexceptions',
    get_string('settings:templatetargetcourseexceptions', 'local_coursetemplatewizard'),
    get_string('settings:templatetargetcourseexceptions_desc', 'local_coursetemplatewizard'),
    '',
    PARAM_SEQUENCE
));

// Add settings-page.
$ADMIN->add('localplugins', $settings);
