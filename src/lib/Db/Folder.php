<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Db;

/**
 * Class Folder
 *
 * @method bool getSuspended()
 * @method void setSuspended(bool $suspended)
 * @method string|null getShareId()
 * @method void setShareId(string|null $shareId)
 * @method string|null getSourceFolder()
 * @method void setSourceFolder(string|null $sourceFolder)
 *
 * @package OCA\Passwords\Db
 */
class Folder extends AbstractModel {

    /**
     * @var bool
     */
    protected bool $suspended;

    /**
     * Uuid of the share that produced this folder clone (null for non-clones).
     *
     * @var string|null
     */
    protected ?string $shareId;

    /**
     * Uuid of the source folder this clone was copied from (null for non-clones).
     *
     * @var string|null
     */
    protected ?string $sourceFolder;

    /**
     * Folder constructor.
     */
    public function __construct() {
        $this->addType('suspended', 'boolean');
        $this->addType('shareId', 'string');
        $this->addType('sourceFolder', 'string');

        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool {
        return $this->getSuspended() === true;
    }
}