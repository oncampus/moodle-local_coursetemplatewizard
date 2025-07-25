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
 * Plugin strings are defined here.
 *
 * @package     local_ocbsbcoursecreation
 * @category    string
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Kursvorlagen & Kopieren';
$string['creation_page_title'] = 'Kursvorlagen';
$string['headline_table_view'] = 'Kursvorlagenübersicht';
$string['table_head_coursename'] = 'Kursname';
$string['table_head_courseimg'] = 'Vorschaubild';
$string['table_head_coursetext'] = 'Beschreibung';
$string['table_head_edit'] = 'Diese Vorlage verwenden';
$string['course_format'] = 'Kursbereich';
$string['info_no_courses'] = 'Es wurde kein Kurs in der ausgewählten Kursvorlagen-Kategorie gefunden.';

$string['settings_choose_course'] = 'Vorlagen-Kursbereich auswählen';
$string['settings_create_course'] = 'Kurs aus Vorlage erstellen';
$string['template_course_desc'] = 'Wählen Sie den Kursbereich, in dem sich die Kursvorlagen befinden.';

$string['form_copy_image_desc'] = 'Wählen Sie ein Kursbild aus oder laden Sie ein eigenes Bild für den Zielkurs hoch.';
$string['form_copy_image_header'] = 'Kursbild auswählen';
$string['form_copy_course_name_header'] = 'Kursdaten';
$string['form_copy_description'] = 'Wählen Sie den Zielkurs, der mit der Vorlage überschrieben wird.';
$string['form_copy_select_default'] = 'Zielkurs auswählen';
$string['select_target_course'] = 'Zielkurs';

$string['fullnamecourse'] = 'Vollständiger Kursname';
$string['shortnamecourse'] = 'Kurzer Kursname';
$string['coursesummary'] = 'Kursbeschreibung';
$string['missingfullname'] = 'Bitte geben Sie einen vollständigen Kursnamen ein.';
$string['missingshortname'] = 'Bitte geben Sie einen kurzen Kursnamen ein.';

$string['apply_template'] = 'Vorlage anwenden';
$string['template_applied'] = 'Die Vorlage wurde erfolgreich auf den Zielkurs angewendet.';

$string['confirm_overwrite_title'] = 'Kurs überschreiben';
$string['confirm_overwrite_message'] = 'Achtung! Der ausgewählte Zielkurs wird mit der gewählten Vorlage überschrieben. Möchten Sie fortfahren?';
$string['confirm_overwrite_yes'] = 'Ja, überschreiben';
$string['confirm_overwrite_no'] = 'Nein, abbrechen';

$string['ocbsbcoursecreation:ocbsbcoursecreation_access_capability'] = 'Zugriff auf Kursvorlagen und Kopierfunktion';
