<?php

namespace App\Exports;

use App\Models\Article;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ArticlesExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $article = Article::leftJoin('urls', 'articles.url_id', '=', 'urls.id')
            ->select('articles.id', 'articles.title', 'articles.description', 'articles.created_at', 'urls.name as category')
            ->where('status', '!=', 1)
            ->orWhereNull('status')
            ->get();

        $collection =  collect();

        foreach ($article as $item) {
            $collection->push(['id' => $item->id, 'date' => $item->created_at, 'title' => $item->title, 'description' => strip_tags($item->description), 'category' => $item->category]);
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
            'id',
            'date',
            'title',
            'description',
            'category',
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
