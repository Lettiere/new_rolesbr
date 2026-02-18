<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicStoriesController extends Controller
{
    public function index()
    {
        return view('site.list_stories');
    }

    public function all(Request $request)
    {
        $now = now()->format('Y-m-d H:i:s');
        $limit = (int) $request->query('limit', 12);
        if ($limit < 1) {
            $limit = 12;
        }

        $userRows = DB::table('user_stories_tb as s')
            ->select('s.story_id', 's.image_url', 's.user_id', 's.created_at', 'u.name as name')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->whereNull('s.deleted_at')
            ->where('s.expires_at', '>=', $now)
            ->orderByDesc('s.created_at')
            ->limit($limit)
            ->get()
            ->toArray();

        $barRows = DB::table('bar_stories_tb as s')
            ->select('s.story_id', 's.image_url', 's.bares_id', 's.user_id', 's.created_at', 'b.nome as name')
            ->leftJoin('form_perfil_bares_tb as b', 'b.bares_id', '=', 's.bares_id')
            ->whereNull('s.deleted_at')
            ->where('s.expires_at', '>=', $now)
            ->orderByDesc('s.created_at')
            ->limit($limit)
            ->get()
            ->toArray();

        $normalize = function (&$row) {
            if (!empty($row->image_url)) {
                $val = str_replace('\\', '/', (string) $row->image_url);
                $prefix = str_replace('\\', '/', public_path().DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = public_path(DIRECTORY_SEPARATOR.ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR));
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $row->image_url = null;
                } else {
                    $row->image_url = $val;
                }
            }
            if (empty($row->name)) {
                $row->name = 'Story';
            }
        };

        foreach ($userRows as &$r) {
            $r->source = 'user';
            $normalize($r);
        }
        unset($r);
        foreach ($barRows as &$r2) {
            $r2->source = 'bar';
            $normalize($r2);
        }
        unset($r2);

        $all = array_merge($userRows, $barRows);
        usort($all, function ($a, $b) {
            return strcmp((string) ($b->created_at ?? ''), (string) ($a->created_at ?? ''));
        });
        if (count($all) > $limit) {
            $all = array_slice($all, 0, $limit);
        }

        $storyIds = array_values(array_unique(array_map(function ($row) {
            return (int) ($row->story_id ?? 0);
        }, $all)));

        $likesByStory = [];
        $likedPosts = [];

        if (!empty($storyIds)) {
            $posts = DB::table('user_posts_tb')
                ->where('posted_as_type', 'story')
                ->whereIn('posted_as_id', $storyIds)
                ->get(['post_id', 'posted_as_id', 'likes_count']);

            foreach ($posts as $post) {
                $likesByStory[(int) $post->posted_as_id] = [
                    'post_id' => (int) $post->post_id,
                    'likes' => (int) $post->likes_count,
                ];
            }

            if (Auth::check() && !empty($likesByStory)) {
                $userId = (int) Auth::id();
                $postIds = array_column($likesByStory, 'post_id');
                $userLikes = DB::table('user_post_likes_tb')
                    ->where('user_id', $userId)
                    ->whereIn('post_id', $postIds)
                    ->pluck('post_id')
                    ->all();
                $likedPosts = array_map('intval', $userLikes);
            }
        }

        foreach ($all as &$row) {
            $sid = (int) ($row->story_id ?? 0);
            $postMeta = $likesByStory[$sid] ?? ['post_id' => 0, 'likes' => 0];
            $row->likes = $postMeta['likes'];
            $row->liked_by_me = in_array($postMeta['post_id'], $likedPosts, true);
        }
        unset($row);

        return response()->json($all);
    }
}
