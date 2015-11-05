<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Helper\TokenSequence;
use Allocine\TwigLinter\Lexer;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $macros = [];

        $importMany   = new TokenSequence(['import', '@STRING', 'as', ['#@NAME', ','], '#@NAME']);
        $importSingle = new TokenSequence(['import', '@STRING', 'as', '#@NAME']);
        $call         = new TokenSequence(['#@NAME']);

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($importMany->match($tokens)) {
                foreach ($importMany->getCaptures() as $macro) {
                    $macros[$macro->getValue()] = $macro->getLine();
                }
                $this->advance($tokens, $importMany->getRelativeCursor());
            } elseif ($importSingle->match($tokens)) {
                $macro = $importSingle->getCaptures()[0];
                $macros[$macro->getValue()] = $macro->getLine();
                $this->advance($tokens, $importSingle->getRelativeCursor());
            } elseif ($call->match($tokens)) {
                $match = $call->getCaptures()[0];
                unset($macros[$match->getValue()]);
            }

            $tokens->next();
        }


        foreach ($macros as $name => $line) {
            $this->addViolation(
                $tokens->getFilename(),
                $line,
                sprintf('Unused macro "%s".', $name)
            );
        }

        return $this->violations;
    }
}
