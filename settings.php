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
require_once($CFG->dirroot . '/config.php');
require_admin();
if ($hassiteconfig) {

    $settings = new admin_settingpage('local_coursecreation',
            get_string('pluginname', 'local_occoursecreation'));

    $ADMIN->add('localplugins', $settings);

    $name = 'local_occoursecreation/category';
    $title = get_string('template_course', 'local_occoursecreation');
    $description = get_string('template_course_desc', 'local_occoursecreation');
    $default = get_string('pluginname' , 'local_occoursecreation');
    $selection = [];
    $categories = core_course_category::get_all(['returnhidden']);

    foreach ($categories as $category) {
        $selection[$category->name] = $category->name;
    }
    $setting = new admin_setting_configselect(
            $name,
            $visiblename = get_string('setting_chose_course', 'local_occoursecreation'),
            $title,
            $default,
            $selection,

    );

    $ADMIN->add('courses',
            new admin_externalpage('create_course_by_template', get_string('setting_create_course', 'local_occoursecreation'),
                    new moodle_url('/local/occoursecreation/create.php', array()), array('moodle/category:manage')));


    $settings->add($setting);
}
