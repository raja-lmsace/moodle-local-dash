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

namespace dashaddon_dashboard\local\hooks;

/**
 * Helper class for hooks. Resets the cache for hook callbacks.
 *
 * @package    dashaddon_dashboard
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Clears the hook cache and optionally recreates it.
     *
     * This function clears the cache for hook callbacks and optionally recreates it
     * if the specified conditions are met. If the `$create` parameter is set to `false`,
     * it checks if the `dashaddon_dashboard_dash` table exists and if there is a record
     * with `redirecttodashboard` set to `true` and `permission` set to `public`. If these
     * conditions are not met, the function returns `false`.
     *
     * @param bool $create Optional. Whether to recreate the cache after clearing it. Default is `false`.
     * @return bool Returns `false` if the cache is not recreated, otherwise no return value.
     */
    public function clear_hook_cache($create = false) {
        global $DB;
        $dbman = $DB->get_manager();

        if (!$create) {
            if (
                !$dbman->table_exists('dashaddon_dashboard_dash') ||
                !$DB->record_exists('dashaddon_dashboard_dash', ['redirecttodashboard' => true, 'permission' => 'public'])
            ) {
                return false;
            }
        }

        if (method_exists(\core\hook\manager::class, 'get_cache')) {

            $cache = $this->get_cache();

            $callbacks = $cache['callbacks'] ?? null;
            $deprecations = $cache['deprecations'] ?? null;
            $hash = $cache['overrideshash'] ?? null;

            clearstatcache(true, $this->get_cache_path());

            $this->set_cache($callbacks, $deprecations, $hash);

            return true;

        } else if (\core_cache\config::instance()->get_definition_by_id('core/hookcallbacks')) {

            $cache = \cache::make('core', 'hookcallbacks');
            // Remove the event callbacks and recreate.
            $cache->delete('callbacks');

            // Build the callbacks again.
            $hookmanager = \core\hook\manager::get_instance();
            $allhooks = $hookmanager->get_all_callbacks();

            $cache->set('callbacks', $allhooks);

            return true;

        }

        return false;
    }

    /**
     * Get the path to the hook cache.
     *
     * @return string
     */
    protected function get_cache_path(): string {
        return make_localcache_directory('') . '/hookcallbacks.json';
    }

    /**
     * Fetch and decode the hook cache.
     *
     * @return array|null
     */
    protected function get_cache(): ?array {
        $cachepath = $this->get_cache_path();
        if (!file_exists($cachepath)) {
            return null;
        }
        return json_decode(file_get_contents($cachepath), true) ?? [];
    }

    /**
     * Store all relevant data in the cache.
     *
     * Extend from core\hook\manager to be able to set the cache directly if the methods are available, otherwise write to the file cache.
     *
     * @param array $callbacks
     * @param array $deprecations
     * @param string|null $hash
     */
    protected function set_cache(
        array $callbacks,
        array $deprecations,
        ?string $hash,
    ): void {
        $cachedata = [
            'callbacks' => $callbacks,
            'deprecations' => $deprecations,
            'overrideshash' => $hash,
        ];

        // Write to a temp file and rename it to ensure atomicity of reads.
        // If we write directly to the cache file, another process may read it during the write and get corrupted data.
        $cachepath = $this->get_cache_path();
        $tmppath = "{$cachepath}." . uniqid('tmp', true);

        file_put_contents($tmppath, json_encode($cachedata));
        rename($tmppath, $cachepath);
        clearstatcache(true, $cachepath);
    }
}
