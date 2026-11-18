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
 * Class BeforeTagRevisionClonedEvent
 *
 * @package OCA\PasswordFolders\Events\TagRevision
 */
class BeforeTagRevisionClonedEvent extends Event {

    /**
     * BeforeTagRevisionClonedEvent constructor.
     *
     * @param TagRevision $original
     * @param TagRevision $clone
     * @param array       $overwrites
     */
    public function __construct(protected TagRevision $original, protected TagRevision $clone, protected array $overwrites) {
        parent::__construct();
    }

    /**
     * @return TagRevision
     */
    public function getOriginal(): TagRevision {
        return $this->original;
    }

    /**
     * @return TagRevision
     */
    public function getClone(): TagRevision {
        return $this->clone;
    }

    /**
     * @return array
     */
    public function getOverwrites(): array {
        return $this->overwrites;
    }
}