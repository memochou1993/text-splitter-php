<?php

class TextSplitter
{
    private $chunkSize;
    private $chunkOverlap;
    private $separators;

    public function __construct($chunkSize, $chunkOverlap, $separators)
    {
        $this->chunkSize = $chunkSize;
        $this->chunkOverlap = $chunkOverlap;
        $this->separators = $separators;
    }

    public function splitText($filePath)
    {
        $chunks = [];
        $stream = fopen($filePath, 'r');

        if ($stream) {
            $currentChunk = '';
            $remainingText = '';

            while (!feof($stream)) {
                $buffer = fread($stream, $this->chunkSize + $this->chunkOverlap);
                $currentChunk .= $buffer;

                $lastSeparatorPos = $this->findLastSeparator($currentChunk);

                $remainingText = '';
                if ($lastSeparatorPos !== false && $lastSeparatorPos >= $this->chunkSize - $this->chunkOverlap) {
                    $remainingText = mb_substr($currentChunk, $lastSeparatorPos);
                    $currentChunk = mb_substr($currentChunk, 0, $this->chunkSize + $this->chunkOverlap);
                }

                $chunks[] = trim($currentChunk);
                $currentChunk = $remainingText;
            }

            fclose($stream);

            if ($remainingText) {
                $chunks[] = trim($remainingText);
            }
        }

        return $chunks;
    }

    private function findLastSeparator($text)
    {
        for ($i = mb_strlen($text) - 1; $i >= 0; $i--) {
            $char = mb_substr($text, $i, 1);
            if (in_array($char, $this->separators)) {
                return $i;
            }
        }

        return false;
    }
}

$splitter = new TextSplitter(512, 128, ["\n", ' ', ',', '.', '，', '。']);

$result = $splitter->splitText('./input.txt');

$data = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

file_put_contents('./output.txt', $data);
