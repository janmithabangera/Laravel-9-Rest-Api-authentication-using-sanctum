<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\ArticleResource;
use App\Http\Controllers\API\BaseController;

class ArticleController extends BaseController
{
    public function index()
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        if (isset($profile) && $profile->role == Config::get('constants.roles.editor')) {
            $articles = Article::all();
        } else {
            $articles = Article::where('user_id', Auth::user()->id)->get();
        }

        if (count($articles) == 0) {
            return $this->sendResponse(ArticleResource::collection($articles), 'No articles found.');
        }
        return $this->sendResponse(ArticleResource::collection($articles), 'Articles retrieved successfully.');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required|unique:articles',
            'post' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $profile = Profile::where('user_id', Auth::user()->id)->first();
        if (isset($profile) && $profile->role == Config::get('constants.roles.writer')) {
            $article = new Article();
            $article->title = $request->title;
            $article->post = $request->post;
            $article->user_id = Auth::user()->id;
            $article->save();
            return $this->sendResponse(new ArticleResource($article), 'Article created successfully.');
        }
        return $this->sendError('Article not Created.', []);
    }

    public function show(Request $request)
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        if (isset($profile) && $profile->role == Config::get('constants.roles.writer')) {
            $article = Article::where('id', $request->route('article_id'))->where('user_id', Auth::user()->id)->first();
        } else {
            $article = Article::where('id', $request->route('article_id'))->first();
        }

        if (isset($article) && isset($profile)) {
            $comments = Comment::where('article_id', $article->id)->get();
            if (!empty($comments)) {
                $article->comments = count($comments) > 0 ? $comments : "No comments found.";
            }
            return $this->sendResponse(new ArticleResource($article), 'Article retrieved successfully.');
        }
        return $this->sendError('Article not found.', []);
    }

    public function update(Request $request, Article $article)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'post' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $article = Article::where('id', $request->route('article_id'))->where('user_id', Auth::user()->id)->first();

        $profile = Profile::where('user_id', Auth::user()->id)->first();
        if (isset($article) && isset($profile) && $profile->role == Config::get('constants.roles.writer')) {
            $article->title = $request->title;
            $article->post = $request->post;
            $article->save();
            return $this->sendResponse(new ArticleResource($article), 'Article updated successfully.');
        }
        return $this->sendError('Article not updated', []);
    }

    public function destroy(Request $request)
    {
        $profile = Profile::where('user_id', Auth::user()->id)->first();
        if (isset($profile) && $profile->role == Config::get('constants.roles.writer')) {
            $articlesExists = Article::where('user_id', Auth::user()->id)->where('id', $request->route('article_id'))->first();
            if (isset($articlesExists)) {
                $articlesExists->delete();
                return $this->sendResponse([], 'Article deleted successfully.');
            }
            return $this->sendError('Article already deleted', []);
        }
        return $this->sendError('Article not deleted.', []);
    }

    public function addComment(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'discription' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $article = Article::where('id', $request->route('article_id'))->first();
        $profile = Profile::where('user_id', Auth::user()->id)->first();

        if (isset($article) && isset($profile) && ($profile->role == Config::get('constants.roles.editor'))) {
            $comment = new Comment();
            $comment->discription = $request->discription;
            $comment->article_id = $article->id;
            $comment->save();
            return $this->sendResponse([], 'Comment added successfully.');
        }
        return $this->sendError('Comment addition unsuccessful.', []);
    }
}
