<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Mail\Mailer;
use Tinyissue\Model;

/**
 * SendMessages is console command to process all of the stacked messages.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class SendMessages extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tinyissue:messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send stacked messages.';

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $queue = new Model\Message\Queue();
        // Get all latest messages in the queue
        $records = $queue->latestMessages()->get();

        // pull out the first item in the list
        while ($latest = $records->first()) {
            // Get all other records for the same issue & remove them from collection
            $others = $records->where('model_id', $latest->model_id)->where('model_type', $latest->model_type);
            $records->forget($others->keys()->toArray());

            // Ask the class to process these messages
            app($latest->model_type . '\\SendMessages')->setMailer($mailer)->process($latest, $others);

            // Remove message queue
            $others->each(function(Model\Message\Queue $queue) {
                $queue->delete();
            });
        }
    }
}
