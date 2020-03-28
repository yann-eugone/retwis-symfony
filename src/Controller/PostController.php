<?php

namespace App\Controller;

use App\Post\Form\PublishForm;
use App\Post\Form\PublishFormModel;
use App\Post\PostStorage;
use App\Post\RecentlyPublished;
use App\Post\Voter\PublishVoter;
use App\Redis\NotFoundException;
use App\User\UserStorage;
use App\View\PostListView;
use App\View\PostView;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PostController extends Controller
{
    /**
     * @Route("/post/publish", name="post_publish", methods={Request::METHOD_GET, Request::METHOD_POST})
     */
    public function publish(Request $request, PostStorage $posts, HashidsInterface $hashids): Response
    {
        $author = $this->getAuthenticatedUserOr403();

        $this->denyAccessUnlessGranted(PublishVoter::PUBLISH);

        $publish = new PublishFormModel();
        $form = $this->createForm(PublishForm::class, $publish);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('post/publish.html.twig', ['form' => $form->createView()]);
        }

        $post = $posts->publish($author->getId(), $publish->message);

        return $this->redirectToRoute('post_show', ['id' => $hashids->encode($post->getId())]);
    }

    /**
     * @Route("/post/{id}", name="post_show", methods=Request::METHOD_GET)
     */
    public function show(string $id, HashidsInterface $hashids, PostStorage $posts, UserStorage $users): Response
    {
        $id = $hashids->decode($id)[0] ?? null;
        if ($id === null) {
            throw $this->createNotFoundException();
        }

        $id = (int)$id;
        try {
            $post = $posts->get($id);
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }

        return $this->render('post/show.html.twig', [
            'post' => PostView::new($post, $users, $hashids),
        ]);
    }

    public function recentlyPublished(
        RecentlyPublished $recentlyPublished,
        HashidsInterface $hashids,
        UserStorage $users
    ): Response {
        $posts = $recentlyPublished->list();
        $postsListView = PostListView::new($posts, $users, $hashids);

        return $this->render('post/recently-published.html.twig', [
            'posts' => $postsListView,
        ]);
    }
}
