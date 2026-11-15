/*
 * @copyright 2026 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

import Sidebar from "@js/Models/Sidebar/Sidebar";

export default class FolderSidebar extends Sidebar {

    constructor(item, tab = null) {
        super('folder', item, item.label, tab);
    }
}
