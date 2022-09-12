<?php

namespace Syno\Storm\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExistsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'file_exists', function (string $filePath) {
                return $this->checkIfFileExists($filePath);
            })
        ];
    }

    public function checkIfFileExists(string $filePath): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $filePath);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $fileHeaders = curl_exec($ch);
        curl_close($ch);

        if($fileHeaders && strpos($fileHeaders, '200 OK')){
            return true;
        }

        return false;
    }
}
