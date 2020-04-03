<?php

namespace App\Tag\Twig;

use App\Tag\Tags;
use Generator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function preg_replace_callback;
use function sprintf;

final class TagsExtension extends AbstractExtension
{
    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function getFilters(): Generator
    {
        yield new TwigFilter('tag_links', function (string $string): string {
            return preg_replace_callback(
                Tags::TAGS_REGEXP,
                function (array $matches): string {
                    return sprintf('<a href="%s">#%s</a>',
                        $this->router->generate('tag_show', ['tag' => $matches[1]]),
                        $matches[1]
                    );
                },
                $string
            );
        });
    }
}
