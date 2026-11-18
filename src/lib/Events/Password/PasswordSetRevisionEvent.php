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
use OCA\PasswordFolders\Db\PasswordRevision;
use OCP\EventDispatcher\Event;

/**
 * Class PasswordSetRevisionEvent
 *
 * @package OCA\PasswordFolders\Events\Password
 */
class PasswordSetRevisionEvent extends Event {

    /**
     * PasswordSetRevisionEvent constructor.
     *
     * @param Password         $password
     * @param PasswordRevision $revision
     */
    public function __construct(protected Password $password, protected PasswordRevision $revision) {
        parent::__construct();
    }

    /**
     * @return PasswordRevision
     */
    public function getRevision(): PasswordRevision {
        return $this->revision;
    }

    /**
     * @return Password
     */
    public function getPassword(): Password {
        return $this->password;
    }
}