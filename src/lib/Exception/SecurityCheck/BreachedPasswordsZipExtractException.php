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

namespace OCA\PasswordFolders\Exception\SecurityCheck;

use Exception;
use Throwable;

/**
 * Class BreachedPasswordsZipExtractException
 *
 * @package OCA\PasswordFolders\Exception\SecurityCheck
 */
class BreachedPasswordsZipExtractException extends Exception {
    const int EXCEPTION_CODE = 109;

    /**
     * @param string         $file
     * @param Throwable|null $previous
     */
    public function __construct(string $file, ?Throwable $previous = null) {
        parent::__construct("Unable to extract {$file} from breached passwords zip file", static::EXCEPTION_CODE, $previous);
    }
}