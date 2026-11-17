<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\EventListener\Share;

use OCA\Passwords\Db\Share;
use OCA\Passwords\Events\Share\ShareDeletedEvent;
use OCA\Passwords\Services\Object\PasswordService;
use OCA\Passwords\Services\Object\ShareService;
use PHPUnit\Framework\TestCase;

/**
 * Class ShareDeletedListenerTest
 *
 * @package OCA\Passwords\EventListener\Share
 */
class ShareDeletedListenerTest extends TestCase {

    public function testFolderSharesDoNotLookupSourcePassword(): void {
        $shareService    = $this->createMock(ShareService::class);
        $passwordService = $this->createMock(PasswordService::class);

        $shareService->expects($this->never())->method('findBySourcePassword');
        $passwordService->expects($this->never())->method('findByUuid');

        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setSourceFolder('source-folder-uuid');

        $listener = new ShareDeletedListener($shareService, $passwordService);
        $listener->handle(new ShareDeletedEvent($share));
    }
}
