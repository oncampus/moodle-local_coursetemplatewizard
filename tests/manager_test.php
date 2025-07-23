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
 * @package    ocbsbcoursecreation
 * @author     Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @copyright  2022 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_ocbsbcoursecreation\manager;

class local_ocbsbcoursecreation_manager_test extends \advanced_testcase {

    /**
     * Initial setup.
     * By Installation default
     *
     * $record_type1->type = "Semester";
     * $record_type1->rank = 1;
     * $record_type1->id = 1;
     *
     * $record_type2->type = "Year";
     * $record_type2->rank = 2;
     * $record_type2->id = 2;
     *
     * $record_type1_value1->string = WiSe;
     * $record_type1_value1->type_id = $record_type1->id;
     *
     * $record_type1_value2->string = SoSe;
     * $record_type1_value2->type_id = $record_type1->id;
     *
     * $record_type2_value1->string = date("y");
     * $record_type2_value1->type_id = $record_type2->id;
     *
     * $record_type2_value2->string = date("y") +1;
     * $record_type2_value2->type_id = $record_type2->id;
     *
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_get_last_type_by_rank() {
        $manager = new manager();
        $type    = $manager->get_last_type_by_rank();
        $manager->create_type("testtype");
        $new_type = $manager->get_last_type_by_rank();

        $this->assertNotEmpty($type);
        $this->assertNotEmpty($new_type);
        $this->assertNotEquals($type, $new_type);
        $this->assertGreaterThan($type->rank, $new_type->rank); // new rank is greater than old
    }

    public function test_get_types_higher_and_equal_rank() {
        $manager = new manager();
        $types   = $manager->get_types_higher_and_equal_rank(0);
        $types2  = $manager->get_types_higher_and_equal_rank(1);
        $types3  = $manager->get_types_higher_and_equal_rank(2);
        $types4  = $manager->get_types_higher_and_equal_rank(3);
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

        $this->assertEquals($type_rank1->rank, $switched_type_rank2->rank, "1");
        $this->assertEquals($type_rank2->rank, $switched_type_rank1->rank, "2");
        $this->assertEquals($type_rank1->type, $switched_type_rank1->type, "3");
        $this->assertEquals($type_rank2->type, $switched_type_rank2->type, "4");
    }

    public function test_update_delete_sort() {
        $manager = new manager();
        $this->assertEquals($manager->get_type_by_rank(1)->id, 1); // inital assertion claims type rank 1 has id 1
        $manager->update_delete_sort(1);
        $this->assertFalse($manager->get_type_by_id(1));         // type with id 1 does not exist
        $this->assertNotNull($manager->get_type_by_rank(1)->id); // type with rank one exists

        $manager->create_type("testtype");
        $this->assertEquals($manager->get_type_by_rank(2)->rank, 2);

        $manager->create_type("testtype2");
        $this->assertEquals($manager->get_type_by_rank(3)->rank, 3);

        $manager->create_type("testtype3");
        $this->assertEquals($manager->get_type_by_rank(4)->rank, 4);

        $manager->create_type("testtype4");
        $this->assertEquals($manager->get_type_by_rank(5)->rank, 5);

        $manager->create_type("testtype5");
        $this->assertEquals($manager->get_type_by_rank(6)->rank, 6);

        $types_pre_del = $manager->get_all_types();
        $i             = 0;
        while (!$manager->update_delete_sort($i)) {
            $i++;
        }
        $types = $manager->get_all_types();
        $this->assertGreaterThan(count($types), count($types_pre_del));

        $types = $manager->get_all_types();
        $types = array_values($types);
        for ($i = 1, $iMax = count($types); $i < $iMax && 1 < $iMax; $i++) {
            $this->assertGreaterThan($types[$i - 1]->rank, $types[$i]->rank);
        }

    }

    public function test_delete_value() {
        $manager        = new manager();
        $values         = $manager->get_all_values();
        $values_pre_del = count($values);
        $id             = $values[array_key_first($values)]->id;
        $type_id        = $values[array_key_first($values)]->type_id;
        $manager->delete_value($id);

        $values = $manager->get_all_values();
        $this->assertGreaterThan(count($values), $values_pre_del);
        $this->assertFalse($manager->get_value_by_id($id));

        $manager->get_all_values();
        foreach ($values as $value) {
            if ($value->type_id === $type_id) {
                $manager->delete_value($value->id);
            }
        }
        global $DB;
        $this->assertEquals(count($DB->get_records('ocbsbcoursecreation_value', ['type_id' => $type_id])), 0);
        $this->assertFalse($manager->get_type_by_id($type_id));
    }

    public function test_create_value_and_type() {
        $manager      = new manager();
        $values_count = count($manager->get_all_values());
        $types_count  = count($manager->get_all_types());
        $this->assertFalse($manager->create_value_and_type("", ""));
        $this->assertFalse($manager->create_value_and_type("test", ""));
        $this->assertFalse($manager->create_value_and_type("", "test"));
        $this->assertTrue($manager->create_value_and_type("test", "test"));//4

        $this->assertEquals($values_count + 1, count($manager->get_all_values()));
        $this->assertEquals($types_count + 1, count($manager->get_all_types()));
    }

}
