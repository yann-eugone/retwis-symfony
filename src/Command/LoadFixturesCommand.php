<?php

namespace App\Command;

use App\Follow\Follow;
use App\Like\Like;
use App\Post\Post;
use App\Post\PostStorage;
use App\User\User;
use App\User\UserStorage;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Generator;
use Predis\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

final class LoadFixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures:load';

    private KernelInterface $kernel;

    private ClientInterface $redis;

    private UserStorage $users;

    private PostStorage $posts;

    private Like $like;

    private Follow $follow;

    public function __construct(
        KernelInterface $kernel,
        ClientInterface $redis,
        UserStorage $users,
        PostStorage $posts,
        Like $like,
        Follow $follow
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->redis = $redis;
        $this->users = $users;
        $this->posts = $posts;
        $this->like = $like;
        $this->follow = $follow;
    }

    protected function configure(): void
    {
        $this->addOption('users', 'u', InputOption::VALUE_REQUIRED, 'Users count to load', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!in_array($this->kernel->getEnvironment(), ['dev', 'test'], true)) {
            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $this->redis->flushall();

        $faker = FakerFactory::create();

        $users = iterator_to_array($this->users($input->getOption('users'), $io, $faker));
        $this->followers($io, $faker, $users);
        $posts = iterator_to_array($this->posts($io, $faker, $users));
        $this->likes($io, $faker, $posts, $users);

        return 0;
    }

    /**
     * @param int          $users
     * @param SymfonyStyle $io
     * @param Faker        $faker
     *
     * @return Generator&User[]
     */
    private function users(int $users, SymfonyStyle $io, Faker $faker): Generator
    {
        $idx = $users;
        $io->progressStart($users);
        do {
            $username = $faker->unique()->userName;
            $password = $username;
            if ($faker->boolean(80)) {
                $name = $faker->firstName;
                if ($faker->boolean(80)) {
                    $name .= ' ' . $faker->lastName;
                }
            } else {
                $name = $faker->userName;
            }

            $io->progressAdvance();

            $user = $this->users->register($name, $username, $password, $this->timestamp($faker));

            if ($faker->boolean(80)) {
                $bio = $faker->boolean(80) ? $faker->text(160) : null;
                $location = $faker->boolean(80) ? $faker->country : null;
                $website = null;

                if ($bio !== null || $location !== null || $website !== null) {
                    $user->fillProfile($name, $bio, $location, $website);
                    $this->users->update($user);
                }
            }

            yield $user->getId() => $user;
        } while (--$idx);

        $io->progressFinish();
        $io->success('Registered ' . $users . ' users');
    }

    /**
     * @param SymfonyStyle $io
     * @param Faker        $faker
     * @param User[]       $users
     */
    private function followers(SymfonyStyle $io, Faker $faker, array $users): void
    {
        // 90% users will follow at least one user
        $maxUsersFollowing = count($users);
        $minUsersFollowing = (int)round($maxUsersFollowing * 0.9);

        $followers = $faker->randomElements(
            $users,
            $countFollowers = $faker->numberBetween($minUsersFollowing, $maxUsersFollowing)
        );
        $countFollow = 0;
        $io->progressStart($countFollowers);
        /** @var User $follower */
        foreach ($followers as $follower) {
            $followingCandidates = $users;
            unset($followingCandidates[$follower->getId()]);

            // users will follow between 1 & 30% users
            $maxUserFollowing = (int)round(count($followingCandidates) * 0.3);
            $minUserFollowing = 1;
            $countFollow += $countFollowing = $faker->numberBetween($minUserFollowing, $maxUserFollowing);
            $following = $faker->randomElements($followingCandidates, $countFollowing);
            /** @var User $user */
            foreach ($following as $user) {
                $this->follow->follow($follower->getId(), $user->getId(),
                    $this->timestamp($faker, $user->getRegistered()));
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Generated ' . $countFollow . ' follow by ' . $countFollowers . ' users');
    }

    /**
     * @param SymfonyStyle $io
     * @param Faker        $faker
     * @param User[]       $users
     *
     * @return Generator&Post[]
     */
    private function posts(SymfonyStyle $io, Faker $faker, array $users): Generator
    {
        // 70% users will have at least published one post
        $maxUsersPublishing = count($users);
        $minUsersPublishing = (int)round($maxUsersPublishing * 0.7);

        $authors = $faker->randomElements(
            $users,
            $countAuthors = $faker->numberBetween($minUsersPublishing, $maxUsersPublishing)
        );
        $countPosts = 0;
        $io->progressStart($countAuthors);
        /** @var User $author */
        foreach ($authors as $author) {
            $idx = $faker->numberBetween(1, 100);
            $countPosts += $idx;
            do {
                $message = $faker->text($faker->numberBetween(10, 280));

                $post = $this->posts->publish(
                    $author->getId(),
                    $message,
                    $this->timestamp($faker, $author->getRegistered())
                );

                yield $post->getId() => $post;
            } while (--$idx);

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Generated ' . $countPosts . ' posts authored by ' . $countAuthors . ' users');
    }

    /**
     * @param SymfonyStyle $io
     * @param Faker        $faker
     * @param Post[]       $posts
     * @param User[]       $users
     */
    private function likes(SymfonyStyle $io, Faker $faker, array $posts, array $users): void
    {
        $countPosts = count($posts);
        $countUsers = count($users);

        // 80% of posts will have at least one like
        $maxPostWithLikes = $countPosts;
        $minPostWithLikes = (int)round($countPosts * 0.8);

        $postsWithLikes = $faker->randomElements(
            $posts,
            $countPostsWithLikes = $faker->numberBetween($minPostWithLikes, $maxPostWithLikes)
        );
        $countLikes = 0;
        $io->progressStart($countPostsWithLikes);
        /** @var Post $post */
        foreach ($postsWithLikes as $post) {
            // a post will have between 1 & 20% total users likes
            $maxPostLikes = (int)round($countUsers * 0.2);
            $minPostLikes = 1;

            $countLikes += $countUsersThatLike = $faker->numberBetween($minPostLikes, $maxPostLikes);
            $userThatLikes = $faker->randomElements($users, $countUsersThatLike);
            /** @var User $user */
            foreach ($userThatLikes as $user) {
                $this->like->like(
                    $post->getId(),
                    $user->getId(),
                    $this->timestamp($faker, max($user->getRegistered(), $post->getPublished()))
                );
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Generated ' . $countLikes . ' likes over ' . $countPostsWithLikes . ' posts');
    }

    private function timestamp(Faker $faker, $start = '-2 years'): int
    {
        if (is_int($start)) {
            $start = date(DATE_ATOM, $start);
        }

        return $faker->dateTimeBetween($start)->getTimestamp();
    }
}
