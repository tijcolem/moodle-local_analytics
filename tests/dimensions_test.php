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
 * Analytics tests for dimensions infrastructure.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics;

use local_analytics\dimension;

defined('MOODLE_INTERNAL') || die();

/**
 * Class local_analytics_dimensions_testcase
 */
class local_analytics_dimensions_testcase extends \advanced_testcase
{

    /**
     * Setup test data.
     */
    public function setUp()
    {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

// 		set_config('location', 'header', 'local_analytics');
    }

    /**
     * Test that instantiate_plugins can instantiate all plugins.
     *
     * GIVEN the dimensions class
     * WHEN its instantiate_plugins function is invoked
     * THEN the result should be an array of plugin instances
     *
     * @test
     */
    public function instantiatePluginsReturnsArrayOfPlugins()
    {
        $plugins = dimensions::instantiate_plugins();

        foreach ($plugins as $scope => $scope_plugins) {
            foreach ($scope_plugins as $name => $plugin) {
                $this->assertInstanceOf($name, $plugin);
            }
        }
    }

    /**
     * Test that instantiated plugins have expected attributes.
     *
     * GIVEN the array of plugin instances returned by instantiate_plugins
     * WHEN each is checked
     * THEN it should implement the dimension interface.
     *
     * @test
     */
    public function instantiatedPluginsImplementInterface()
    {
        $plugins = dimensions::instantiate_plugins();

        foreach ($plugins as $scope => $scope_plugins) {
            foreach ($scope_plugins as $name => $plugin) {
                $this->assertTrue($plugin instanceOf \local_analytics\dimension\dimension_interface, "The ${name} plugin doesn't
                implement the dimension interface");
            }
        }
    }

    /**
     * Test that setting_options correctly returns the setting options for a scope.
     *
     * Note that this test uses the real plugins and will need to be updated when new
     * plugins are added. If you're updating this list, you should also be modifying
     * tests for dimension plugins.
     *
     * GIVEN a file in the plugins directory not having a class that matches the filename
     * WHEN the instantiate_plugins function tries to use it
     * THEN it should generate a debugging message about the issue.
     *
     * @test
     */
    public function settingOptionsReturnsExpectedValues()
    {
        $actual = dimensions::setting_options('visit');
        $expected = array(
            '' => '',
            'is_on_bundoora_campus' => 'User is on Bundoora campus network',
            'is_on_campus' => 'User is on campus',
            'user_department' => 'User department',
            'user_email_domain' => 'User email domain',
            'user_institution' => 'User institution',
            'user_name' => 'User name',
            'user_profile_field_faculty_cost_code' => 'Faculty cost code user profile field',
        );

        $this->assertSame($expected, $actual);

        $actual = dimensions::setting_options('action');
        $expected = array (
            '' => '',
            'context' => 'Context',
            'course_category_hierarchy_full_path' => 'Course category hierarchy full path',
            'course_enrolment_method' => 'Course enrolment method',
            'course_full_name' => 'Course full name',
            'course_id_number' => 'Course ID number',
            'course_short_name' => 'Course short name',
            'user_role' => 'User role',
        );

        $this->assertSame($expected, $actual);
    }

}