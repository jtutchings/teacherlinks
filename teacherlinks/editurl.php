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

/**
 * Script to let a user edit the properties of a particular RSS feed.
 *
 * @package   moodlecore
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

class teacherlinks_edit_form extends moodleform {
    protected $isadding;
    protected $caneditshared;
    protected $displaytext = '';
    protected $url = '';
    protected $visible = 1;

    public function __construct($actionurl, $isadding) {
        $this->isadding = $isadding;
        parent::__construct($actionurl);
    }

    public function definition() {
        $mform =& $this->_form;

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'directedurleditheader', get_string('editurlheader', 'block_teacherlinks'));

        $mform->addElement('text', 'displaytext', get_string('displaytextlabel', 'block_teacherlinks'), array('size' => 60));
        $mform->setType('displaytext', PARAM_TEXT);
        $mform->addRule('displaytext', null, 'required');

        $mform->addElement('text', 'url', get_string('urllabel', 'block_teacherlinks'), array('size' => 60));
        $mform->setType('url', PARAM_RAW);

        $mform->addRule('url', null, 'required');
        $mform->addHelpButton('url', 'url', 'block_teacherlinks');

        $mform->addElement('checkbox', 'visible', get_string('visiblelabel', 'block_teacherlinks'));

        $mform->setDefault('visible', 1);

        $mform->addElement('checkbox', 'iframe', get_string('iframelabel', 'block_teacherlinks'));
        $mform->setDefault('iframe', 1);
        $mform->addHelpButton('iframe', 'iframe', 'block_teacherlinks');

        $mform->addElement('checkbox', 'md5', get_string('md5label', 'block_teacherlinks'));
        $mform->setDefault('md5', 0);
        $mform->addHelpButton('md5', 'md5', 'block_teacherlinks');

        $mform->addElement('text', 'sharedsecret', get_string('sharedsecretlabel', 'block_teacherlinks'), array('size' => 60));
        $mform->setType('sharedsecret', PARAM_TEXT);
        $mform->addHelpButton('sharedsecret', 'sharedsecret', 'block_teacherlinks');

        $submitlabal = null; // Default.
        if ($this->isadding) {
            $submitlabal = get_string('addnewurl', 'block_teacherlinks');
        }
        $this->add_action_buttons(true, $submitlabal);
    }

    public function definition_after_data() {
        $mform =& $this->_form;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            $this->url = $data->url;
            if (!isset($data->visible)) {
                $data->visible = 0;
            }
            if (!isset($data->md5)) {
                $data->md5 = 0;
            }
            if ( !isset($data->iframe)) {
                $data->iframe = 0;
            }
        }
        return $data;
    }

}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INTEGER);
$urlid = optional_param('urlid', 0, PARAM_INTEGER); // 0 mean create new.

if ($courseid == SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

$urlparams = array('urlid' => $urlid);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

$managelinks = new moodle_url('/blocks/teacherlinks/manageurls.php', $urlparams);

$PAGE->set_url('/blocks/teacherlinks/editurl.php', $urlparams);
$PAGE->set_pagelayout('base');

if ($urlid) {
    $isadding = false;
    $rssrecord = $DB->get_record('block_teacherlinks', array('id' => $urlid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $rssrecord = new stdClass;
}

$mform = new teacherlinks_edit_form($PAGE->url, $isadding);
$mform->set_data($rssrecord);

if ($mform->is_cancelled()) {
    redirect($managelinks);

} else if ($data = $mform->get_data()) {
    $data->userid = $USER->id;


    if ($isadding) {
        $maxsort = $DB->get_field('block_teacherlinks', 'max(sortorder)', array());
        if ($maxsort) {
            $data->sortorder = $maxsort + 1;
        } else {
            $data->sortorder = 1;
        }



        $DB->insert_record('block_teacherlinks', $data);
    } else {
        $data->id = $urlid;
        $DB->update_record('block_teacherlinks', $data);
    }

    redirect($managelinks);

} else {
    if ($isadding) {
        $strtitle = get_string('addnewurl', 'block_teacherlinks');
    } else {
        $strtitle = get_string('editaurl', 'block_teacherlinks');
    }

    $PAGE->set_title($strtitle);
    $PAGE->set_heading($strtitle);

    $settingsurl = new moodle_url('/admin/settings.php?section=blocksettingteacherlinks');
    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_teacherlinks'), $settingsurl);
    $PAGE->navbar->add(get_string('manageurls', 'block_teacherlinks'), $managelinks);
    $PAGE->navbar->add($strtitle);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);

    $mform->display();

    echo $OUTPUT->footer();
}