<?php

namespace App\Controller\Helpers;

use App\Entity\MediaObject;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Trait helper to save the image into a folder.
 */
trait MediaObjectHelperController
{
    /**
     * Save the base_64 image into media folder.
     *  
     * @param MediaObject $mediaObject.
     * @param string $data as json object with base64 image or img.
     * @param TranslatorInterface translator
     * @throws Exception
     * @return string the new filename.
     */
    public function manageImage(array $data, TranslatorInterface $translator, $filename = null)
    {
        if (!(isset($data['image']) || isset($data['img']))) { return $filename; }
        $img64 = null; 
        if(isset($data['image'])) { $img64 = $data['image']; }
        if(isset($data['img'])) { $img64 = $data['img']; } 
        if ($img64) {
            if (preg_match('/^data:image\/(\w+)\+?\w*;base64,/', $img64, $type)) {
                $img64 = substr($img64, strpos($img64, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'svg'])) {
                    throw new Exception($translator->trans('invalid.image.type.'));
                }

                $img = base64_decode($img64);

                if ($img === false) {
                    throw new Exception($translator->trans('base64_decode failed'));
                }
            } else {
                throw new Exception($translator->trans('did not match data URI with image data'));
            }
            $oldFilename = null;
            if (!$filename) {
                $filename = uniqid() . "." . $type;
            } else if (!$this->endsWith($filename, $type)){
                $oldFilename = $filename;
                $filename = uniqid() . "." . $type;
            }
            try {
                $directoryName = $this->getParameter('media_object');
                //Check if the directory already exists.
                if(!is_dir($directoryName)){
                    //Directory does not exist, so lets create it.
                    mkdir($directoryName, 0755);
                }
                error_log($directoryName);
                file_put_contents(
                    $directoryName . "/" . $filename,
                    $img
                );
            } catch (FileException $e) {
                throw new Exception($translator->trans('cannot save image data to file'));
            }

            if ($oldFilename) {
                unlink($this->getParameter('media_object') . "/" . $oldFilename);
            }
            return $filename;
        }
    }

    private function endsWith($string, $test) {
        $strLen = strlen($string);
        $testLen = strlen($test);
        if ($testLen > $strLen) return false;
        return substr_compare($string, $test, $strLen - $testLen, $testLen) === 0;
    }
}