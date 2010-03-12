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
 * TODO
 *
 * @package   local-amos
 * @copyright 2010 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

require_login(SITEID, false);
require_capability('moodle/site:config', $PAGE->context);

$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/amos/view.php');
$PAGE->set_title('AMOS');
$PAGE->set_heading('AMOS');
//$PAGE->requires->js_init_call('M.local_amos.init_translator', array('container' => 'translator-wrapper', 'datafeed' => 'waitress.php'), true);

$output = $PAGE->get_renderer('local_amos');

/// Output starts here
echo $output->header();

// create a renderable object that represents the filter
$filter = new local_amos_filter($PAGE->url);
$fdata = $filter->get_data();

// create a renderable object that represent the translation table
echo $output->render($filter);

print_object($fdata); // DONOTCOMMIT
echo $output->footer();
