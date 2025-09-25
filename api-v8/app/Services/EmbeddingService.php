<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class EmbeddingService
{
    public function generate($text)
    {
        $process = new Process(['python3', 'scripts/generate_embedding.py', $text]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception('Embedding generation failed');
        }
        return json_decode($process->getOutput(), true);
    }
}
