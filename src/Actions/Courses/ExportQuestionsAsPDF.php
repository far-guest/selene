<?php

namespace Selene\Actions\Courses;


use Selene\Actions\Action;
use Illuminate\Support\Collection;
use PDF;

class ExportQuestionsAsPDF extends Action
{
    protected $query;

    protected $title = "دریافت سوالات (PDF)";

    public function handel(array $fields, Collection $models)
    {
//        ini_set('max_execution_time', 0);

        $pdfs = [];

        foreach ($models as $model) {
            $questions = $model->questions();

            if ($this->query) {
                call_user_func($this->query, $questions);
            }

            $pdfs[] = [
                $model,
                PDF::loadView('courses.export-questions-pdf', [
                    'course' => $model,
                    'questions' => $questions->get(),
                ]),
            ];
        }

        switch (count($pdfs)) {
            case 0:
                return Action::error('فایلی ایجاد نشد', 'فایلی برادی دانلود وجود ندارد.');
            case 1:
                return $this->exportPDF($pdfs[0]);
            default:
                return $this->exportArchive($pdfs);
        }
    }

    private function exportPDF($item)
    {
        $filename = str_slug($item[0]->name) . '_' . time() . '.pdf';
        $download = $item[1]->output('S', $filename, true);

        return Action::download($download, $filename, 'application/pdf');
    }

    private function exportArchive($pdfs)
    {

    }

    /**
     * @param mixed $query
     * @return static
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param string $title
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }
}