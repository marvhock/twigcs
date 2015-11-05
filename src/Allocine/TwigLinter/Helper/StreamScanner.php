<?php

namespace Allocine\TwigLinter\Helper;

class StreamScanner
{
    /**
     * @var array
     */
    private $sequences;

    /**
     * @param array|TokenSequence $sequence
     * @param callable            $callable
     */
    public function addSequence($sequence, callable $callable)
    {
        $this->sequences[] = [
            'sequence' => is_array($sequence) ? new TokenSequence($sequence) : $sequence,
            'callable' => $callable
        ];
    }

    /**
     * @param \Twig_TokenStream $tokens
     */
    public function scan(\Twig_TokenStream $tokens)
    {
        while (!$tokens->isEOF()) {
            $matched = false;

            foreach ($this->sequences as $sequence) {
                if ($sequence['sequence']->match($tokens)) {
                    $matched = true;
                    $this->advance($tokens, $sequence['sequence']->getRelativeCursor());
                    call_user_func($sequence['callable'], $sequence['sequence']->getCaptures());
                }
            }

            if (!$matched) {
                $tokens->next();
            }
        }
    }

    /**
     * @param integer $amount
     */
    private function advance(\Twig_TokenStream $tokens, $amount)
    {
        for ($i=0; $i<$amount; $i++) {
            $tokens->next();
        }
    }
}
