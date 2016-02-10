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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package local_analytics
 * @copyright David Bezemer <info@davidbezemer.nl>, www.davidbezemer.nl
 * @author David Bezemer <info@davidbezemer.nl>, Bas Brands <bmbrands@gmail.com>, Gavin Henrick <gavin@lts.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('local_analytics_interface.php');

class local_analytics_guniversal implements local_analytics_interface {
    static public function trackurl() {
        global $DB, $PAGE, $COURSE;
        $pageinfo = get_context_info_array($PAGE->context->id);
        $trackurl = "'/";

        // Adds course category name.
        if (isset($pageinfo[1]->category)) {
            if ($category = $DB->get_record('course_categories', array (
                    'id' => $pageinfo[1]->category
            ))) {
                $cats = explode("/", $category->path);
                foreach (array_filter($cats) as $cat) {
                    if ($categorydepth = $DB->get_record("course_categories", array (
                            "id" => $cat
                    ))) {
                        ;
                        $trackurl .= urlencode($categorydepth->name) . '/';
                    }
                }
            }
        }

        // Adds course full name.
        if (isset($pageinfo[1]->fullname)) {
            if (isset($pageinfo[2]->name)) {
                $trackurl .= urlencode($pageinfo[1]->fullname) . '/';
            } else if ($PAGE->user_is_editing()) {
                $trackurl .= urlencode($pageinfo[1]->fullname) . '/' . get_string('edit', 'local_analytics');
            } else {
                $trackurl .= urlencode($pageinfo[1]->fullname) . '/' . get_string('view', 'local_analytics');
            }
        }

        // Adds activity name.
        if (isset($pageinfo[2]->name)) {
            $trackurl .= urlencode($pageinfo[2]->modname) . '/' . urlencode($pageinfo[2]->name);
        }

        $trackurl .= "'";
        return $trackurl;
    }
    static public function insert_tracking() {
        global $CFG, $PAGE;
        $enabled = get_config('local_analytics', 'enabled');
        $siteid = get_config('local_analytics', 'siteid');
        $trackadmin = get_config('local_analytics', 'trackadmin');
        $cleanurl = get_config('local_analytics', 'cleanurl');
        $location = "additionalhtml" . get_config('local_analytics', 'location');

        if ($cleanurl) {
            $addition = "{'hitType' : 'pageview',
                'page' : " . self::trackurl() . ",
                'title' : '" . addslashes($PAGE->heading) . "'
                }";
        } else {
            $addition = "'pageview'";
        }

        if ($enabled && (!is_siteadmin() || $trackadmin)) {
            $CFG->$location .= "
                <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                ga('create', '" . $siteid . "', {'siteSpeedSampleRate': 50});
                ga('send', " . $addition . ");

                </script>";
        }
    }
}