<?php
/*
 * @copyright 2020 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\EventListener\Tag;

use Exception;
use OCA\PasswordFolders\Db\TagRevision;
use OCA\PasswordFolders\Events\Tag\TagClonedEvent;
use OCA\PasswordFolders\Services\Object\PasswordRevisionService;
use OCA\PasswordFolders\Services\Object\PasswordTagRelationService;
use OCA\PasswordFolders\Services\Object\TagRevisionService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * Class TagClonedListener
 *
 * @package OCA\PasswordFolders\EventListener\Tag
 */
class TagClonedListener implements IEventListener {

    /**
     * @var TagRevisionService
     */
    protected TagRevisionService $revisionService;

    /**
     * @var PasswordTagRelationService
     */
    protected PasswordTagRelationService $relationService;

    /**
     * PasswordHook constructor.
     *
     * @param TagRevisionService         $revisionService
     * @param PasswordTagRelationService $relationService
     */
    public function __construct(
        TagRevisionService $revisionService,
        PasswordTagRelationService $relationService
    ) {
        $this->revisionService         = $revisionService;
        $this->relationService         = $relationService;
    }

    /**
     * @param Event $event
     *
     * @throws Exception
     */
    public function handle(Event $event): void {
        if(!($event instanceof TagClonedEvent)) return;
        $originalTag = $event->getOriginal();
        $clonedTag = $event->getClone();

        /** @var TagRevision[] $revisions */
        $revisions = $this->revisionService->findByModel($originalTag->getUuid());

        $currentClonedRevision = null;
        foreach($revisions as $revision) {
            /** @var TagRevision $revisionClone */
            $revisionClone = $this->revisionService->clone($revision, ['model' => $clonedTag->getUuid()]);
            $this->revisionService->save($revisionClone);
            if($revision->getUuid() === $originalTag->getRevision()) {
                $clonedTag->setRevision($revisionClone->getUuid());
                $currentClonedRevision = $revisionClone;
            }
        }

        $relations = $this->relationService->findByTag($originalTag->getUuid());
        foreach($relations as $relation) {
            $relationClone = $this->relationService->clone($relation, [
                'tag'         => $currentClonedRevision->getModel(),
                'tagRevision' => $currentClonedRevision->getUuid(),
                'hidden'      => $currentClonedRevision->isHidden() || $relation->isHidden()
            ]);
            $this->relationService->save($relationClone);
        }
    }
}