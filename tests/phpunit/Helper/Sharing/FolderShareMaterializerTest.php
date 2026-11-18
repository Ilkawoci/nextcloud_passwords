<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Helper\Sharing;

use OCA\PasswordFolders\Db\Folder;
use OCA\PasswordFolders\Db\Password;
use OCA\PasswordFolders\Db\FolderRevision;
use OCA\PasswordFolders\Db\Share;
use OCA\PasswordFolders\Services\EncryptionService;
use OCA\PasswordFolders\Services\LoggingService;
use OCA\PasswordFolders\Services\Object\FolderRevisionService;
use OCA\PasswordFolders\Services\Object\FolderService;
use OCA\PasswordFolders\Services\Object\PasswordRevisionService;
use OCA\PasswordFolders\Services\Object\PasswordService;
use OCA\PasswordFolders\Services\Object\ShareService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FolderShareMaterializerTest
 *
 * @package OCA\PasswordFolders\Helper\Sharing
 */
class FolderShareMaterializerTest extends TestCase {

    protected ShareService|MockObject $shareService;
    protected FolderService|MockObject $folderService;
    protected FolderRevisionService|MockObject $folderRevisionService;
    protected PasswordService|MockObject $passwordService;
    protected FolderShareMaterializer $materializer;

    protected function setUp(): void {
        $this->shareService          = $this->createMock(ShareService::class);
        $this->folderService         = $this->createMock(FolderService::class);
        $this->folderRevisionService = $this->createMock(FolderRevisionService::class);
        $this->passwordService       = $this->createMock(PasswordService::class);

        $this->materializer = new FolderShareMaterializer(
            $this->shareService,
            $this->folderService,
            $this->folderRevisionService,
            $this->passwordService,
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

    public function testPruneIgnoresPasswordShares(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_PASSWORD);

        $this->shareService->expects($this->never())->method('findByParentShare');

        $this->materializer->pruneTree($share);
    }

    public function testPruneIgnoresUnmaterializedFolderShares(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setSourceFolder('source-folder-uuid');
        $share->setTargetFolder(null);

        $this->shareService->expects($this->never())->method('findByParentShare');

        $this->materializer->pruneTree($share);
    }

    public function testPruneRemovesCopiesOfDeletedPasswords(): void {
        $root = new Share();
        $root->setUuid('root-share-uuid');
        $root->setSourceType(Share::ENTITY_FOLDER);
        $root->setSourceFolder('source-folder-uuid');
        $root->setTargetFolder('target-folder-uuid');
        $root->setReceiver('erika');
        $root->setUserId('max');

        $child = new Share();
        $child->setUuid('child-share-uuid');
        $child->setSourceType(Share::ENTITY_PASSWORD);
        $child->setSourcePassword('deleted-password-uuid');
        $child->setTargetPassword('target-password-uuid');
        $child->setTargetFolder('target-folder-uuid');
        $child->setParentShare('root-share-uuid');

        $folder = new Folder();
        $folder->setUuid('source-folder-uuid');
        $revision = new FolderRevision();
        $revision->setCseType(EncryptionService::CSE_ENCRYPTION_NONE);
        $revision->setTrashed(false);

        $target = new Password();
        $target->setUuid('target-password-uuid');

        $this->shareService->method('findByParentShare')->with('root-share-uuid')->willReturn([$child]);
        $this->shareService->expects($this->once())->method('delete')->with($child);

        $this->folderService->method('findByUuid')->willReturn($folder);
        $this->folderService->method('findByParent')->willReturn([]);
        $this->folderRevisionService->method('findCurrentRevisionByModel')->willReturn($revision);

        $this->passwordService->method('findByFolder')->willReturn([]);
        $this->passwordService->method('findByUuid')->willReturn($target);
        $this->passwordService->expects($this->once())->method('delete')->with($target);

        $this->materializer->pruneTree($root);
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
