<?php

namespace App\Controller;

use App\Like\Like;
use App\Post\Form\PublishForm;
use App\Post\Form\PublishFormModel;
use App\Post\Post;
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
    private PostStorage $posts;

    private HashidsInterface $hashids;

    public function __construct(PostStorage $posts, HashidsInterface $hashids)
    {
        $this->posts = $posts;
        $this->hashids = $hashids;
    }

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
    public function show(string $id, UserStorage $users, Like $like): Response
    {
        $post = $this->getPostByHashIdOr404($id);

        return $this->render('post/show.html.twig', [
            'post' => PostView::new($post, $users, $this->hashids, $like),
        ]);
    }

    public function recentlyPublished(
        RecentlyPublished $recentlyPublished,
        UserStorage $users,
        Like $like
    ): Response {
        $posts = $recentlyPublished->list();
        $postsListView = PostListView::new($posts, $users, $this->hashids, $like);

        return $this->render('post/recently-published.html.twig', [
            'posts' => $postsListView,
        ]);
    }

    private function getPostByHashIdOr404(string $hash): Post
    {
        $id = $this->hashids->decode($hash)[0] ?? null;
        if ($id === null) {
            throw $this->createNotFoundException();
        }

        try {
            return $this->posts->get((int)$id);
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }
    }
}
