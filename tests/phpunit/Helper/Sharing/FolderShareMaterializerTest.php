<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Helper\Sharing;

use OCA\Passwords\Db\Folder;
use OCA\Passwords\Db\FolderRevision;
use OCA\Passwords\Db\Share;
use OCA\Passwords\Services\EncryptionService;
use OCA\Passwords\Services\LoggingService;
use OCA\Passwords\Services\Object\FolderRevisionService;
use OCA\Passwords\Services\Object\FolderService;
use OCA\Passwords\Services\Object\PasswordRevisionService;
use OCA\Passwords\Services\Object\PasswordService;
use OCA\Passwords\Services\Object\ShareService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FolderShareMaterializerTest
 *
 * @package OCA\Passwords\Helper\Sharing
 */
class FolderShareMaterializerTest extends TestCase {

    protected ShareService|MockObject $shareService;
    protected FolderService|MockObject $folderService;
    protected FolderRevisionService|MockObject $folderRevisionService;
    protected FolderShareMaterializer $materializer;

    protected function setUp(): void {
        $this->shareService          = $this->createMock(ShareService::class);
        $this->folderService         = $this->createMock(FolderService::class);
        $this->folderRevisionService = $this->createMock(FolderRevisionService::class);

        $this->materializer = new FolderShareMaterializer(
            $this->shareService,
            $this->folderService,
            $this->folderRevisionService,
            $this->createMock(PasswordService::class),
            $this->createMock(PasswordRevisionService::class),
            $this->createMock(LoggingService::class)
        );
    }

    public function testSyncIgnoresPasswordShares(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_PASSWORD);

        $this->shareService->expects($this->never())->method('findByParentShare');

        $this->materializer->syncNewChildren($share);
    }

    public function testSyncIgnoresUnmaterializedFolderShares(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setSourceFolder('source-folder-uuid');
        $share->setTargetFolder(null);

        $this->shareService->expects($this->never())->method('findByParentShare');

        $this->materializer->syncNewChildren($share);
    }

    public function testSyncReadsExistingChildrenOfMaterializedFolder(): void {
        $share = new Share();
        $share->setUuid('root-share-uuid');
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setSourceFolder('source-folder-uuid');
        $share->setTargetFolder('target-folder-uuid');
        $share->setReceiver('erika');
        $share->setUserId('max');

        $folder = new Folder();
        $folder->setUuid('source-folder-uuid');

        $revision = new FolderRevision();
        $revision->setCseType(EncryptionService::CSE_ENCRYPTION_NONE);
        $revision->setTrashed(false);

        $this->shareService->expects($this->once())
                           ->method('findByParentShare')
                           ->with('root-share-uuid')
                           ->willReturn([]);
        $this->folderService->method('findByUuid')->willReturn($folder);
        $this->folderService->method('findByParent')->willReturn([]);
        $this->folderRevisionService->method('findCurrentRevisionByModel')->willReturn($revision);

        $this->materializer->syncNewChildren($share);
    }
}
