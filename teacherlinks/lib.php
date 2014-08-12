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

function teacherlinks_general_tab($tagid) {

    $tabtext = html_writer::start_tag('div', array('id' => strtolower($tagid)));
    $tabtext .= get_config('teacherlinks', 'general_tab_text');
    $tabtext .= html_writer::end_tag('div');

    return $tabtext;

}

function teacherlinks_get_other_cohorts_tab($course , $tagid) {
    global $CFG;
    global $DB;
    global $USER;

    $teachroleid = teacherlinks_get_editingteacher_roleid();

    $coursecontext = context_course::instance($course->id);
    $isteacher = user_has_role_assignment($USER->id, $teachroleid, $coursecontext->id);

    $tabtext = html_writer::start_tag('div', array('id' => $tagid, ));

    $modcode = explode('_', $course->shortname);

    if (!isset($modcode[1])) {
        $tabtext .= html_writer::tag('h1', $course->fullname);
        $tabtext .= get_string('not_cohorted_module', 'block_teacherlinks');
        $tabtext .= html_writer::end_tag('div');
        return $tabtext;
    }

    $allowselfenrol = get_config('teacherlinks', 'allow_self_enrol');

    if (!$allowselfenrol) {
        $tabtext .= get_string('serlfenroldisabled', 'block_teacherlinks');
    }

    $sql = 'select id, shortname from {course} where shortname like "' . $modcode[0] . '%" order by shortname desc';

    $courses = $DB->get_records_sql($sql);

    if (count($courses) > 1) {

        $table = new html_table();
        $table->head  = array(get_string('other_cohort_col', 'block_teacherlinks'),
                    get_string('enrolled_col', 'block_teacherlinks'),
                    get_string('other_enrolments_col', 'block_teacherlinks'));

        $table->align = array('left', 'center', 'center');
        $table->width = '100%';
        $table->data  = array();
        $rows = array();

        foreach ($courses as $crs) {
            if ($crs->id == $course->id) {
                continue;
            }
            $coursecontext = context_course::instance($crs->id);
            $row = new html_table_row();

            $othercourse = new html_table_cell($crs->shortname);
            $enrolled = user_has_role_assignment($USER->id, $teachroleid, $coursecontext->id);

            //
            // Look to see if the person needs to enrol themselves.
            //

            $linktext = get_string('already_enrolled', 'block_teacherlinks');
            $linkurl = new moodle_url('/course/view.php', array( 'id' => $crs->id));
            if (!$enrolled && $isteacher) {
                if ($allowselfenrol) {
                    $linktext = get_string('enrol_me', 'block_teacherlinks');

                    $linkurl = new moodle_url('/blocks/teacherlinks/teacherlinks_enrol.php',
                                                array('id' => $course->id, 'enrol' => $crs->id));

                } else {
                    $linktext = get_string('not_enrolled', 'block_teacherlinks');
                    $linkurl = '';
                }
            }
            $enrolmentlink = html_writer::link($linkurl, $linktext);
            $enrolme = new html_table_cell($enrolmentlink);

            //
            // Information about other enrolments.
            //

            $sql = "SELECT
                r.name as name, count(r.name) as count
                FROM
                {role_assignments} ra, {role} r
                WHERE
                ra.roleid = r.id AND ra.contextid = " .  $coursecontext->id . " ".
                "GROUP BY
                r.name
                ORDER BY
                r.id";

            $rolesassigned = $DB->get_records_sql($sql);

            $roledetails = get_string('no_other_roles', 'block_teacherlinks');
            if ($rolesassigned) {

                $otherroles = array();
                foreach ($rolesassigned as $role) {

                    if ($role->count > 1) {
                        $role->name .= 's';
                    }
                    $otherroles[] = $role->name . ':' . $role->count;
                }

                $roledetails = html_writer::alist($otherroles);

            }

            $numofstudents = 2;
            $otherenrolments = new html_table_cell($roledetails);

            //
            // Get the assessment details.
            //

            $row->cells = array($othercourse, $enrolme, $otherenrolments);
            $rows[] = $row;
        }

        $table->data = array_merge($table->data, $rows);
        $tabtext .= html_writer::table($table);
    } else {
        echo get_string('nomodulelinkstodisplay', 'block_teacherlinks');
    }
    $tabtext .= html_writer::end_tag('div');
    return $tabtext;
}

function teacherlinks_get_editingteacher_roleid() {
    global $DB;

    $recid  = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

    return $recid;
}
