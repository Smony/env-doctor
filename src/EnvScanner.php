<?php

namespace Smony\EnvDoctor;

class EnvScanner
{
    public function scan(string $path = '.'): array
    {
        $result = [];

        $directory = new \RecursiveDirectoryIterator(
            $path,
            \FilesystemIterator::SKIP_DOTS
        );

        $filter = new \RecursiveCallbackFilterIterator(
            $directory,
            function ($current, $key, $iterator) {
                if ($current->isDir()) {
                    if ($current->getFilename() === 'vendor') {
                        return false;
                    }
                }

                return true;
            }
        );

        $iterator = new \RecursiveIteratorIterator($filter);

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            $tokens = token_get_all($content);

            for ($i = 0; $i < count($tokens); $i++) {

                if (is_array($tokens[$i]) && $tokens[$i][1] === 'env') {

                    if (isset($tokens[$i + 1]) && $tokens[$i + 1] === '(') {

                        $j = $i + 2;

                        if (
                            isset($tokens[$j]) &&
                            is_array($tokens[$j]) &&
                            $tokens[$j][0] === T_CONSTANT_ENCAPSED_STRING
                        ) {
                            $value = trim($tokens[$j][1], "'\"");

                            $result[] = $value;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($result));
    }
}