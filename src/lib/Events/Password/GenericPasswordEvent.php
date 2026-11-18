<?php
/*
 * @copyright 2025 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\Events\Password;

use OCA\PasswordFolders\Db\Password;
use OCP\EventDispatcher\Event;

/**
 * Class GenericPasswordEvent
 *
 * @package OCA\PasswordFolders\Events\Password
 */
class GenericPasswordEvent extends Event {

    /**
     * GenericPasswordEvent constructor.
     *
     * @param Password $password
     */
    public function __construct(protected Password $password) {
        parent::__construct();
    }

    /**
     * @return Password
     */
    public function getPassword(): Password {
        return $this->password;
    }
}