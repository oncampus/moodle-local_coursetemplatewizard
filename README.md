# Local Plugin ocbsbcoursecreation (Vorlagen-Assistent)

**Local ocbsbcoursecreation** ist ein **lokales Plugin** für Moodle, welches die Nutzung und Verwaltung von Kursvorlagen vereinfacht. Lehrkräfte und Keyuser bei BSFB Hamburg können damit Kursvorlagen auswählen und in ihren eigenen Kursbereich kopieren, um schneller standardisierte Kurse zu erstellen.  
Das Plugin basiert auf [local_oc_course_creation](https://gitlab.oncampus-system.de/moodle/plugins/local/local_oc_course_creation/-/tree/MOODLE_405_STABLE?ref_type=heads).

## Features

- **Kursvorlagenverwaltung**: Automatische Anlage einer Kategorie für Vorlagenkurse.
- **Kurskopien**: Kopieren von Vorlagen in beliebige Kategorien (manuell oder per Cronjob).
- **Einfache Anpassung**: Konfiguration von Namen, Kurznamen, Sichtbarkeit, Kategorie und Beschreibung beim Kopieren.
- **Preset-Verwaltung**: Bearbeiten von vorgefertigten Werten (Semester, Jahr etc.).
- **Webservice-Unterstützung**: Löschen und Bearbeiten von Preset-Werten via Webservice.
- **Rollen- und Zugriffssteuerung**: Berechtigungen für Manager und Kurscreator.

## Installation

1. Kopiere das Plugin in das Verzeichnis:  
   ```bash
   /local/ocbsbcoursecreation
   ```

2. Starte die Installation über:  
   **Website-Administration → Mitteilungen**  
   oder führe den CLI-Upgrade aus:  
   ```bash
   php admin/cli/upgrade.php
   ```

### Voraussetzungen
- Moodle-Version: `2020061511` oder höher.

## Konfiguration

Nach der Installation ist das Plugin über folgende Seite konfigurierbar:  
**Website-Administration → Plugins → Lokale Plugins → ocbsbcoursecreation**

Wichtige Einstellungen:
- **Vorlagen-Kategorie**: Standard-Kategorie für Kursvorlagen.
- **Namenskonvention**: Verwendung von Prefix, Separator und Textfeldern.
- **Voreinstellungen**: `use_default_course_naming`, `course_name_readonly`, `course_shortname_readonly`.
- **Asynchroner Modus**: Optionale Aktivierung der Kurskopie über Cronjobs.
- **Preset-Verwaltung**: Hinzufügen, Bearbeiten oder Löschen von Preset-Werten.

## Nutzung

- Lehrkräfte und Keyuser sehen verfügbare Vorlagenkurse und können diese mit wenigen Klicks in ihre eigenen Bereiche kopieren.
- Das Kopieren kann entweder direkt oder über einen asynchronen Task (Cronjob) erfolgen.
- Administratoren können Preset-Werte wie Semester oder Jahr vordefinieren und diese im UI bereitstellen.

## Rechte

Dieses Plugin definiert folgende Rechte:

| Name des Rechts                                        | Beschreibung                                    | Standardrolle       |
|--------------------------------------------------------|-------------------------------------------------|---------------------|
| `local/ocbsbcoursecreation:ocbsbcoursecreation_access_capability` | Zugriff auf die Kursvorlagen-Übersicht         | Manager, Kurscreator |
| `local/ocbsbcoursecreation:handle_presets`             | Verwaltung von Preset-Werten                    | Manager             |
| `local/ocbsbcoursecreation:course_cat_copy_cap`        | Erlaubt das Kopieren von Kursvorlagen           | Manager, Kurscreator |

## Cronjobs

Dieses Plugin definiert folgende Cronjobs:

| Task Class | Beschreibung            | Standardintervall der Ausführung |
|------------|-------------------------|----------------------------------|
| *(optional)* | Asynchrone Kurskopien | Manuell oder nach Bedarf         |

## Web Services

Dieses Plugin stellt folgende Webservice-Funktionen zur Verfügung:

| Webservice-Funktion                              | Beschreibung                                |
|--------------------------------------------------|---------------------------------------------|
| `local_ocbsbcoursecreation_custom_preset_delete` | Löscht einen definierten Preset-Eintrag per ID |

## Lizenz

Dieses Plugin ist lizensiert unter [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

## Credits

Autor: Jordan Krause ([jordan.krause@oncampus.de](mailto:jordan.krause@oncampus.de))  
Inspired by / thanks to: oncampus GmbH, Laurenz Schindler
