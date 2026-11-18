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

namespace OCA\PasswordFolders\Events\Tag;

use OCA\PasswordFolders\Db\Tag;
use OCA\PasswordFolders\Db\TagRevision;
use OCP\EventDispatcher\Event;

/**
 * Class AfterTagSetRevisionEvent
 *
 * @package OCA\PasswordFolders\Events\Tag
 */
class AfterTagSetRevisionEvent extends Event {

    /**
     * AfterTagSetRevisionEvent constructor.
     *
     * @param Tag         $Tag
     * @param TagRevision $revision
     */
    public function __construct(protected Tag $Tag, protected TagRevision $revision) {
        parent::__construct();
    }

    /**
     * @return TagRevision
     */
    public function getRevision(): TagRevision {
        return $this->revision;
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag {
        return $this->Tag;
    }
}