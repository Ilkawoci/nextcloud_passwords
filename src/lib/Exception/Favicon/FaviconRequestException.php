<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Exception\Favicon;

use OCA\PasswordFolders\Exception\AbstractException;

/**
 * Class FaviconRequestException
 *
 * @package OCA\PasswordFolders\Exception\Favicon
 */
class FaviconRequestException extends AbstractException {
    const int EXCEPTION_CODE       = 103;
    const string EXCEPTION_MESSAGE = 'The request to the favicon service failed';
}