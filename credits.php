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
 * Displays maintainers and contributors per language
 *
 * @package   local_amos
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/local/amos/locallib.php');
require_once($CFG->dirroot.'/local/amos/mlanglib.php');
require_once($CFG->dirroot.'/local/amos/renderer.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/amos/credits.php');
$PAGE->set_title(get_string('creditstitleshort', 'local_amos'));
$PAGE->set_heading(get_string('creditstitleshort', 'local_amos'));

$languages = mlang_tools::list_languages(false, true, false);

// Get the list of known languages.

foreach ($languages as $langcode => $langname) {
    $list[$langcode] = (object)array('langname' => $langname, 'maintainers' => array(), 'contributors' => array());
}

// Get the list of assigned maintainers.

$userfields = user_picture::fields('u');
list($sortsql, $sortparams) = users_order_by_sql('u');

$sql = "SELECT t.lang, {$userfields}
          FROM {amos_translators} t
          JOIN {user} u ON t.userid = u.id
         WHERE t.lang <> 'X' and t.lang <> 'en'
      ORDER BY {$sortsql}";

$rs = $DB->get_recordset_sql($sql, $sortparams);

foreach ($rs as $maintainer) {
    $lang = $maintainer->lang;
    unset($maintainer->lang);
    if (empty($list[$lang])) {
        debugging('Unknown language ' . $lang, DEBUG_DEVELOPER);
        continue;
    }
    $list[$lang]->maintainers[$maintainer->id] = $maintainer;
}

$rs->close();

// Get the list of users who contributed to a language pack.

$sql = "SELECT c.lang, {$userfields}, COUNT(*) AS numofcontributions
          FROM {amos_contributions} c
          JOIN {user} u ON c.authorid = u.id
         WHERE c.status = :status
      GROUP BY c.lang, {$userfields}
        HAVING COUNT(*) >= 3
      ORDER BY {$sortsql}";

$rs = $DB->get_recordset_sql($sql, array_merge($sortparams,
    array('status' => local_amos_contribution::STATE_ACCEPTED)));

foreach ($rs as $contributor) {
    $lang = $contributor->lang;
    unset($contributor->lang);
    if (empty($list[$lang])) {
        debugging('Unknown language ' . $lang, DEBUG_DEVELOPER);
        continue;
    }
    if (isset($list[$lang]->maintainers[$contributor->id])) {
        debugging('Already in maintainers: '.$contributor->id, DEBUG_DEVELOPER);
        continue;
    }
    $list[$lang]->contributors[$contributor->id] = $contributor;
}

$rs->close();

// Output starts here
echo $OUTPUT->header();

$output = $PAGE->get_renderer('local_amos');
echo $output->page_credits($list, current_language());
echo $OUTPUT->footer();