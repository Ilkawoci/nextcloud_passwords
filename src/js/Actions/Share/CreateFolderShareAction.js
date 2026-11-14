/*
 * @copyright 2026 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

import API from "@js/Helper/api";
import {getCurrentUser} from '@nextcloud/auth';
import CreateShareError from "@js/Actions/Share/CreateShareError";
import LoggingService from "@js/Services/LoggingService";
import {emitMultiple} from "@js/Helper/event-bus";

export default class CreateFolderShareAction {
    #folder;
    #options;
    #recipient;
    #user;

    constructor(folder, recipient, options) {
        this.#folder = folder;
        this.#recipient = recipient;
        this.#options = options;
        this.#user = getCurrentUser();
    }

    async run() {
        await this.#loadShares();

        if(!this.#hasPermissionToShare()) {
            this.#throwError(['CreateShareErrorNoPermission', {password: this.#folder.label, recipient: this.#recipient.displayName}]);
        }
        if(this.#isShareWithOwner()) {
            this.#throwError(['CreateShareErrorShareWithOwner', {password: this.#folder.label, recipient: this.#recipient.displayName}]);
        }
        if(!await this.#clearExistingShares()) {
            this.#throwError(['CreateShareErrorAlreadyShared', {password: this.#folder.label, recipient: this.#recipient.displayName}]);
        }

        await this.#createShare();

        emitMultiple(['passwords:folder:updated', 'passwords:item:updated'], this.#folder);
    }

    #hasPermissionToShare() {
        return this.#recipient.id !== this.#user.uid &&
               (
                   !this.#folder.hasOwnProperty('share') ||
                   this.#folder.share === null ||
                   this.#folder.share.shareable
               );
    }

    #isShareWithOwner() {
        return this.#folder.hasOwnProperty('share') && this.#folder.share !== null && !this.#folder.share.owner.id === this.#recipient.id;
    }

    async #loadShares() {
        if(this.#folder.hasOwnProperty('shares') && (this.#folder.share === null && typeof this.#folder.share !== 'string')) {
            return;
        }

        this.#folder = await API.showFolder(this.#folder.id, 'model+shares');
    }

    async #clearExistingShares() {
        for(let index in this.#folder.shares) {
            let share = this.#folder.shares[index];

            if(share.receiver.id === this.#recipient.id) {
                if(!this.#options.overwrite) {
                    return false;
                }

                await API.deleteShare(share.id);
                delete this.#folder[share.id];
            }
        }

        return true;
    }

    async #createShare() {
        let share = {
            folder   : this.#folder.id,
            expires  : this.#getExpiryDate(),
            editable : this.#getEditable(),
            shareable: this.#options.shareable,
            receiver : this.#recipient.id
        };

        let shareModel = await API.createShare(share);
        share.id = shareModel.id;
        share.updatePending = true;
        share.owner = {
            id  : this.#user.uid,
            name: this.#user.displayName
        };
        share.receiver = {id: this.#recipient.id, name: this.#recipient.displayName};
        share.created = new Date();
        share.updated = new Date();

        this.#folder.shares[share.id] = share;
    }

    #getExpiryDate() {
        if(this.#folder.share !== null &&
           this.#folder.share.expires !== null &&
           (
               this.#options.expires === null ||
               this.#folder.share.expires < this.#options.expires
           )
        ) {
            return this.#folder.share.expires;
        }

        if(this.#options.expires instanceof Date) {
            if(this.#options.expires.getTime() < Date.now() - 1000) {
                this.#throwError(['CreateShareErrorAlreadyExpired', {password: this.#folder.label, recipient: this.#recipient.displayName}]);
            }
        }

        return this.#options.expires;
    }

    #getEditable() {
        if(this.#folder.hasOwnProperty('share') && this.#folder.share !== null) {
            return this.#folder.share.editable;
        }

        return this.#options.editable;
    }

    #throwError(message) {
        let error = new CreateShareError(message);
        LoggingService.info(error.message);
        throw error;
    }
}
