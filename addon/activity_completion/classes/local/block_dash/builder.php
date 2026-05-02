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
 * Builds a query.
 *
 * @package   dashaddon_activity_completion
 * @copyright 2025 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local\dash\addon\activity_completion\local\block_dash;

/**
 * Custom builder to add two step query to handle large data sets.
 */
class builder extends \block_dash\local\dash_framework\query_builder\builder {
    /**
     * Get number of records this query will return.
     *
     * @param  int $isunique Counted by unique id.
     * @return int
     * @throws dml_exception
     * @throws exception\invalid_operator_exception
     */
    public function count($isunique): int {
        global $DB;

        $builder = clone $this;

        [$wheres, $whereparams] = $this->get_where_sql_and_params();
        // Copy the selects.
        $originalselects = $builder->selects;
        $joins = $builder->joins;
        $rawjoins = $builder->rawjoins;

        foreach ($builder->rawjoins as $key => $join) {
            if (strpos($wheres, $join->get_alias()) === false) {
                unset($builder->rawjoins[$key]);
            }
        }

        if (strpos($wheres, 'c_status') !== false) {
            $builder->selects['c_status'] = $originalselects['c_status'] ?? '';
        }

        if (strpos($wheres, 'profile.') === false) {
            $builder->remove_join('profile');

            if (strpos($wheres, 'u.') === false) {
                $builder->remove_join('u');
            }
        }

        if (strpos($wheres, 'cmc.') === false) {
            $builder->remove_join('cmc');
        }

        if (strpos($wheres, 'gg.') === false) {
            if (strpos($wheres, 'gt.') === false) {
                $builder->remove_join('gt');
            }
            $builder->remove_join('gg');
        }

        if (strpos($wheres, 'cc.') === false) {
            $builder->remove_join('cc');
        }

        if (strpos($wheres, 'cs.') === false) {
            $builder->remove_join('cs');
        }

        if (strpos($wheres, 'mds.') === false) {
            if (strpos($wheres, 'm.') === false) {
                $builder->remove_join('m');
            }

            $builder->remove_join('mds');
        }

        if ($isunique) {
            $builder->set_selects(
                ['count' => 'COUNT(DISTINCT ' . $DB->sql_concat_join("'-'", ['cm.id', 'ue.userid']) . ')']
            );
        } else {
            $builder->set_selects(['count' => 'COUNT(DISTINCT ' . $this->tablealias . '.id)']);
        }

        $builder->limitfrom(0)->limitnum(0)->remove_orderby();
        [$sql, $params] = $builder->get_sql_and_params();
        $sql = (string) $sql;

        $countcachekey = md5($sql . serialize($params));

        if (self::$lastcount !== null) {
            if (self::$lastcountcachekey == $countcachekey) {
                return self::$lastcount;
            }
        }

        self::$lastcountcachekey = $countcachekey;

        $count = 0;
        $courses = $DB->get_fieldset_select('course', 'id', 'id > 0');
        foreach ($courses as $courseid) {
            $courseparams = $params;
            $courseparams['cm_course_id'] = $courseid;
            $count += $DB->count_records_sql($sql . ' AND cm.course = :cm_course_id', $courseparams);
        }

        self::$lastcount = $count;

        return $count;
    }

    /**
     * Remove a join by alias.
     *
     * @param  string $alias
     * @return ?join
     */
    public function remove_join(string $alias) {

        foreach ($this->joins as $key => $join) {
            if ($join->get_alias() === $alias) {
                $newjoin = clone $this->joins[$key];
                unset($this->joins[$key]);
            }
        }

        foreach ($this->rawjoins as $key => $join) {
            if ($join->get_alias() === $alias) {
                $newjoin = clone $this->rawjoins[$key];
                unset($this->rawjoins[$key]);
            }
        }

        return $newjoin ?? null;
    }

    /**
     * Execute the query and return the results.
     *
     * @return array
     */
    public function query() {
        global $DB;

        // There where clause conditions.
        [$wheres, $whereparams] = $this->get_where_sql_and_params();
        $orderby = implode(' ', array_keys($this->orderby));

        // Copy the selects.
        $originalselects = $this->selects;
        $joins = $this->joins;
        $rawjoins = $this->rawjoins;
        foreach ($this->rawjoins as $key => $join) {
            if (strpos($wheres, $join->get_alias()) === false && strpos($orderby, $join->get_alias() . '.') === false) {
                unset($this->rawjoins[$key]);
            }
        }

        $this->selects = [
            'unique_id' => $DB->sql_concat_join("'-'", ['cm.id', 'ue.userid']),
            'cm_id' => 'cm.id',
            'ue_userid' => 'ue.userid',
        ];

        if (strpos($wheres, 'c_status') !== false || strpos($orderby, 'c_status') !== false) {
            $this->selects['c_status'] = $originalselects['c_status'] ?? '';
        }

        if (strpos($wheres, 'profile.') === false && strpos($orderby, 'profile.') === false) {
            $this->remove_join('profile');

            if (strpos($wheres, 'u.') === false && strpos($orderby, 'u.') === false) {
                $this->remove_join('u');
            }
        }

        if (strpos($wheres, 'cmc.') === false && strpos($orderby, 'cmc.') === false) {
            $this->remove_join('cmc');
        }

        if (strpos($wheres, 'gg.') === false && strpos($orderby, 'gg.') === false) {
            if (strpos($wheres, 'gt.') === false && strpos($orderby, 'gt.') === false) {
                $this->remove_join('gt');
            }
            $this->remove_join('gg');
        }

        if (strpos($wheres, 'cc.') === false && strpos($orderby, 'cc.') === false) {
            $this->remove_join('cc');
        }

        if (strpos($wheres, 'cs.') === false && strpos($orderby, 'cs.') === false) {
            $this->remove_join('cs');
        }

        if (strpos($wheres, 'mds.') === false && strpos($orderby, 'mds.') === false) {
            if (strpos($wheres, 'm.') === false && strpos($orderby, 'm.') === false) {
                $this->remove_join('m');
            }

            $this->remove_join('mds');
        }

        [$sql, $params] = $this->get_sql_and_params();
        $pairs = [];
        $all = [];
        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $row) {
            $key = $row->cm_id . '-' . $row->ue_userid;
            if (!isset($pairs[$key])) {
                $pairs[$key] = true;
                $all[] = $row;
            }
        }
        $recordset->close();

        $limitnum = $this->get_limitnum();
        $results = array_slice($all, $this->get_limitfrom(), $limitnum ?: null);
        unset($all, $pairs);

        if (empty($results)) {
            return [];
        }

        // Restore the selects and joins.
        $this->selects = $originalselects;
        $this->selects['unique_id'] = $DB->sql_concat_join("'-'", ['cm.id', 'ue.userid']);
        $this->joins = $joins;
        $this->rawjoins = $rawjoins;
        $conditions = [];
        $inparams = [];
        foreach ($results as $i => $row) {
            $conditions[] = '(cm.id = :sqcmid' . $i . ' AND u.id = :squserid' . $i . ')';
            $inparams['sqcmid' . $i] = (int) $row->cm_id;
            $inparams['squserid' . $i] = (int) $row->ue_userid;
        }
        $this->rawwhere[] = '(' . implode(' OR ', $conditions) . ')';
        [$sql, $params] = $this->get_sql_and_params();
        $params = array_merge($params, $inparams);
        $visited = [];
        $pagerows = [];
        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $row) {
            $key = $row->cm_id . '-' . $row->u_id;
            if (!isset($visited[$key])) {
                $visited[$key] = true;
                $pagerows[] = $row;
            }
        }
        $recordset->close();

        return $pagerows;
    }
}
