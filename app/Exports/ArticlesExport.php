<?php

namespace App\Exports;

use App\Models\Article;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class ArticlesExport implements FromCollection, WithHeadings, WithEvents
{
    protected $id;

    function __construct($id, $date)
    {
        $this->id = $id;
        $this->date = $date;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $newDate = Carbon::createFromFormat('d-m-Y', $this->date)->format('Y-m-d');
        if ($this->id > 0) {
            $article = Article::leftJoin('urls', 'articles.url_id', '=', 'urls.id')
                ->select('articles.id', 'articles.title', 'articles.description', 'articles.created_at', 'urls.name as company')
                ->where('status', '!=', 1)
                ->where('edited', 1)
                ->where('articles.url_id', $this->id)
                ->whereDate('articles.created_at', $newDate)
                ->orWhereNull('status')
                ->get();
        }

        if ($this->id == 0) {
            $article = Article::leftJoin('urls', 'articles.url_id', '=', 'urls.id')
                ->select('articles.id', 'articles.title', 'articles.description', 'articles.created_at', 'urls.name as company')
                ->where('status', '!=', 1)
                ->where('edited', 1)
                ->whereDate('articles.created_at', $newDate)
                ->orWhereNull('status')
                ->get();
        }

        $collection =  collect();

        foreach ($article as $item) {
            $collection->push(['ID' => $item->id, 'Date' => $item->created_at, 'Title' => $item->title, 'Description' => strip_tags($item->description), 'Company' => $item->company]);
            $stolen_article = Article::findOrFail($item->id);
            $stolen_article->status = 1;
            $stolen_article->save();
        }
        $collection->all();


        return $collection;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Title',
            'Description',
            'Company',
        ];
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getStyle('A1:E1')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('#FFA500');
            },

        ];
    }
}
