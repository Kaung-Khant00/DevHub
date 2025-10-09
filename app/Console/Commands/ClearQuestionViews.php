<?php

namespace App\Console\Commands;

use App\Models\QuestionView;
use Illuminate\Console\Command;

class ClearQuestionViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:question-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commend to delete question views hourly that are older than one day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        QuestionView::where('created_at', '<', now()->subDay())->delete();
        $this->info('Question views older than one day are cleared.');
        return 0;

    }
}
