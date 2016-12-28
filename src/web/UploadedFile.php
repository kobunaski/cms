<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
 */

namespace craft\web;

use Craft;

/**
 * UploadedFile represents the information for an uploaded file.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class UploadedFile extends \yii\web\UploadedFile
{
    // Public Methods
    // =========================================================================

    /**
     * Returns an instance of the specified uploaded file.  The name can be a plain string or a string like an array
     * element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
     *
     * @param string $name The name of the file input field.
     *
     * @return UploadedFile|null The instance of the uploaded file. null is returned if no file is uploaded for the
     *                           specified name.
     */
    public static function getInstanceByName($name)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::getInstanceByName(self::_normalizeName($name));
    }

    /**
     * Returns an array of instances starting with specified array name.
     *
     * If multiple files were uploaded and saved as 'Files[0]', 'Files[1]', 'Files[n]'..., you can have them all by
     * passing 'Files' as array name.
     *
     * @param string  $name                  The name of the array of files
     * @param boolean $lookForSingleInstance If set to true, will look for a single instance of the given name.
     *
     * @return UploadedFile[] The array of UploadedFile objects. Empty array is returned if no adequate upload was
     *                        found. Please note that this array will contain all files from all subarrays regardless
     *                        how deeply nested they are.
     */
    public static function getInstancesByName($name, $lookForSingleInstance = true)
    {
        $name = self::_normalizeName($name);
        $instances = parent::getInstancesByName($name);

        if (empty($instances) && $lookForSingleInstance) {
            $singleInstance = parent::getInstanceByName($name);

            if ($singleInstance) {
                $instances[] = $singleInstance;
            }
        }

        return $instances;
    }

    /**
     * Saves the uploaded file to a temp location.
     *
     * @param boolean $deleteTempFile whether to delete the temporary file after saving.
     *                                If true, you will not be able to save the uploaded file again in the current request.
     *
     * @return string|false the path to the temp file, or false if the file wasn't saved successfully
     * @see error
     */
    public function saveAsTempFile($deleteTempFile = true)
    {
        if ($this->error != UPLOAD_ERR_OK) {
            return false;
        }

        $tempFilename = uniqid(pathinfo($this->name, PATHINFO_FILENAME), true).'.'.pathinfo($this->name, PATHINFO_EXTENSION);
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;

        if (!$this->saveAs($tempPath, $deleteTempFile)) {
            return false;
        }

        return $tempPath;
    }

    // Private Methods
    // =========================================================================

    /**
     * Swaps dot notation for the normal format.
     *
     * ex: fields.assetsField => fields[assetsField]
     *
     * @param string $name The name to normalize.
     *
     * @return string
     */
    private static function _normalizeName($name)
    {
        if (($pos = strpos($name, '.')) !== false) {
            // Convert dot notation to the normal format ex: fields.assetsField => fields[assetsField]
            $name = substr($name, 0, $pos).'['.str_replace('.', '][', substr($name, $pos + 1)).']';
        }

        return $name;
    }
}
