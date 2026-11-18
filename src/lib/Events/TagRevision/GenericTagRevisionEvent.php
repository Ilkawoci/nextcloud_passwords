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

namespace OCA\PasswordFolders\Events\TagRevision;

use OCA\PasswordFolders\Db\TagRevision;
use OCP\EventDispatcher\Event;

/**
 * Class GenericTagRevisionEvent
 *
 * @package OCA\PasswordFolders\Events\TagRevision
 */
class GenericTagRevisionEvent extends Event {

    /**
     * GenericTagRevisionEvent constructor.
     *
     * @param TagRevision $TagRevision
     */
    public function __construct(protected TagRevision $TagRevision) {
        parent::__construct();
    }

    /**
     * @return TagRevision
     */
    public function getTagRevision(): TagRevision {
        return $this->TagRevision;
    }
}