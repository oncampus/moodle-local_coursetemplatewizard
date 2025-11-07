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
 * @package    local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @category   admin
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Nur Haupt-Admin darf Settings sehen.
if (!$hassiteconfig) {
    return;
}

$component = 'local_ocbsbcoursecreation';
$settings  = new admin_settingpage($component, get_string('pluginname', $component));

// Kategorien-Auswahl: existierenden Kursbereich wählen (ID-basiert, voller Pfad).
$catoptions = [];
$catlist = core_course_category::make_categories_list();
foreach ($catlist as $id => $path) {
    $catoptions[$id] = $path;
}

$settings->add(new admin_setting_configselect(
    'local_ocbsbcoursecreation/categoryid',
    get_string('settings_choose_course', $component),
    get_string('template_course_desc', $component),
    0,
    $catoptions
));

$settings->add(new admin_setting_configtext(
    'local_ocbsbcoursecreation/serviceuserid',
    get_string('settings_serviceuserid', $component),
    get_string('settings_serviceuserid_desc', $component),
    0,
    PARAM_INT
));

// Seite einhängen.
$ADMIN->add('localplugins', $settings);
