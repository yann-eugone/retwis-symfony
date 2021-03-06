<?php

namespace App\Controller;

use App\Post\Form\PublishForm;
use App\Post\Form\PublishFormModel;
use App\Post\RecentlyPublished;
use App\Post\Voter\PublishVoter;
use App\View\ViewFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PostController extends AbstractPostController
{
    /**
     * @Route("/post/publish", name="post_publish", methods={Request::METHOD_GET, Request::METHOD_POST})
     */
    public function publish(Request $request): Response
    {
        $author = $this->getAuthenticatedUserOr403();

        $this->denyAccessUnlessGranted(PublishVoter::PUBLISH);

        $publish = new PublishFormModel();
        $form = $this->createForm(PublishForm::class, $publish);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('post/publish.html.twig', ['form' => $form->createView()]);
        }

        $post = $this->posts->publish($author->getId(), $publish->message);

        return $this->redirectToRoute('post_show', ['id' => $this->hashids->encode($post->getId())]);
    }

    /**
     * @Route("/post/{id}", name="post_show", methods=Request::METHOD_GET)
     */
    public function show(string $id, ViewFactory $views): Response
    {
        $post = $this->getPostByHashIdOr404($id);

        return $this->render('post/show.html.twig', [
            'post' => $views->post($post),
        ]);
    }

    public function recentlyPublished(RecentlyPublished $recentlyPublished, ViewFactory $views): Response
    {
        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $recentlyPublished->count();
        $count = 10;
        $start = $request->query->getInt('start');
        $posts = $recentlyPublished->list($start, $count);
        $postsListView = $views->posts($posts);

        return $this->render('post/list.html.twig', [
            'posts' => $postsListView,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}
