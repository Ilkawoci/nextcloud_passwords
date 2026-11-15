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
 * @method void setShareId(?string $shareId)
 * @method bool getHasShares()
 * @method void setHasShares(bool $hasShares)
 *
 * @package OCA\Passwords\Db
 */
class Folder extends AbstractModel {

    /**
     * @var bool
     */
    protected bool $suspended;

    /**
     * @var string|null
     */
    protected ?string $shareId;

    /**
     * @var bool
     */
    protected bool $hasShares;

    /**
     * Password constructor.
     */
    public function __construct() {
        $this->addType('suspended', 'boolean');
        $this->addType('shareId', 'string');
        $this->addType('hasShares', 'boolean');

        parent::__construct();
    }

    /**
     * Whether this folder was shared by another user
     *
     * @return bool
     */
    public function hasShare(): bool {
        return $this->getShareId() !== null;
    }

    /**
     * Whether this folder is shared with other users
     *
     * @return bool
     */
    public function hasShares(): bool {
        return $this->getHasShares() === true;
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool {
        return $this->getSuspended() === true;
    }
}