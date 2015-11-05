<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Helper\StreamScanner;
use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $scanner = new StreamScanner();
        $scanner->addSequence(['set', '#@NAME', '='], function (array $matches) use ($tokens) {
            if (!ctype_lower($matches[0]->getValue())) {
                $this->addViolation($tokens->getFilename(), $matches[0]->getLine(), sprintf('The "%s" variable should be in lower case (use _ as a separator).', $matches[0]->getValue()));
            }
        });

        $scanner->scan($tokens);

        return $this->violations;
    }
}
