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
 * @package     local_oc_course_creation
 * @category    string
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Kurse duplizieren';
$string['plugin_categoryname'] = 'Oncampus Kurserstellungs-Kategorie';
$string['settings:choose_course'] = 'Kursbereich auswählen';
$string['settings:create_course'] = 'Kurs aus Vorlage erstellen';
$string['template_course_desc'] =
        'Setzte den Kursbereich fest, im welchen die Kursvorlagen gespeichert sind';
$string['creation_page_title'] = 'Kursvorlagen';
$string['headline_table_view'] = 'Kursvorlagen';
$string['table_head_coursename'] = 'Kursname';
$string['table_head_edit'] = 'Diesen Kurs als Vorlage nutzen';
$string['course_format'] = 'Kursbereich';
$string['info_no_courses'] = 'Es wurde kein Kurs im gewählten Kursbereich gefunden.';
$string['table_head_courseimg'] = 'Vorschaubild';
$string['form:string:form_select'] = 'Bearbeiten/Erstellen';
$string['table_head_preset_value'] = 'voreingestellter Wert';
$string['modal_delete_title'] = 'Eintrag löschen';
$string['modal_delete_preset_failed'] = 'Eintrag löschen fehlgeschlagen';
$string['modal_delete_message'] = 'Willst du den Eintrag löschen?';
$string['modal_delete_button'] = 'Löschen';
$string['modal_create_title'] = 'Eintrag erstellen';
$string['modal_create_message_failed'] = 'Erstellen fehlgeschlagen';
$string['modal_create_message'] = 'Willst du den Eintrag löschen?';
$string['modal_create_button'] = 'Erstellen';
$string['table_btn_submit'] = 'Erstellen';
$string['settings:edit_presets'] = 'Voreingestellte Wert bearbeiten';
$string['edit_preset_value_title'] = 'Bearbeite vorgegebenen Wert';
$string['record_type1_value1'] = 'SoSe';
$string['record_type1_value2'] = 'WiSe';
$string['table_head_new_type'] = 'Beschreibender Titel';
$string['table_head_coursetext'] = 'Beschreibung';
$string['settings:template_prefix_desc'] = 'Stelle ein Beispieltext für den Kursnamens-Prefix ein';
$string['settings:template_prefix_checkbox_text_desc'] = 'Wie soll die Nutzung des Prefixes betitelt sein?';
$string['settings:template_prefix_default'] = 'Modul 1';
$string['settings:edit_template_prefix'] = 'Kursnamens-Prefix';
$string['settings:edit_template_checkbox_desc'] = 'Beschreibung der Checkbox';
$string['settings:edit_template_checkbox_default'] = 'Füge ein Präfix zum Kursnamen hinzufügen';
$string['oc_course_creation:handle_presets'] = 'Kurskopie Voreinstellungen bearbeiten';
$string['oc_course_creation:oc_course_creation_access_capability'] = 'Zugriff auf Kurserstellung aus Vorlage';
$string['oc_course_creation:course_cat_copy_cap'] = 'Kurserstellung aus einer Vorlage';
$string['form:copy:image_desc'] =
        'Wählen Sie bitte ein Bild aus unserer Sammlung (linker grauer Button) aus oder laden Sie ein eigenes Bild für Ihren Kurs hoch.';
$string['form:copy:image_header'] = 'Kursbild auswählen';
$string['form:copy:course_name_header'] = 'Kursdaten eingeben';
$string['form:copy:teacher_course_type_placeholder'] = 'Kursname';
$string['form:copy:teacher_name_placeholder'] = 'Nachname Dozent/in';
$string['form:copy:course_details'] = 'Kursdetails eingeben';
$string['form:copy:description'] =
        'Dieser Kurs wird erstellt und in die angegebene Kurskategorie eingefügt.';
$string['form:copy:select_default'] = 'Kursbereich auswählen';
$string['form:copy:prefix_desc'] = 'Modulbezeichnung hinzufügen';
$string['settings:use_default_course_naming'] = 'Kursname mit Vorgaben erstellen';
$string['settings:use_default_course_naming_desc'] =
        'Dieses Feld deaktiviert in der Kurserstellungsform alle optionen zur Vorgabe des Kursnamens.';
$string['settings:template_textfield_values'] = 'Vorgaben für Textfelder';
$string['settings:template_textfield_values_desc'] =
        'Über die eingabe mit kommaseparierten Bezeichnungen werden freitext eingabefelder in der Kurseingabeform erstellt. Als Standard "Nachname, Kurs Typ" sind zwei Eingabefelder mit der Bezeichnung Nachname und Kurs Bezeichnung eingetragen.';
$string['settings:template_textfield_values_default'] = 'Nachname, Kurs Bezeichnung';
$string['settings:template_textfield_seperator'] = 'Trennelemente';
$string['settings:template_textfield_seperator_desc'] =
        'Die Trennelemente werden zwischen jedem Element der eingabemaske als Drop-Down angezeigt und bieten die möglichkeit den Kursnamen lesbar zu gestalten.';
$string['settings:template_course_name_readonly'] = 'Vollständiger Kursname nur lesen';
$string['settings:template_course_name_readonly_desc'] =
        'Entzieht dem Nutzer die Bearbeitungsrechte der Formulareingabe des vollständigen Kursnamens.';
$string['settings:template_course_shortname_readonly'] = 'Kurzer Kursname nur lesen';
$string['settings:template_course_shortname_readonly_desc'] =
        'Entzieht dem Nutzer die Bearbeitungsrechte der Formulareingabe des kurzen Kursnamens.';
$string['settings:template_prefix_checkbox_toggle'] = 'Ein-/ Ausschalten des Prefix';
$string['settings:template_prefix_checkbox_toggle_desc'] = 'Der Kursnamens Prefix wird (nicht) im Formular angezeigt.';
$string['settings:template_display_seperator'] = 'Ein-/ Ausschalten der Trennelemente';
$string['settings:template_display_seperator_desc'] =
        'Die Trennelemente werden (nicht) im Formular angezeigt und durch ein einfaches Leerzeichen ersetzt.';
$string['settings:use_after_creation_name'] = 'Templates nach Kurserstellung';
$string['settings:use_after_creation_desc'] =
        'Durch Aktivierung werden Templates nach der Kursertellung zur Auswahl angezeigt.';
$string['settings:cshortname_charnumber'] = 'Kurzer Kursname übernommene Zeichen';
$string['settings:cshortname_charnumber_desc'] =
        'Wie viele Zeichen aus den Kursdetails Vorgabe feldern in den Kurs Kurznamen übernommen werden sollen.';
$string['settings:async_process'] = 'Kurskopieprozess asynchron ausführen';
$string['settings:async_process_desc'] = 'Wenn der Kurskopieprozess asynchron ausgeführt wird, wird dieser als Adhoc-Task eingereiht 
und im nächsten Chronjob-Zyklus ausgeführt.
Andernfalls wird der Nutzerthread direkt zur ausführung genutzt.';
