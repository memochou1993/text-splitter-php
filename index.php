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

    public function splitText($text)
    {
        return $this->split($text);
    }

    private function split($text)
    {
        if (mb_strlen($text) <= $this->chunkSize) {
            return [$text];
        }

        $currentChunk = mb_substr($text, 0, $this->chunkSize);

        $lastSeparatorPos = $this->findLastSeparator($currentChunk);

        $overlap = $this->chunkOverlap;
        if ($lastSeparatorPos !== false && $lastSeparatorPos >= $this->chunkSize - $overlap) {
            $overlap = $this->chunkSize - $lastSeparatorPos;
        }

        $currentChunk = mb_substr($text, 0, $this->chunkSize + $overlap);

        $remainingText = mb_substr($text, $this->chunkSize - $overlap);

        $remainingChunks = $this->split($remainingText);

        $result = [$currentChunk];
        $result = array_merge($result, $remainingChunks);

        return $result;
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

$splitter = new TextSplitter(20, 10, [" ", "。"]);
$text = file_get_contents('./content.txt');
$result = $splitter->splitText($text);

print_r($result);
