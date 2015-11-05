<?php

namespace Allocine\TwigLinter\Helper;

use Allocine\TwigLinter\Lexer;

class TokenSequence
{
    /**
     * @var array
     */
    protected $sequence;

    /**
     * @var \Twig_token[]
     */
    protected $captures;

    /**
     * @var integer
     */
    protected $cursor;

    /**
     * @param array $sequence
     */
    public function __construct(array $sequence)
    {
        $this->sequence = $sequence;
        $this->captures = [];
    }

    /**
     * @return \Twig_Token[]
     */
    public function getCaptures()
    {
        return $this->captures;
    }

    /**
     * @param \Twig_Token[] $captures
     */
    public function addCaptures(array $captures)
    {
        $this->captures = array_merge($this->captures, $captures);
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $offset
     *
     * @return boolean
     */
    public function match(\Twig_TokenStream $tokens, $offset = 0)
    {
        $this->captures = [];
        $this->cursor = $offset;

        $i = $offset;
        $j = 0;
        $state = 'pass';
        $size = count($this->sequence);

        while (($state !== 'fail') && $j < $size) {
            if (is_array($this->sequence[$j])) {
                $subSequence = new TokenSequence($this->sequence[$j]);
                while ($subSequence->match($tokens, $i)) {
                    $this->addCaptures($subSequence->getCaptures());
                    $i = $subSequence->cursor;
                }
                $state = 'pass';
            } else {
                $state = $this->test($this->sequence[$j], $tokens->look($i));
            }

            if ($state !== 'skip') {
                $j++;
            }

            $i++;
        }

        $this->cursor = $i;

        return $state !== 'fail';
    }

    /**
     * @param string      $keyword
     * @param \Twig_Token $token
     *
     * @return string
     */
    protected function test($keyword, \Twig_Token $token)
    {
        $capture = false;

        if ($keyword[0] === '#') {
            $capture = true;
            $keyword = ltrim($keyword, '#');
        }

        if ($keyword === '@ALL') {
            $state = 'pass';
        } else if ($token->getType() === Lexer::WHITESPACE_TYPE && $keyword !== '@WHITESPACE') {
            $state = 'skip';
        } else if ($keyword[0] === '@') {
            $state = $token->getType() === $this->mapTokenType(ltrim($keyword, '@')) ? 'pass' : 'fail';
        } else {
            $state = $token->getValue() === $keyword ? 'pass' : 'fail';
        }

        if ($state === 'pass' && $capture) {
            $this->captures[]= $token;
        }

        return $state;
    }

    /**
     * @param string $name
     *
     * @return integer
     */
    protected function mapTokenType($name)
    {
        return [
            'WHITESPACE' => Lexer::WHITESPACE_TYPE,
            'EOF' => \Twig_Token::EOF_TYPE,
            'TEXT' => \Twig_Token::TEXT_TYPE,
            'BLOCK_START' => \Twig_Token::BLOCK_START_TYPE,
            'VAR_START' => \Twig_Token::VAR_START_TYPE,
            'BLOCK_END' => \Twig_Token::BLOCK_END_TYPE,
            'VAR_END' => \Twig_Token::VAR_END_TYPE,
            'NAME' => \Twig_Token::NAME_TYPE,
            'NUMBER' => \Twig_Token::NUMBER_TYPE,
            'STRING' => \Twig_Token::STRING_TYPE,
            'OPERATOR' => \Twig_Token::OPERATOR_TYPE,
            'PUNCTUATION' => \Twig_Token::PUNCTUATION_TYPE,
            'INTERPOLATION_START' => \Twig_Token::INTERPOLATION_START_TYPE,
            'INTERPOLATION_END' => \Twig_Token::INTERPOLATION_END_TYPE,
        ][$name];
    }
}
