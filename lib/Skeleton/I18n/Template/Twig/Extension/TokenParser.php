<?php
/**
 * Twig translation (tokenparser)
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n\Template\Twig\Extension;

class TokenParser extends \Twig_TokenParser {
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A \Twig_Token instance
     * @return \Twig_NodeInterface A \Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token) {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $count = null;
        $plural = null;

        if (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse([$this, 'decideForFork']);
            if ('plural' === $stream->next()->getValue()) {
                $count = $this->parser->getExpressionParser()->parseExpression();
                $stream->expect(\Twig_Token::BLOCK_END_TYPE);
                $plural = $this->parser->subparse([$this, 'decideForEnd'], true);
            }
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $this->checkTransString($body, $lineno);

        return new \Twig_Extensions_Node_Trans_Tigron($body, $plural, $count, $lineno, $this->getTag());
    }

    public function decideForFork($token) {
        return $token->test(['plural', 'endtrans']);
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

    protected function checkTransString(\Twig_NodeInterface $body, $lineno) {
        foreach ($body as $i => $node) {
            if (
                $node instanceof \Twig_Node_Text
                ||
                ($node instanceof \Twig_Node_Print && $node->getNode('expr') instanceof \Twig_Node_Expression_Name)
            ) {
                continue;
            }

            throw new \Twig_Error_Syntax(sprintf('The text to be translated with "trans" can only contain references to simple variables'), $lineno);
        }
    }
}
