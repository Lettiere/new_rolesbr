<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoryUploadController extends Controller
{
    public function upload(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $request->validate([
            'image' => ['required','image','max:5120'],
            'overlay_json' => ['nullable','string'],
        ]);
        $userId = (int) Auth::id();
        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $name = time().'_'.Str::random(10).'.'.$ext;
        $dir = public_path('uploads/perfis/'.$userId.'/stories');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file->move($dir, $name);
        $rel = 'uploads/perfis/'.$userId.'/stories/'.$name;
        DB::table('user_stories_tb')->insert([
            'user_id'    => $userId,
            'image_url'  => $rel,
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'deleted_at' => null,
            'overlay_json' => $request->input('overlay_json') ?: null,
        ]);
        return back()->with('status', 'Story enviado');
    }

    public function destroy(Request $request, int $story)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }
        $userId = (int) Auth::id();
        $row = DB::table('user_stories_tb')->where('story_id', $story)->first();
        if (!$row || (int) $row->user_id !== $userId) {
            return response()->json(['error' => 'not_found'], 404);
        }
        DB::table('user_stories_tb')->where('story_id', $story)->update([
            'deleted_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
        return response()->json(['ok' => true]);
    }
}
