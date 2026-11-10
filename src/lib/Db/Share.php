<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Db;

/**
 * Class Share
 *
 * @method string getType()
 * @method void setType(string $type)
 * @method string|null getSourcePassword()
 * @method void setSourcePassword(string|null $sourcePassword)
 * @method string getTargetPassword()
 * @method void setTargetPassword(string $targetPassword)
 * @method string|null getSourceFolder()
 * @method void setSourceFolder(string|null $sourceFolder)
 * @method string|null getTargetFolder()
 * @method void setTargetFolder(string|null $targetFolder)
 * @method string getReceiver()
 * @method void setReceiver(string $receiver)
 * @method bool getEditable()
 * @method void setEditable(bool $editable)
 * @method bool getShareable()
 * @method void setShareable(bool $shareable)
 * @method bool getSourceUpdated()
 * @method bool getTargetUpdated()
 * @method int|null getExpires()
 * @method void setExpires(int|null $expires)
 * @method string getClient()
 * @method void setClient(string $client)
 *
 * @package OCA\Passwords\Db
 */
class Share extends AbstractEntity implements EntityInterface {

    const string TYPE_USER  = 'user';
    const string TYPE_GROUP = 'group';
    const string TYPE_LINK  = 'link';

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $client;

    /**
     * @var string
     */
    protected string $receiver;

    /**
     * @var bool
     */
    protected bool $editable;

    /**
     * @var bool
     */
    protected bool $shareable;

    /**
     * @var int|null
     */
    protected ?int $expires;

    /**
     * @var string|null
     */
    protected ?string $sourcePassword;

    /**
     * @var string|null
     */
    protected ?string $targetPassword;

    /**
     * @var string|null
     */
    protected ?string $sourceFolder;

    /**
     * @var string|null
     */
    protected ?string $targetFolder;

    /**
     * @var bool
     */
    protected bool $sourceUpdated;

    /**
     * @var bool
     */
    protected bool $targetUpdated;

    /**
     * Password constructor.
     */
    public function __construct() {
        $this->addType('type', 'string');
        $this->addType('client', 'string');
        $this->addType('receiver', 'string');
        $this->addType('sourcePassword', 'string');
        $this->addType('targetPassword', 'string');
        $this->addType('sourceFolder', 'string');
        $this->addType('targetFolder', 'string');

        $this->addType('expires', 'integer');

        $this->addType('editable', 'boolean');
        $this->addType('shareable', 'boolean');
        $this->addType('sourceUpdated', 'boolean');
        $this->addType('targetUpdated', 'boolean');

        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isEditable(): bool {
        return $this->getEditable() === true;
    }

    /**
     * @return bool
     */
    public function isShareable(): bool {
        return $this->getShareable() === true;
    }

    /**
     * A root folder share represents the act of sharing a whole folder.
     * It has a source folder but no source password; the concrete password
     * clones are represented by child shares (see isFolderChildShare()).
     *
     * @return bool
     */
    public function isFolderShareRoot(): bool {
        return $this->getSourceFolder() !== null && $this->getSourcePassword() === null;
    }

    /**
     * A child share belongs to a folder share: it clones one concrete password
     * (sourcePassword) of the shared folder tree (sourceFolder points to the
     * shared root folder). The target folder is set once the clone exists.
     *
     * @return bool
     */
    public function isFolderChildShare(): bool {
        return $this->getSourceFolder() !== null && $this->getSourcePassword() !== null;
    }

    /**
     * Any share that is part of folder sharing (root or child).
     *
     * @return bool
     */
    public function isFolderShare(): bool {
        return $this->getSourceFolder() !== null;
    }

    /**
     * @return bool
     */
    public function isSourceUpdated(): bool {
        return $this->getSourceUpdated() === true;
    }

    /**
     * @param bool $sourceUpdated
     */
    public function setSourceUpdated(bool $sourceUpdated): void {
        $this->sourceUpdated = $sourceUpdated === true;
        $this->markFieldUpdated('sourceUpdated');
    }

    /**
     * @return bool
     */
    public function isTargetUpdated(): bool {
        return $this->getTargetUpdated() === true;
    }

    /**
     * @param bool $targetUpdated
     */
    public function setTargetUpdated(bool $targetUpdated): void {
        $this->targetUpdated = $targetUpdated === true;
        $this->markFieldUpdated('targetUpdated');
    }
}