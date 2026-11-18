<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Db;

/**
 * Class Share
 *
 * @method string getType()
 * @method void setType(string $type)
 * @method string getSourceType()
 * @method void setSourceType(string $sourceType)
 * @method string getSourcePassword()
 * @method void setSourcePassword(?string $sourcePassword)
 * @method string getTargetPassword()
 * @method void setTargetPassword(?string $targetPassword)
 * @method string|null getSourceFolder()
 * @method void setSourceFolder(?string $sourceFolder)
 * @method string|null getTargetFolder()
 * @method void setTargetFolder(?string $targetFolder)
 * @method string|null getParentShare()
 * @method void setParentShare(?string $parentShare)
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
 * @package OCA\PasswordFolders\Db
 */
class Share extends AbstractEntity implements EntityInterface {

    const string TYPE_USER  = 'user';
    const string TYPE_GROUP = 'group';
    const string TYPE_LINK  = 'link';

    const string ENTITY_PASSWORD = 'password';
    const string ENTITY_FOLDER   = 'folder';

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $sourceType = self::ENTITY_PASSWORD;

    /**
     * @var string|null
     */
    protected ?string $sourceFolder;

    /**
     * @var string|null
     */
    protected ?string $targetFolder;

    /**
     * @var string|null
     */
    protected ?string $parentShare;

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
        $this->addType('sourceType', 'string');
        $this->addType('sourceFolder', 'string');
        $this->addType('targetFolder', 'string');
        $this->addType('parentShare', 'string');
        $this->addType('client', 'string');
        $this->addType('receiver', 'string');
        $this->addType('sourcePassword', 'string');
        $this->addType('targetPassword', 'string');

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

    /**
     * Whether this share references a folder instead of a password
     *
     * @return bool
     */
    public function isFolderShare(): bool {
        return $this->getSourceType() === self::ENTITY_FOLDER;
    }

    /**
     * Whether this share references a password
     *
     * @return bool
     */
    public function isPasswordShare(): bool {
        return $this->getSourceType() !== self::ENTITY_FOLDER;
    }

    /**
     * Whether this share is part of a shared folder tree
     *
     * @return bool
     */
    public function isChildShare(): bool {
        return $this->getParentShare() !== null;
    }
}