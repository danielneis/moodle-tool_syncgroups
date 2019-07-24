Copy groups and members between courses Plugin for Moodle
---------------------------------------------------

With this plugin you can synchronize groups and groups members between two courses,
in a way that a course A will be a mirror of course B.

Only users that are already enrolled are added as group members.

Install
-------

* Put these files at moodle/admin/tool/syncgroups/
 * You may use "composer require danielneis/tool_syncgroups"
 * or git "clone https://github.com/danielneis/moodle-tool_syncgroups.git"
 * or download the latest version from https://github.com/danielneis/moodle-tool_syncgroups/repository/archive.zip
* Log in your Moodle as Admin and go to "Notifications" page
* Follow the instructions to install the plugin

Usage
-----

After installing the plugin there will be a new link "Synchronize groups" under Course Administration > Users on the Administration block.
Clicking that link will show a page for you to select the groups from current course you want to copy members to which target courses.
