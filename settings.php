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

// Einstellungsseite unter "Plugins -> Lokale Plugins".
$component = 'local_ocbsbcoursecreation';
$settings  = new admin_settingpage($component, get_string('pluginname', $component));

// Kategorie-Auswahl (für Vorlagenkategorie, falls weiterhin genutzt).
$name = 'local_ocbsbcoursecreation/category';
$description = get_string('template_course_desc', $component);

// Kategorienamen als Auswahl (Name->Name, wie zuvor genutzt).
$selection = [];
$categories = core_course_category::get_all(['returnhidden' => true]);
foreach ($categories as $cat) {
    $selection[$cat->name] = $cat->name;
}
$default = '';
$settings->add(new admin_setting_configselect(
    $name,
    get_string('settings_choose_course', $component),
    $description,
    $default,
    $selection
));

// Seite in den Adminbaum einhängen (unter Plugins -> Lokale Plugins).
$ADMIN->add('localplugins', $settings);
