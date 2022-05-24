<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use KubAT\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use App\Models\Article;
use App\Http\Resources\ArticlesResource;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArticlesExport;
use App\Models\Url;


class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function export($id, $date)
    {
        return Excel::download(new ArticlesExport($id, $date), 'articles.xlsx');
    }

    public function clear()
    {
        $article = Article::get();
        foreach ($article as $item) {
            $a = Article::findOrFail($item->id);
            $a->status = 0;
            $a->save();
        }
        echo "Done";
    }

    public function updateArticle(Request $request)
    {
        $article = Article::findOrFail($request->input('id'));
        $article->title = $request->input('title');
        $article->description = $request->input('description');
        if ($article->edited == 0) {
            $article->edited = 1;
        }
        $article->save();
        $response["message"] = "Record has been updated successfully";
        return new ArticlesResource($response);
    }

    public function delete($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        $response["message"] = "Success. Finished Deleting";
        return new ArticlesResource($response);
    }

    public function deleteBulk(Request $request)
    {
        $article_ids = $request->input('ids');
        foreach ($article_ids as $id) {
            $article = Article::findOrFail($id);
            $article->delete();
        }
        $response["message"] = "Success. Finished Deleting";
        return new ArticlesResource($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        return new ArticlesResource($article);
    }

    public function companies()
    {
        $url = Url::get();
        return new ArticlesResource($url);
    }

    public function getArticles()
    {

        $url = Url::get();
        foreach ($url as $link_web) {
            if ($link_web->name === "purduepapers") {
                $date_today = Carbon::now()->format('Y/m/d');
                $url2 = $link_web->url . $date_today . '/page/' . 1 . '/';
            } else {
                $url2 = $link_web->url;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            $dom = HtmlDomParser::str_get_html($response);

            foreach ($dom->find('section.pt-5 > div.container > div.row > div.col-lg-8 > article > div.entry-meta > span') as $test) {
                $link = $test->find('a')[0]->href;
                $date_created = $test->find('a time')[0]->innertext;

                $now = Carbon::now();
                $date = Carbon::parse($now)->toDateString();
                $createdAt = Carbon::parse($date_created);
                $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $createdAt)->format('Y-m-d');

                if ($date === $createdAt) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $link);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $dom = HtmlDomParser::str_get_html($response);

                    foreach ($dom->find('section.pt-5 > div.container > div.row > div.col-lg-8') as $test) {
                        $desc = collect();
                        $p = "";
                        foreach ($test->find('div > p') as $u) {
                            if (!is_null(strip_tags($u->innertext))) {
                                $desc->push(['description' => strip_tags($u->innertext)]);
                                $p = '<p>' . $p . $u->innertext . '</p>';
                            }
                        }
                        $desc->all();
                        $getArticles = Article::where('title', $test->find('h1')[0]->innertext)->exists();
                        if (!$getArticles) {
                            $article = new Article();
                            $article->title = $test->find('h1')[0]->innertext;
                            $article->description = $p;
                            $article->url_id = $link_web->id;
                            $article->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @var string[]
     */
    protected $sortFields = ['title'];
    /**
     * Display a paginated listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function datatableIndex(Request $request)
    {
        //Pagination and sorting
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField = in_array($sortFieldInput, $this->sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput = $request->input('search');
        $perPage = $request->input('per_page') ?? self::PER_PAGE;
        $companySort =  $request->input('company_name');

        $query = Article::where('status', 0)->orderBy($sortField, $sortOrder);

        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query = $query->where('title', 'like', $searchQuery)
                ->orWhere('description', 'like', $searchQuery);
        }

        if (!is_null($companySort)) {
            if (!$companySort == 0) {
                $query = $query->where('url_id', $companySort);
            }
        }

        $articles = $query->paginate((int)$perPage);

        return ArticlesResource::collection($articles);
    }

    public function status($id)
    {
        $article = Article::findOrFail($id);

        if ($article->status == 1) {
            $article->status = 0;
            $response = "Status changed to Not read";
        } else {
            $article->status = 1;
            $response = "Status changed to read";
        }
        $article->save();
        $collection =  collect();
        $collection->push(['message' => $response]);
        $collection->all();
        return ArticlesResource::collection($collection);
    }

    public function cleanUp()
    {
        $article = Article::get();
        foreach ($article as $item) {
            $art = Article::findOrFail($item->id);
            $art->description = $this->strip_tags_content($item->description);
            $art->save();
        }

        $response["message"] = "Success. Finshed cleaning data";
        return response()->json($response);
    }

    function strip_tags_content($string)
    {
        // ----- remove HTML TAGs ----- 
        $string = preg_replace('/<[^>]*>/', ' ', $string);
        // ----- remove control characters ----- 
        $string = str_replace("\r", '', $string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace("\t", ' ', $string);
        // ----- remove multiple spaces ----- 
        $string = trim(preg_replace('/ {2,}/', ' ', $string));
        return $string;
    }

    //get articles for NerdMypaper
    public function getNerdMyPaper()
    {
        $url = Url::where('name', 'Nerdmypaper')->first();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        //return json_decode($response);
        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }
    // public function getNerdMyPaper()
    // {

    //     $url = Url::where('name', 'Nerdmypaper')->first();
    //     $date_today = Carbon::now()->format('Y/m/d');
    //     $url2 = $url->url . $date_today;

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url2);
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     $dom = HtmlDomParser::str_get_html($response);

    //     foreach ($dom->find('section.pt-5 > div.container > div.row > div.col-lg-8 > article > div.entry-meta > span') as $test) {
    //         $link = $test->find('a')[0]->href;
    //         $date_created = $test->find('a time')[0]->innertext;

    //         $now = Carbon::now();
    //         $date = Carbon::parse($now)->toDateString();
    //         $createdAt = Carbon::parse($date_created);
    //         $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $createdAt)->format('Y-m-d');

    //         if ($date === $createdAt) {
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_URL, $link);
    //             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //             $response = curl_exec($ch);
    //             curl_close($ch);
    //             $dom = HtmlDomParser::str_get_html($response);

    //             foreach ($dom->find('section.pt-5 > div.container > div.row > div.col-lg-8') as $test) {
    //                 $desc = collect();
    //                 $p = "";
    //                 foreach ($test->find('div > p') as $u) {
    //                     if (!is_null(strip_tags($u->innertext))) {
    //                         $desc->push(['description' => strip_tags($u->innertext)]);
    //                         $p = '<p>' . $p . $u->innertext . '</p>';
    //                     }
    //                 }
    //                 $desc->all();
    //                 $getArticles = Article::where('title', $test->find('h1')[0]->innertext)->exists();
    //                 if (!$getArticles) {
    //                     $article = new Article();
    //                     $article->title = $test->find('h1')[0]->innertext;
    //                     $article->description = $p;
    //                     $article->url_id = $url->id;
    //                     $article->save();
    //                 }
    //             }
    //         }
    //     }
    // }


    //get articles for Skilled Papers
    public function getSkilledPapers()
    {
        $url = Url::where('name', 'Skilledpapers')->first();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }

    // public function getSkilledPapers()
    // {

    //     $url = Url::where('name', 'Skilledpapers')->first();
    //     $date_today = Carbon::now()->format('Y/m/d');
    //     $url2 = $url->url . $date_today . '/';

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url2);
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     $dom = HtmlDomParser::str_get_html($response);

    //     foreach ($dom->find('section.content-area > div.site-main article.post') as $test) {
    //         $link = $test->find('a')[0]->href;
    //         $date_created = $test->find('a time')[0]->innertext;

    //         $now = Carbon::now();
    //         $date = Carbon::parse($now)->toDateString();
    //         $createdAt = Carbon::parse($date_created);
    //         $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $createdAt)->format('Y-m-d');

    //         if ($date === $createdAt) {
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_URL, $link);
    //             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //             $response = curl_exec($ch);
    //             curl_close($ch);
    //             $dom = HtmlDomParser::str_get_html($response);

    //             foreach ($dom->find('section.content-area > div.site-main article.post') as $test2) {
    //                 $desc = collect();
    //                 $p = "";
    //                 foreach ($test2->find('div.entry-content > p') as $u) {
    //                     if (!is_null(strip_tags($u->innertext))) {
    //                         $desc->push(['description' => strip_tags($u->innertext)]);
    //                         $p = '<p>' . $p . $u->innertext . '</p>';
    //                     }
    //                 }
    //                 $desc->all();
    //                 $getArticles = Article::where('title', $test2->find('h1')[0]->innertext)->exists();
    //                 if (!$getArticles) {
    //                     $article = new Article();
    //                     $article->title = $test2->find('h1')[0]->innertext;
    //                     $article->description = $p;
    //                     $article->url_id = $url->id;
    //                     $article->save();
    //                 }

    //                 //echo ('<p>'.$test2->find('h1')[0]->innertext.'</p>' .'<p>'. $p. '</p>');
    //             }
    //         }
    //     }
    // }


    //get articles for Write Tasks Papers
    public function getWriteTasks()
    {
        $url = Url::where('name', 'Writertask')->first();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        //return json_decode($response);
        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }
    // public function getWriteTasks()
    // {

    //     $url = Url::where('name', 'Writertask')->first();
    //     $date_today = Carbon::now()->format('Y/m/d');
    //     $url2 = $url->url . $date_today . '/';

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url2);
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     $dom = HtmlDomParser::str_get_html($response);

    //     foreach ($dom->find('body.archive > div#page > div#main > div.wf-wrap >div.wf-container-main > div#content > div.articles-list article') as $test) {
    //         $link = $test->find('a')[0]->href;

    //         $title =  $test->find('a')[0]->title;;


    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $link);
    //         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         $response = curl_exec($ch);
    //         curl_close($ch);
    //         $dom2 = HtmlDomParser::str_get_html($response);
    //         foreach ($dom2->find('body#the7-body > div#page > div#main > div.wf-wrap >div.wf-container-main > div#content > article') as $test2) {
    //             $desc = collect();
    //             $p = "";
    //             foreach ($test2->find('p') as $u) {
    //                 if (!is_null(strip_tags($u->innertext))) {
    //                     $desc->push(['description' => strip_tags($u->innertext)]);
    //                     $p = '<p>' . $p . $u->innertext . '</p>';
    //                 }
    //             }
    //             $desc->all();
    //             $getArticles = Article::where('title', $title)->exists();
    //             if (!$getArticles) {
    //                 $article = new Article();
    //                 $article->title = $title;
    //                 $article->description = $p;
    //                 $article->url_id = $url->id;
    //                 $article->save();
    //             }
    //         }
    //     }
    // }


    //get articles for The Custom Essays
    // public function getCustomEssays()
    // {

    //     $url = Url::where('name', 'Thecustomessays')->first();
    //     $url2 = $url->url;
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url2);
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     $dom = HtmlDomParser::str_get_html($response);
    //     foreach ($dom->find('body.archive > div#Wrapper > div#Content > div.content_wrapper  > div.sections_group > div.section > div.section_wrapper > div.column > div.blog_wrapper > div.posts_group > div.post-item') as $test) {

    //         $link = $test->find('a')[0]->href;

    //         $title =  $test->find('h2')[0]->plaintext;


    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $link);
    //         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         $response = curl_exec($ch);
    //         curl_close($ch);
    //         $dom2 = HtmlDomParser::str_get_html($response);
    //         foreach ($dom2->find('body.single-post > div#Wrapper > div#Content > div.content_wrapper  > div.sections_group > div.post > div.post-wrapper-content > div.section  > div.section_wrapper > div.the_content_wrapper') as $test2) {
    //             $desc = $test2->find('div')[0];

    //             $getArticles = Article::where('title', $title)->exists();
    //             if (!$getArticles) {
    //                 $article = new Article();
    //                 $article->title = $title;
    //                 $article->description = $desc;
    //                 $article->url_id = $url->id;
    //                 $article->save();
    //             }

    //             //echo ('<p><b>'.$title.'</b></p>' . $desc . '</br></br>');
    //         }
    //     }
    // }


    public function getCustomEssays()
    {
        $url = Url::where('name', 'Thecustomessays')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }

    //get articles for Elite Custom Writings
    public function getEliteCustomWritings()
    {
        $url = Url::where('name', 'Elitecustomwritings')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        //return response()->json(json_decode($response));
        foreach (json_decode($response) as $item) {
            $string = str_replace('| StudyDaddy.com', '', $item->title->rendered);

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }

    //get articles for Perfect Research Papers
    public function getPerfectresearchpapers()
    {
        $url = Url::where('name', 'Perfectresearchpapers')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }

    //Get Emergency Essay
    public function getEmergencyEssayPapers()
    {
        $url = Url::where('name', 'EmergencyEssay')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }


    //Get Homeworkcraft Essay
    public function getHomeworkcraftPapers()
    {
        $url = Url::where('name', 'Homeworkcraft')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }



    //Get Homework Essay Market
    public function getHomeworkEssayMarketPapers()
    {
        $url = Url::where('name', 'Homework Essay Market')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }


    //Get Essay Writer
    public function getEssayWriterPapers()
    {
        $url = Url::where('name', 'Essay Writer')->first();
        $url2 = $url->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url->url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        foreach (json_decode($response) as $item) {
            $string = $item->title->rendered;

            $getArticles = Article::where('title', $string)->exists();
            if (!$getArticles) {
                $article = new Article();
                $article->title = $string;
                $article->description = $item->content->rendered;
                $article->url_id = $url->id;
                $article->save();
            }
            //echo ('<p><b>' . $string . '</b></p>' . $item->content->rendered . '</br></br>');
        }
    }
}
