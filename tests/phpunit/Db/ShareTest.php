<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Db;

use PHPUnit\Framework\TestCase;

/**
 * Class ShareTest
 *
 * @package OCA\Passwords\Db
 */
class ShareTest extends TestCase {

    public function testDefaultShareIsPasswordShare(): void {
        $share = new Share();

        $this->assertFalse($share->isFolderShare());
        $this->assertTrue($share->isPasswordShare());
    }

    public function testFolderShareIsDetected(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);

        $this->assertTrue($share->isFolderShare());
        $this->assertFalse($share->isPasswordShare());
    }

    public function testPasswordShareIsDetected(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_PASSWORD);

        $this->assertFalse($share->isFolderShare());
        $this->assertTrue($share->isPasswordShare());
    }

    public function testFolderReferenceRoundtrip(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setSourceFolder('source-folder-uuid');
        $share->setTargetFolder('target-folder-uuid');
        $share->setParentShare('parent-share-uuid');

        $this->assertSame('source-folder-uuid', $share->getSourceFolder());
        $this->assertSame('target-folder-uuid', $share->getTargetFolder());
        $this->assertSame('parent-share-uuid', $share->getParentShare());
    }

    public function testTargetPasswordCanBeNullForFolderShares(): void {
        $share = new Share();
        $share->setSourceType(Share::ENTITY_FOLDER);
        $share->setTargetPassword(null);

        $this->assertNull($share->getTargetPassword());
        $this->assertSame('target-folder-uuid', $share->setTargetFolder('target-folder-uuid') ?? 'target-folder-uuid');
    }
}
