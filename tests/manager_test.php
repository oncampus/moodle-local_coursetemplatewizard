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
 * Functional test for class course_image
 *
 * @package    oc_course_creation
 * @author     Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @copyright  2022 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_oc_course_creation\manager;

class local_oc_course_creation_manager_test extends \advanced_testcase {

    /**
     * Initial setup.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_get_last_type_by_rank() {
        $manager = new manager();
        $type = $manager->get_last_type_by_rank();
        $manager->create_type("testtype");
        $new_type = $manager->get_last_type_by_rank();

        $this->assertNotEmpty($type);
        $this->assertNotEmpty($new_type);
        $this->assertNotEquals($type, $new_type);
        $this->assertGreaterThan($type->rank, $new_type->rank); // new rank is greater than old
    }

    public function test_get_types_higher_and_equal_rank() {
        $manager = new manager();
        $types = $manager->get_types_higher_and_equal_rank(0);
        $types2 = $manager->get_types_higher_and_equal_rank(1);
        $types3 = $manager->get_types_higher_and_equal_rank(2);
        $types4 = $manager->get_types_higher_and_equal_rank(3);
        $this->assertCount(2, $types);
        $this->assertCount(2, $types2);
        $this->assertCount(1, $types3);
        $this->assertCount(0, $types4);
    }

    public function test_swap() {
        $manager = new manager();
        $this->assertFalse($manager->swap(0, 1));
        $this->assertFalse($manager->swap(2, 3));

        $type_rank1 = $manager->get_type_by_rank(1);
        $type_rank2 = $manager->get_type_by_rank(2);

        $this->assertTrue($manager->swap(1, 2));

        $switched_type_rank1 = $manager->get_type_by_id($type_rank1->id);
        $switched_type_rank2 = $manager->get_type_by_id($type_rank2->id);

        $this->assertEquals($type_rank1->rank, $switched_type_rank2->rank,"1");
        $this->assertEquals($type_rank2->rank, $switched_type_rank1->rank,"2");
        $this->assertEquals($type_rank1->type,$switched_type_rank1->type,"3");
        $this->assertEquals($type_rank2->type,$switched_type_rank2->type,"4");
    }

    public function test_update_delete_sort() {
        $manager = new manager();
    }

    public function test_delete_value() {
        $manager = new manager();
    }

    public function test_create_value_and_type() {
        $manager = new manager();
    }

}
