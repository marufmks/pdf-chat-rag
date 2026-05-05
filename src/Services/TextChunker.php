<?php
declare(strict_types=1);

namespace PDFChatRAG\Services;

class TextChunker {
    public function split(string $text, int $chunkSize = 1000, int $chunkOverlap = 200): array {
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (strlen($text) <= $chunkSize) {
            return $text ? [$text] : [];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks    = [];
        $current   = '';

        foreach ($sentences as $sentence) {
            $candidate = $current ? $current . ' ' . $sentence : $sentence;

            if (strlen($candidate) <= $chunkSize) {
                $current = $candidate;
                continue;
            }

            if ($current) {
                $chunks[] = $current;
            }

            if (strlen($sentence) > $chunkSize) {
                $current = $this->hardSplit($sentence, $chunkSize);
                $chunks  = array_merge($chunks, $current['chunks']);
                $current = $current['remainder'];
            } else {
                $current = $sentence;
            }
        }

        if ($current) {
            $chunks[] = $current;
        }

        if ($chunkOverlap > 0 && count($chunks) > 1) {
            for ($i = 1; $i < count($chunks); $i++) {
                $prev        = $chunks[$i - 1];
                $overlapText = substr($prev, -min($chunkOverlap, strlen($prev)));
                $chunks[$i]  = trim($overlapText . ' ' . $chunks[$i]);
            }
        }

        return array_values(array_filter($chunks));
    }

    private function hardSplit(string $text, int $chunkSize): array {
        $words   = explode(' ', $text);
        $chunks  = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current ? $current . ' ' . $word : $word;
            if (strlen($candidate) <= $chunkSize) {
                $current = $candidate;
            } else {
                if ($current) {
                    $chunks[] = $current;
                }
                $current = $word;
            }
        }

        return [
            'chunks'    => $chunks,
            'remainder' => $current,
        ];
    }
}
