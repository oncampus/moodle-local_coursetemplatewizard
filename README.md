# Course creation #


## Descripton ##
   This plugin will create a new course category in which template courses can be created.
   These templates can be easely copied to other categories to create courses after certaint shemes. 
   It Allows to edit course img, name, shortname, visibility, category and description.
   The creation will be either by crontask or as an direct execute (Changing with branch used).

### Functionality/Use Case ###
   This plugin can be used to enable people to use courses as predefiend templates. Through the slightly 
   customized cretion interface course relatet information can be modified.
   It let's course creator manage course creation faster with less steps in the settings and a UI which displays the templates.

   The plugin will create an course wich will be filled with the special extra informations.
   After that it let's a crontask merge all other course settings into this created course.
   
### Version-Testet ### 
    Moodle 3.9-4.0 Stable
    
### Current use ###
    --- Should not be mentiond here--- 
    
### Requires ### 
   Moodle core course-copy, manual enrollment 
    
 ### Technical ### 
   Plugin page can be found under
   - Administration -> courses -> course dublication -> create course from template
  
  The course creation plugin has settings in 
   - Administration -> plugins -> local plugins  
      - course category which hosts the template courses
      - Name of the checkbox used to enable a prefix
      - Text of the prefix field
    
   - Administration -> courses -> course dublication -> Edit presets
      - let's you edit the dropdown entries in the course creation form

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/oc_course_creation

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
