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

namespace OCA\PasswordFolders\Exception\Image;

use OCA\PasswordFolders\Exception\AbstractException;

class ImageConversionException extends AbstractException {

    const string EXCEPTION_MESSAGE = 'Could not convert image';

}