<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ArticlesResource;
use Illuminate\Support\Arr;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function scrapper()
    {
        // $collection =  collect();
        // $collection->push(['result' => "Success"]);
        // $collection->all();
        // return new ArticlesResource($collection);
        //           Done
        //https://edubirdies.net/category/homework-help/page/;
        //https://purduepapers.com/2022/02/25/
        //https://nerdmypaper.com/2022/02/25/
        //https://skilledpapers.com/2022/02/25/
        //https://writerstask.com/2022/02/25/
        //https://thecustomessays.com/author/admin/
        //https://elitecustomwritings.com/wp-json/wp/v2/posts
        //https://perfectresearchpapers.com/wp-json/wp/v2/posts

    }
}
