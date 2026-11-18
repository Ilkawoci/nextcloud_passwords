<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Exception\Favicon;

use OCA\PasswordFolders\Exception\AbstractException;

/**
 * Class NoFaviconDataException
 *
 * @package OCA\PasswordFolders\Exception\Favicon
 */
class NoFaviconDataException extends AbstractException {
    const int EXCEPTION_CODE       = 100;
    const string EXCEPTION_MESSAGE = 'Favicon service returned no data';
}