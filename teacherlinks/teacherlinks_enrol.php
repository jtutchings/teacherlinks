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

require_once('../../config.php');
require_once(dirname(__FILE__). '/lib.php');
$fromcourse = 0;
$tocourse = 0;

$teacherroleid = teacherlinks_get_editingteacher_roleid();

if ($_REQUEST['id'] && $_REQUEST['enrol'] && ctype_digit($_REQUEST['id']) && ctype_digit($_REQUEST['enrol'])) {
    $fromcourse = trim($_REQUEST['id']);
    $tocourse = trim($_REQUEST['enrol']);
    require_login($fromcourse, false, 0, false);
} else {
    require_login(1, false, 0, false);
}

// If we can enrol enrol the user.
if (get_config('teacherlinks', 'allow_self_enrol')) {

    $enrolok = false;
    $messagetext = '';
    $coursetogoto = $tocourse;
    // First check the users role to make sure they are a teacher on the course the link came from.
    $coursecontext = get_context_instance(CONTEXT_COURSE, $fromcourse);
    if ($coursecontext) {

        if (user_has_role_assignment($USER->id, $teacherroleid, $coursecontext->id)) {
            // They are a teacher.
            $enrolok = true;
        }

        $coursecontext = get_context_instance(CONTEXT_COURSE, $tocourse);
        if ($coursecontext) {
            if ($enrolok && !user_has_role_assignment($USER->id, $teacherroleid, $coursecontext->id)) {
                $enrol = enrol_get_plugin('manual');
                $enrolinstances = enrol_get_instances($tocourse, true);
                foreach ($enrolinstances as $courseenrolinstance) {
                    if ($courseenrolinstance->enrol == "manual") {
                        $instance = $courseenrolinstance;
                        break;
                    }
                }

                $enrol->enrol_user($instance, $USER->id, $teacherroleid);
                if (user_has_role_assignment($USER->id, $teacherroleid, $coursecontext->id)) {
                    $messagetext = get_string('success_enrol_text', 'block_teacherlinks');
                } else {
                    $coursetogoto = $fromcourse;
                    $messagetext = get_string('fail_enrol_text', 'block_teacherlinks');
                }
            }
        } else {
            $messagetext = get_string('context_error_text', 'block_teacherlinks');
        }

    } else {
        $messagetext = get_string('context_error_text', 'block_teacherlinks');
    }
    $url = $CFG->wwwroot. '/course/view.php?id=' . $coursetogoto;
    redirect($url, $messagetext);
} else {
    redirect($url, get_string('enrollment_disabled_text', 'block_teacherlinks'));
}