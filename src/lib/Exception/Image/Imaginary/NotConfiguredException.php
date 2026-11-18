<?php
/*
 * @copyright 2024 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\Exception\Image\Imaginary;

use OCA\PasswordFolders\Exception\AbstractException;

class NotConfiguredException extends AbstractException {

    const string EXCEPTION_MESSAGE = 'Imaginary image provider is enabled but not configured';

}