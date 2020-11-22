<?php

namespace App\Controller\Helpers;

use App\Entity\MediaObject;
use ErrorException;
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
        if (!isset($data['image'])) {
            return $filename;
        }
        $img64 = $data['image'];
        $isBase64 = true;
        if ($img64) {
            if (is_string($img64)) {
                if (preg_match('/^data:image\/(\w+)\+?\w*;base64,/', $img64, $type)) {
                    $img64 = substr($img64, strpos($img64, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'svg'])) {
                        throw new Exception('image.failed.type');
                    }

                    $img = base64_decode($img64);

                    if ($img === false || $img === "") {
                        throw new Exception('image.failed.base64.decode.failed');
                    }
                } else {
                    throw new Exception('image.failed.base64.uri');
                }
            } else {
                var_dump(memory_get_usage());
                //$img64 = implode(array_map('chr', $img64));
                foreach ($img64 as $key => &$value) {
                    $value = chr($value);
                }
                $imgStr = implode($img64);

                $img = imagecreatefromstring($imgStr);
                $type = 'jpg';
                if (!$img) throw new Exception('image.create.from.string.failed');
                $isBase64 = false;
            }
            $oldFilename = null;
            if (!$filename) {
                $filename = uniqid() . "." . $type;
            } else if (!$this->endsWith($filename, $type)) {
                $oldFilename = $filename;
                $filename = uniqid() . "." . $type;
            }
            try {
                $directoryName = $this->getParameter('media_object');
                //Check if the directory already exists.
                if (!is_dir($directoryName)) {
                    //Directory does not exist, so lets create it.
                    mkdir($directoryName, 0755);
                }
                error_log($directoryName);
                if ($isBase64)
                    file_put_contents(
                        $directoryName . "/" . $filename,
                        $img
                    );
                else
                    imagejpeg($img, $directoryName . "/" . $filename);
                imagedestroy($img);
            } catch (FileException $e) {
                throw new Exception('image.failed.save');
            } catch (ErrorException $e) {
                throw new Exception('image.failed.save');
            }
            try {
                if ($type !== 'svg') {
                    $newFilename = $this->resize($directoryName, $filename, $filename, $type, 1000);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 900);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 800);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 700);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 600);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 500);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 400);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 300);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 200);
                    $newFilename = $this->resize($directoryName, $newFilename, $filename, $type, 100);
                }
            } catch (Exception $e) {
                throw new Exception('image.resize.failed');
            }
            $this->deleteImage($oldFilename);
            return $filename;
        }
    }

    public function deleteImage($oldFilename)
    {
        if ($oldFilename) {
            $filePath = $this->getParameter('media_object') . "/" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_1000" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_900" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_800" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_700" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_600" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_500" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_400" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_300" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_200" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
            $filePath = $this->getParameter('media_object') . "/w_100" . $oldFilename;
            if (file_exists($filePath))
                unlink($filePath);
        }
    }

    private function resize($directoryName, $filename, $baseFilename, $type, $newWidth)
    {
        $dirAndFileName = $directoryName . "/" . $filename;
        list($oldWidth, $oldHeight) = getimagesize($dirAndFileName);
        $newHeight = $oldHeight * $newWidth / $oldWidth;
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        if ($type === 'jpeg' || $type === 'jpg')
            $source = imagecreatefromjpeg($dirAndFileName);
        if ($type === 'png')
            $source = imagecreatefrompng($dirAndFileName);
        if ($type === 'gif')
            $source = imagecreatefromgif($dirAndFileName);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
        $dirAndFileName = $directoryName . "/w_" . $newWidth . "_" . $baseFilename;
        if ($type === 'jpeg' || $type === 'jpg')
            imagejpeg($thumb, $dirAndFileName);
        if ($type === 'png')
            $source = imagepng($thumb, $dirAndFileName);
        if ($type === 'gif')
            $source = imagegif($thumb, $dirAndFileName);
        imagedestroy($thumb);
        return "w_" . $newWidth . "_" . $baseFilename;
    }

    private function endsWith($string, $test)
    {
        $strLen = strlen($string);
        $testLen = strlen($test);
        if ($testLen > $strLen) return false;
        return substr_compare($string, $test, $strLen - $testLen, $testLen) === 0;
    }
}
