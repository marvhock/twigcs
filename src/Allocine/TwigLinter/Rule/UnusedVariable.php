<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Helper\TokenSequence;
use Allocine\TwigLinter\Lexer;

class UnusedVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $variables = [];

        $declaration = new TokenSequence(['set', '#@NAME', '=']);
        $call        = new TokenSequence(['#@NAME']);

        while (!$tokens->isEOF()) {
            if ($declaration->match($tokens)) {
                $match = $declaration->getCaptures()[0];
                $variables[$match->getValue()] = $match->getLine();
                $this->advance($tokens, 3);
            } elseif ($call->match($tokens)) {
                $match = $call->getCaptures()[0];
                unset($variables[$match->getValue()]);
            }

            $tokens->next();
        }

        foreach ($variables as $name => $line) {
            $this->addViolation(
                $tokens->getFilename(),
                $line,
                sprintf('Unused variable "%s".', $name)
            );
        }

        return $this->violations;
    }
}
