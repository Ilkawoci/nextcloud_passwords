<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Exception\Command;

use OCA\PasswordFolders\Exception\AbstractException;

/**
 * Class NonInteractiveShellException
 *
 * @package OCA\PasswordFolders\Exception\Command
 */
class NonInteractiveShellException extends AbstractException {
    const int EXCEPTION_CODE       = 107;
    const string EXCEPTION_MESSAGE = 'Interactive command requires "--no-interaction" to be set to run in non-interactive shell';
}