<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Helper\StreamScanner;
use Allocine\TwigLinter\Helper\TokenSequence;
use Allocine\TwigLinter\Lexer;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * @var integer
     */
    private $macros;

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $scanner = new StreamScanner();
        $scanner->addSequence(['import', '@STRING', 'as', ['#@NAME', ','], '#@NAME'], [$this, 'matchDeclarations']);
        $scanner->addSequence(['import', '@STRING', 'as', '#@NAME'], [$this, 'matchDeclarations']);
        $scanner->addSequence(['#@NAME'], [$this, 'matchUsages']);

        $scanner->scan($tokens);

        foreach ($this->macros as $name => $line) {
            $this->addViolation($tokens->getFilename(), $line, sprintf('Unused macro "%s".', $name));
        }

        return $this->violations;
    }

    /**
     * @param \Twig_Token[] $matches
     */
    public function matchDeclarations(array $matches)
    {
        foreach ($matches as $macro) {
            $this->macros[$macro->getValue()] = $macro->getLine();
        }
    }

    /**
     * @param \Twig_Token[] $matches
     */
    public function matchUsages(array $matches)
    {
        $match = $matches[0];
        unset($this->macros[$match->getValue()]);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->macros = [];

        parent::reset();
    }
}
