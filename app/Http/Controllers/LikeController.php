<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $userId = (int) Auth::id();
        $type = (string) $request->input('type', '');
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['error' => 'invalid_id'], Response::HTTP_BAD_REQUEST);
        }

        switch ($type) {
            case 'event':
                return $this->toggleEventLike($id, $userId);
            case 'bar':
                return $this->toggleGenericLike('bar', $id, $userId);
            case 'product':
                return $this->toggleGenericLike('product', $id, $userId);
            case 'ticket':
                return $this->toggleGenericLike('ticket', $id, $userId);
            case 'story':
                return $this->toggleGenericLike('story', $id, $userId);
        }

        return response()->json(['error' => 'invalid_type'], Response::HTTP_BAD_REQUEST);
    }

    protected function toggleEventLike(int $eventId, int $userId)
    {
        $table = 'evt_interesse_evento_tb';
        $exists = DB::table($table)
            ->where('evento_id', $eventId)
            ->where('user_id', $userId)
            ->where('type', 'like')
            ->exists();
        if ($exists) {
            DB::table($table)
                ->where('evento_id', $eventId)
                ->where('user_id', $userId)
                ->where('type', 'like')
                ->delete();
            $liked = false;
        } else {
            DB::table($table)->insert([
                'evento_id' => $eventId,
                'user_id'   => $userId,
                'type'      => 'like',
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);
            $liked = true;
        }
        $count = DB::table($table)
            ->where('evento_id', $eventId)
            ->where('type', 'like')
            ->count();
        return response()->json(['liked' => $liked, 'likes' => $count]);
    }

    protected function toggleGenericLike(string $entityType, int $entityId, int $userId)
    {
        $table = 'user_post_likes_tb';
        $postTable = 'user_posts_tb';
        $post = DB::table($postTable)
            ->where('posted_as_type', $entityType)
            ->where('posted_as_id', $entityId)
            ->first();
        if (!$post) {
            $postId = DB::table($postTable)->insertGetId([
                'user_id'        => $userId,
                'owner_user_id'  => $userId,
                'owner_bar_id'   => null,
                'posted_as_type' => $entityType,
                'posted_as_id'   => $entityId,
                'visibility'     => 'public',
                'likes_count'    => 0,
                'comments_count' => 0,
                'caption'        => null,
                'image_url'      => null,
                'location_lat'   => null,
                'location_lng'   => null,
                'neighborhood'   => null,
                'location_id'    => null,
                'created_at'     => now(),
                'updated_at'     => now(),
                'deleted_at'     => null,
            ]);
        } else {
            $postId = (int) $post->post_id;
        }

        $like = DB::table($table)
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($like) {
            DB::table($table)
                ->where('id', $like->id)
                ->delete();
            DB::table($postTable)
                ->where('post_id', $postId)
                ->update([
                    'likes_count' => DB::raw('GREATEST(likes_count-1,0)'),
                    'updated_at'  => now(),
                ]);
            $liked = false;
        } else {
            DB::table($table)->insert([
                'user_id'   => $userId,
                'post_id'   => $postId,
                'created_at'=> now(),
                'update_at' => now(),
                'delete_at' => null,
            ]);
            DB::table($postTable)
                ->where('post_id', $postId)
                ->update([
                    'likes_count' => DB::raw('likes_count+1'),
                    'updated_at'  => now(),
                ]);
            $liked = true;
        }

        $countRow = DB::table($postTable)
            ->where('post_id', $postId)
            ->first(['likes_count']);
        $count = $countRow ? (int) $countRow->likes_count : 0;

        return response()->json(['liked' => $liked, 'likes' => $count]);
    }
}
