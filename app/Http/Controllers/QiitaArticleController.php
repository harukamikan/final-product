<?php

namespace App\Http\Controllers;

use App\Models\QiitaArticle;
use Illuminate\Http\Request;

class QiitaArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = QiitaArticle::with('user')
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('qiita.index', compact('articles'));
    }
}
