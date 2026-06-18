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
 * Category mega-filter for Dash.
 *
 * @module     local_dash/category_mega_filter
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    /** @type {boolean} */
    var delegated = false;

    /** CSS selectors used throughout this module. */
    var SELECTOR = {
        wrap: '.dash-cat-mega-wrap',
        label: '.dash-cat-label',
        megaCol: '.dash-mega-col',
        hiddenSel: '.dash-cat-hidden-select',
        drawer: '.dash-cat-drawer',
        drawerPanel: '.dash-cat-drawer-panel',
        drawerClose: '.dash-cat-drawer-close',
        mobileOpen: '.dash-cat-mobile-open',
        mobileList: '.dash-mobile-list',
        mobileTitle: '.dash-mobile-title',
        mobileBack: '.dash-mobile-back',
        clearBtn: '.dash-cat-clear',
    };

    /**
     * Get the category tree stored on a wrap element.
     *
     * @param {HTMLElement} wrap
     * @return {Array}
     */
    function getTree(wrap) {
        try {
            return JSON.parse(wrap.dataset.tree) || [];
        } catch (error) {
            return [];
        }
    }

    /**
     * Return the column element at index within wrap, creating it if it does not exist yet.
     *
     * @param {HTMLElement} wrap
     * @param {number} index
     * @return {HTMLElement}
     */
    function ensureColumn(wrap, index) {
        var columns = wrap.querySelectorAll(SELECTOR.megaCol);
        if (index < columns.length) {
            return columns[index];
        }
        var container = wrap.querySelector('.dash-mega-cols');

        var column = document.createElement('div');
        column.className = 'dash-mega-col dash-mega-col-empty';
        column.dataset.level = String(index + 1);

        var columnList = document.createElement('ul');
        columnList.className = 'list-unstyled m-0 p-0';
        columnList.setAttribute('role', 'menu');
        column.appendChild(columnList);
        container.appendChild(column);
        return column;
    }

    /**
     * Render a list of category nodes into a desktop column.
     *
     * @param {HTMLElement} wrap
     * @param {HTMLElement} listElement
     * @param {HTMLElement} columnElement
     * @param {Array} items
     * @param {number} level
     */
    function buildColumn(wrap, listElement, columnElement, items, level) {
        listElement.innerHTML = '';

        if (!items || items.length === 0) {
            columnElement.classList.add('dash-mega-col-empty');
            return;
        }
        columnElement.classList.remove('dash-mega-col-empty');

        items.forEach(function(category) {
            var listItem = document.createElement('li');
            listItem.setAttribute('role', 'menuitem');
            listItem.dataset.catId = category.id;

            var nameSpan = document.createElement('span');
            nameSpan.className = 'dash-cat-item-name';
            nameSpan.textContent = category.name;
            listItem.appendChild(nameSpan);

            if (category.has_children) {
                var chevronIcon = document.createElement('i');
                chevronIcon.className = 'fa fa-chevron-right dash-cat-chevron';
                chevronIcon.setAttribute('aria-hidden', 'true');
                listItem.appendChild(chevronIcon);
            }

            listItem.addEventListener('mouseenter', function() {
                listElement.querySelectorAll('li').forEach(function(sibling) {
                    sibling.classList.remove('active');
                });
                listItem.classList.add('active');
                var nextColumn = ensureColumn(wrap, level);
                buildColumn(wrap, nextColumn.querySelector('ul'), nextColumn, category.children || [], level + 1);
                var columns = wrap.querySelectorAll(SELECTOR.megaCol);
                for (var i = level + 1; i < columns.length; i++) {
                    buildColumn(wrap, columns[i].querySelector('ul'), columns[i], [], i + 1);
                }
            });

            listItem.addEventListener('click', function(event) {
                event.stopPropagation();
                applyFilter(wrap, category.id, category.name);
                closeDropdown(wrap);
            });

            listElement.appendChild(listItem);
        });
    }

    /**
     * Build the desktop columns from the tree stored on the wrap.
     *
     * @param {HTMLElement} wrap
     */
    function buildDesktopColumns(wrap) {
        var tree = getTree(wrap);
        var columns = wrap.querySelectorAll(SELECTOR.megaCol);
        if (columns.length >= 1) {
            buildColumn(wrap, columns[0].querySelector('ul'), columns[0], tree, 1);
            for (var i = 1; i < columns.length; i++) {
                buildColumn(wrap, columns[i].querySelector('ul'), columns[i], [], i + 1);
            }
        }
    }

    /**
     * Close the Bootstrap dropdown inside this wrap.
     *
     * @param {HTMLElement} wrap
     */
    function closeDropdown(wrap) {
        var dropdownButton = wrap.querySelector('[data-bs-toggle="dropdown"]')
            || wrap.querySelector('[data-toggle="dropdown"]');
        if (!dropdownButton) {
            return;
        }
        if (window.bootstrap && window.bootstrap.Dropdown) {
            var dropdownInstance = window.bootstrap.Dropdown.getInstance(dropdownButton);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        } else if (window.jQuery) {
            window.jQuery(dropdownButton).dropdown('hide');
        }
    }

    /**
     * Open the custom drawer for the given wrap.
     *
     * @param {HTMLElement} wrap
     */
    function openDrawer(wrap) {
        var drawerElement = wrap.querySelector(SELECTOR.drawer);
        if (!drawerElement) {
            return;
        }

        var titleElement = wrap.querySelector(SELECTOR.mobileTitle);
        var labelElement = wrap.querySelector(SELECTOR.label);
        if (titleElement && labelElement) {
            titleElement.textContent = labelElement.dataset.defaultLabel
                || wrap.dataset.labelall
                || labelElement.textContent;
        }

        var backButton = wrap.querySelector(SELECTOR.mobileBack);
        if (backButton) {
            backButton.style.display = 'none';
        }

        var stack = [];
        drawerElement._dashStack = stack;
        buildMobileList(wrap, getTree(wrap), stack);

        drawerElement.setAttribute('aria-hidden', 'false');
        drawerElement.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Close the custom drawer.
     *
     * @param {HTMLElement} wrap
     * @param {Function} [callback]
     */
    function closeDrawer(wrap, callback) {
        var drawerElement = wrap.querySelector(SELECTOR.drawer);
        if (!drawerElement || !drawerElement.classList.contains('show')) {
            if (callback) {
                callback();
            }
            return;
        }

        drawerElement.classList.remove('show');
        drawerElement.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (!callback) {
            return;
        }

        var panel = drawerElement.querySelector(SELECTOR.drawerPanel);
        if (!panel) {
            callback();
            return;
        }

        var fired = false;
        var finish = function() {
            if (fired) {
                return;
            }
            fired = true;
            panel.removeEventListener('transitionend', finish);
            callback();
        };
        panel.addEventListener('transitionend', finish);
        setTimeout(finish, 400);
    }

    /**
     * Render categories into the mobile list.
     *
     * @param {HTMLElement} wrap
     * @param {Array} items
     * @param {Array} stack Navigation history for the back button.
     */
    function buildMobileList(wrap, items, stack) {
        var mobileListElement = wrap.querySelector(SELECTOR.mobileList);
        mobileListElement.innerHTML = '';

        items.forEach(function(category) {
            var listItem = document.createElement('li');
            listItem.setAttribute('role', 'menuitem');
            listItem.dataset.catId = category.id;

            var nameSpan = document.createElement('span');
            nameSpan.textContent = category.name;
            listItem.appendChild(nameSpan);

            if (category.has_children) {
                var chevronIcon = document.createElement('i');
                chevronIcon.className = 'fa fa-chevron-right dash-cat-chevron';
                chevronIcon.setAttribute('aria-hidden', 'true');
                listItem.appendChild(chevronIcon);

                chevronIcon.addEventListener('click', function(event) {
                    event.stopPropagation();
                    var titleElement = wrap.querySelector(SELECTOR.mobileTitle);
                    stack.push({items: items, title: titleElement ? titleElement.textContent : ''});
                    if (titleElement) {
                        titleElement.textContent = category.name;
                    }
                    var backButton = wrap.querySelector(SELECTOR.mobileBack);
                    if (backButton) {
                        backButton.style.display = '';
                    }
                    buildMobileList(wrap, category.children || [], stack);
                });
            }

            listItem.addEventListener('click', function() {
                requestCloseAndFilter(wrap, category.id, category.name);
            });

            mobileListElement.appendChild(listItem);
        });
    }

    /**
     * Close the drawer then apply the category filter.
     *
     * @param {HTMLElement} wrap
     * @param {number} categoryId
     * @param {string} categoryName
     */
    function requestCloseAndFilter(wrap, categoryId, categoryName) {
        closeDrawer(wrap, function() {
            applyFilter(wrap, categoryId, categoryName);
        });
    }

    /**
     * Apply the selected category filter.
     *
     * @param {HTMLElement} wrap
     * @param {number} categoryId
     * @param {string} categoryName
     */
    function applyFilter(wrap, categoryId, categoryName) {
        wrap.querySelectorAll(SELECTOR.label).forEach(function(labelElement) {
            if (!labelElement.dataset.defaultLabel) {
                labelElement.dataset.defaultLabel = labelElement.textContent;
            }
            labelElement.textContent = categoryName;
        });

        var selectElement = wrap.querySelector(SELECTOR.hiddenSel);
        if (selectElement) {
            Array.from(selectElement.options).forEach(function(option) {
                option.selected = (parseInt(option.value, 10) === parseInt(categoryId, 10));
            });
            selectElement.dispatchEvent(new Event('change', {bubbles: true}));
        }

        wrap.querySelectorAll(SELECTOR.clearBtn).forEach(function(button) {
            button.style.display = (parseInt(categoryId, 10) === -1) ? 'none' : '';
        });
    }

    /**
     * Reset the category filter to "All" and restore the default label.
     *
     * @param {HTMLElement} wrap
     */
    function clearFilter(wrap) {
        wrap.querySelectorAll(SELECTOR.label).forEach(function(labelElement) {
            var defaultLabel = labelElement.dataset.defaultLabel || wrap.dataset.labelall;
            if (defaultLabel) {
                labelElement.textContent = defaultLabel;
            }
        });

        var selectElement = wrap.querySelector(SELECTOR.hiddenSel);
        if (selectElement) {
            Array.from(selectElement.options).forEach(function(option) {
                option.selected = (parseInt(option.value, 10) === -1);
            });
            selectElement.dispatchEvent(new Event('change', {bubbles: true}));
        }

        wrap.querySelectorAll(SELECTOR.clearBtn).forEach(function(button) {
            button.style.display = 'none';
        });
    }

    /**
     * Initialise the delegated events.
     */
    function initDelegatedEvents() {
        document.addEventListener('show.bs.dropdown', function(event) {
            var wrap = event.target.closest(SELECTOR.wrap);
            if (wrap) {
                buildDesktopColumns(wrap);
            }
        });

        document.addEventListener('click', function(event) {
            var dropdownToggle = event.target.closest('[data-bs-toggle="dropdown"], [data-toggle="dropdown"]');
            if (dropdownToggle) {
                var wrap = dropdownToggle.closest(SELECTOR.wrap);
                if (wrap) {
                    buildDesktopColumns(wrap);
                }
            }

            var mobileOpenButton = event.target.closest(SELECTOR.mobileOpen);
            if (mobileOpenButton) {
                var wrap = mobileOpenButton.closest(SELECTOR.wrap);
                if (wrap) {
                    openDrawer(wrap);
                }
                return;
            }

            var clearButton = event.target.closest(SELECTOR.clearBtn);
            if (clearButton) {
                event.stopPropagation();
                var wrap = clearButton.closest(SELECTOR.wrap);
                if (wrap) {
                    clearFilter(wrap);
                }
                return;
            }

            var closeButton = event.target.closest(SELECTOR.drawerClose);
            if (closeButton) {
                var wrap = closeButton.closest(SELECTOR.wrap);
                if (wrap) {
                    closeDrawer(wrap);
                }
                return;
            }

            if (event.target.classList.contains('dash-cat-drawer')) {
                var wrap = event.target.closest(SELECTOR.wrap);
                if (wrap) {
                    closeDrawer(wrap);
                }
                return;
            }

            var backButton = event.target.closest(SELECTOR.mobileBack);
            if (backButton) {
                var wrap = backButton.closest(SELECTOR.wrap);
                if (!wrap) {
                    return;
                }
                var drawerElement = wrap.querySelector(SELECTOR.drawer);
                var stack = drawerElement ? (drawerElement._dashStack || []) : [];
                if (stack.length === 0) {
                    return;
                }
                var previousState = stack.pop();
                var titleElement = wrap.querySelector(SELECTOR.mobileTitle);
                if (titleElement) {
                    titleElement.textContent = previousState.title;
                }
                if (stack.length === 0) {
                    backButton.style.display = 'none';
                }
                buildMobileList(wrap, previousState.items, stack);
            }
        });
    }

    return {
        /**
         * Initialise the category mega filter.
         */
        init: function() {
            if (!delegated) {
                initDelegatedEvents();
                delegated = true;
            }

            document.querySelectorAll(SELECTOR.wrap).forEach(function(wrap) {
                var selectElement = wrap.querySelector(SELECTOR.hiddenSel);

                if (!selectElement) {
                    return;
                }

                var hasActiveFilter = Array.from(selectElement.options).some(function(option) {
                    return option.selected && parseInt(option.value, 10) !== -1;
                });

                if (hasActiveFilter) {
                    wrap.querySelectorAll(SELECTOR.clearBtn).forEach(function(button) {
                        button.style.display = '';
                    });
                }
            });
        }
    };
});
