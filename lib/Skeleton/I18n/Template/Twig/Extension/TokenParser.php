<?php
/**
 * Twig translation (tokenparser)
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n\Template\Twig\Extension;

class TokenParser extends \Twig\TokenParser\AbstractTokenParser {
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig\Token $token A \Twig\Token instance
     * @return \Twig_NodeInterface A \Twig_NodeInterface instance
     */
    public function parse(\Twig\Token $token) {
        $line = $token->getLine();
        $stream = $this->parser->getStream();

        if (!$stream->test(\Twig\Token::BLOCK_END_TYPE)) {
            $value = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(\Twig\Token::BLOCK_END_TYPE);
            $value = $this->parser->subparse([$this, 'decideForFork']);
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        //$this->checkTransString($value, $line);

        return new Node\Trans\Tigron('trans', $value, $line, $this->getTag());
    }

    public function decideForFork($token) {
        return $token->test(['endtrans']);
    }

    public function decideForEnd($token) {
        return $token->test('endtrans');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @param string The tag name
     */
    public function getTag() {
        return 'trans';
    }

    protected function checkTransString(\Twig\Node\Expression\ConstantExpression $body, $line) {
        foreach ($body as $i => $node) {
            if (
                $node instanceof \Twig_Node_Text
                ||
                ($node instanceof \Twig_Node_Print && $node->getNode('expr') instanceof \Twig_Node_Expression_Name)
            ) {
                continue;
            }

            throw new \Twig_Error_Syntax(sprintf('The text to be translated with "trans" can only contain references to simple variables'), $line);
        }
    }
}
