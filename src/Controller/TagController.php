<?php

namespace App\Controller;

use App\Post\PostStorage;
use App\Tag\Tags;
use App\View\ViewFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TagController extends Controller
{
    private Tags $tags;

    public function __construct(Tags $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @Route("/explore/tags", name="tag_expore", methods=Request::METHOD_GET)
     */
    public function explore(Request $request): Response
    {
        $total = $this->tags->tagCount();
        $count = 10;
        $start = $request->query->getInt('start');

        $trending = $this->tags->trendingTags($start, $count);

        return $this->render('tag/explore.html.twig', [
            'trending' => $trending,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    /**
     * @Route("/tag/{tag}", name="tag_show", methods=Request::METHOD_GET)
     */
    public function show(Request $request, string $tag, PostStorage $posts, ViewFactory $views): Response
    {
        $emergence = $this->tags->tagEmergence($tag);
        if ($emergence === null) {
            throw $this->createNotFoundException();
        }

        $total = $this->tags->postCount($tag);
        $count = 10;
        $start = $request->query->getInt('start');

        $ids = $this->tags->listPostIds($tag, $start, $count);

        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
            'emergence' => $emergence,
            'posts' => $views->posts($posts->list($ids)),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    public function trending(): Response
    {
        $trending = $this->tags->trendingTags(0, 10);

        return $this->render('tag/top-trending.html.twig', [
            'trending' => $trending,
            'start' => 0,
        ]);
    }
}
