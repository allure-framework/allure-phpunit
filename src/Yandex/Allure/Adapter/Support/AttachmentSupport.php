<?php

namespace Yandex\Allure\Adapter\Support;


use Exception;
use ReflectionClass;
use TokenReflection\ReflectionMethod;
use Yandex\Allure\Adapter\Model;
use Yandex\Allure\Adapter\Model\AttachmentType;
use Yandex\Allure\Adapter\Model\Provider;

trait AttachmentSupport {

    /**
     * Adds a new attachment to report
     * @param string $filePathOrContents either a string with file contents or file path to copy
     * @param $caption
     * @param $type
     * @throws \Exception
     */
    public function addAttachment($filePathOrContents, $caption, $type = AttachmentType::OTHER)
    {
        if ($this instanceof \PHPUnit_Framework_TestCase){
            $testInstance = Provider::getCurrentTestSuite();
            if (isset($testInstance) && $testInstance instanceof Model\TestSuite){
                $testCase = $testInstance->getCurrentTestCase();
                if (isset($testInstance) && $testCase instanceof Model\TestCase){
                    $newFileName = self::getAttachmentFileName($filePathOrContents, $type);
                    $attachment = new Model\Attachment($caption, $newFileName, $type);
                    $testCase->addAttachment($attachment);
                }
            }
        } else {
            throw new Exception('This method can be called only inside test class.');
        }
    }

    private static function getAttachmentFileName($filePathOrContents, $type)
    {
        if ($type == Model\AttachmentType::OTHER) {
            //Type = other is mainly for attached URLs
            return $filePathOrContents;
        } else if (file_exists($filePathOrContents)) {
            //Trying to attach some file outputted by method
            $fileSha1 = sha1_file($filePathOrContents);
            $outputPath = self::getOutputPath($fileSha1, $type);
            if (!file_exists($outputPath) && !copy($filePathOrContents, $outputPath)) {
                throw new Exception("Failed to copy attachment from $filePathOrContents to $outputPath.");
            }
            return self::getOutputPath($fileSha1, $type);
        } else {
            //Trying to attach string content outputted by method
            $contentsSha1 = sha1($filePathOrContents);
            $outputPath = self::getOutputPath($contentsSha1, $type);
            if (!file_exists($outputPath) && !file_put_contents($outputPath, $filePathOrContents)) {
                throw new Exception("Failed to save file data to $outputPath.");
            }
            return self::getOutputFileName($contentsSha1, $type);
        }
    }

    private static function getOutputFileName($sha1, $type)
    {
        return $sha1 . '-attachment.' . $type;
    }

    private static function getOutputPath($sha1, $type)
    {
        return Model\Provider::getOutputDirectory() . DIRECTORY_SEPARATOR . self::getOutputFileName($sha1, $type);
    }

}