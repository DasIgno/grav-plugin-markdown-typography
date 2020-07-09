<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class MarkdownTypographyPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            "onMarkdownInitialized" => ["onMarkdownInitialized", 0]
        ];
    }

    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event["markdown"];
        $config = $this->grav["config"]->get("plugins.".$this->name);

        // TODO: Once Grav requires Parsedown 1.8:
        // - $Element["name"] is no longer required, so remove redundant spans
        // - $Element["handler"] changes for extents with Markdown inside
        // - Do arrows starting with "<" work in that version?

        // Enable arrows (before dashes to avoid conflict)
        if ($config["arrows"]) {
            // Add inline types
            $markdown->addInlineType("-", "TypographyArrowSingleRight");
            $markdown->addInlineType("=", "TypographyArrowDoubleRight");
            //$markdown->addInlineType(">", "TypographyArrowBothLeft");

            // Add function to handle inline type
            $markdown->inlineTypographyArrowSingleRight = function($excerpt) {
                if (preg_match("/^(--?>)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "→",  // single right arrow (long arrow: ⟶)
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
                    elseif (preg_match("/^(--?<)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "←",  // single left arrow (long arrow: ⟵)
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
            };


            $markdown->inlineTypographyArrowDoubleRight = function($excerpt) {
                if (preg_match("/^(==?>)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "⇨",  // fat right arrow
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
                    elseif (preg_match("/^(==?<)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "⇦",  // fat left arrow
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
            };

            // TODO: Doesn't seem to work at the moment!?
            /*$markdown->inlineTypographyArrowBothLeft = function($excerpt) {
                $this->grav["debugger"]->addMessage($excerpt);
                if (preg_match("/^(.--?)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "←",  // thin left arrow
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
                    elseif (preg_match("/^(.==?)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "⇦",  // fat left arrow
                            "attributes" => [
                                "class" => "typography-arrow"
                            ]
                        ),
                    );
                }
            };*/
        }

        // Enable dashes
        if ($config["dashes"]) {
            // Add inline type
            $markdown->addInlineType("-", "TypographyDash");

            // Add function to handle inline type
            $markdown->inlineTypographyDash = function($excerpt) {
                if (preg_match("/-/", $excerpt["text"], $matches) && preg_match("/ (-) /", $excerpt["context"], $matches))
                {
                    return array(
                        "extent" => 1,
                        "element" => array(
                            "name" => "span",
                            "text" => "–",  // en dash
                        ),
                    );
                } elseif (preg_match("/^(---)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => 3,
                        "element" => array(
                            "name" => "span",
                            "text" => "—",  // em dash
                        ),
                    );
                } elseif (preg_match("/^(--)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => 2,
                        "element" => array(
                            "name" => "span",
                            "text" => "–",  // en dash
                        ),
                    );
                }
            };
        }

        // Enable smart-quotes
        if ($config["smart_quotes"]) {
            // Add inline types
            $markdown->addInlineType('"', "TypographyDoubleQuote");
            $markdown->addInlineType("'", "TypographySingleQuote");

            // Add function to handle double quotes
            $markdown->inlineTypographyDoubleQuote = function($excerpt) {
                if (preg_match('/^"(.*?)"/', $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[0]),
                        "element" => array(
                            "name" => "span",
                            "text" => "“".$matches[1]."”",  // double-quotes
                            'handler' => "line",
                        ),
                    );
                }
            };

            // Add function to handle single quotes (+ apostrophes)
            $markdown->inlineTypographySingleQuote = function($excerpt) {
                if (preg_match("/\w".preg_quote($excerpt["text"], "/")."/", $excerpt["context"], $matches))
                {
                    return array(
                        "extent" => 1,
                        "element" => array(
                            "name" => "span",
                            "text" => "’",  // apostrophe
                        ),
                    );
                } elseif (preg_match("/^'(.*?)'(?=\W?)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[0]),
                        "element" => array(
                            "name" => "span",
                            "text" => "‘".$matches[1]."’",  // single-quotes
                            'handler' => "line",
                        ),
                    );
                }
            };
        }

        // Enable ellipsis
        if ($config["ellipsis"]) {
            // Add inline type
            $markdown->addInlineType(".", "TypographyEllipsis");

            // Add function to handle inline type
            $markdown->inlineTypographyEllipsis = function($excerpt) {
                if (preg_match("/^(\.\.\.)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => 3,
                        "element" => array(
                            "name" => "span",
                            "text" => "…",  // ellipsis
                        ),
                    );
                }
            };
        }

        // Enable line breaks
        if ($config["line_breaks"]) {
            // Add inline type
            $markdown->addInlineType("\\", "TypographyLineBreaks");

            // Add function to handle inline type
            $markdown->inlineTypographyLineBreaks = function($excerpt) {
                if (preg_match("/\\\n/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => 1,
                        "element" => array(
                            "name" => "br",  // line break
                        ),
                    );
                }
            };
        }

        // Enable interrobang
        if ($config["interrobang"]) {
            // Add inline type
            $markdown->addInlineType("!", "TypographyInterrobang");

            // Add function to handle inline type
            $markdown->inlineTypographyInterrobang = function($excerpt) {
                if (preg_match("/^(!\?)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => 2,
                        "element" => array(
                            "name" => "span",
                            "text" => "‽",  // interrobang
                        ),
                    );
                }
            };
        }

        // Enable plus-minus
        if ($config["plus_minus"]) {
            // Add inline type
            $markdown->addInlineType("+", "TypographyPlusMinus");

            // Add function to handle inline type
            $markdown->inlineTypographyPlusMinus = function($excerpt) {
                if (preg_match("/^(\+\/?-)/", $excerpt["text"], $matches))
                {
                    return array(
                        "extent" => strlen($matches[1]),
                        "element" => array(
                            "name" => "span",
                            "text" => "±",  // plus-minus
                        ),
                    );
                }
            };
        }
    }
}
