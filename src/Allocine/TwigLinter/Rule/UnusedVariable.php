<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Helper\StreamScanner;
use Allocine\TwigLinter\Helper\TokenSequence;
use Allocine\TwigLinter\Lexer;

class UnusedVariable extends AbstractRule implements RuleInterface
{
    /**
     * @var integer[]
     */
    private $variables = [];

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $scanner = new StreamScanner();
        $scanner->addSequence(['set', '#@NAME', '='], [$this, 'matchDeclarations']);
        $scanner->addSequence(['#@NAME'], [$this, 'matchUsages']);

        $scanner->scan($tokens);

        foreach ($this->variables as $name => $line) {
            $this->addViolation($tokens->getFilename(), $line, sprintf('Unused variable "%s".', $name));
        }

        return $this->violations;
    }

    /**
     * @param \Twig_Token[] $matches
     */
    public function matchDeclarations(array $matches)
    {
        $match = $matches[0];
        $this->variables[$match->getValue()] = $match->getLine();
    }

    /**
     * @param \Twig_Token[] $matches
     */
    public function matchUsages(array $matches)
    {
        $match = $matches[0];
        unset($this->variables[$match->getValue()]);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->variables = [];

        parent::reset();
    }
}
