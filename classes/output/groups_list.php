<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_syncgroups\output;

class groups_list implements \renderable, \templatable {

    public function __construct($courseid) {
        $this->courseid = $courseid;
    }
    public function export_for_template(\renderer_base $renderer) {
        $data = new \stdClass();

        $data->groups = array_map(function($group) {
            return (object)['id' => $group->id, 'name' => $group->name];
        }, array_values(groups_get_all_groups($this->courseid, 0, 0, 'g.id, g.name')));

        $data->hasgroups = !empty($data->groups);
        $data->nogroups = get_string('nogroups', 'tool_syncgroups');
        $data->groupname = get_string('groups');
        $options = [
            'id' => 'check-groups',
            'name' => 'check-groups',
            'value' => 1,
        ];
        $data->mastercheckbox = $renderer->render(new \core\output\checkbox_toggleall('groups', true, $options));

        return $data;
    }
}
