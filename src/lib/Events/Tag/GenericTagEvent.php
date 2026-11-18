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
use OCP\EventDispatcher\Event;

/**
 * Class GenericTagEvent
 *
 * @package OCA\PasswordFolders\Events\Tag
 */
class GenericTagEvent extends Event {

    /**
     * GenericTagEvent constructor.
     *
     * @param Tag $Tag
     */
    public function __construct(protected Tag $Tag) {
        parent::__construct();
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag {
        return $this->Tag;
    }
}