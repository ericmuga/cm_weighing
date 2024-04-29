<?php

namespace App\Listeners;

use App\Events\ReceiptsUploadCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ToInsertForGradingActionListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ReceiptsUploadCompleted  $event
     * @return void
     */
    public function handle(ReceiptsUploadCompleted $event)
    {
        // Call the second action method from the controller
        app()->call('App\Http\Controllers\SlaughterController@insertForQAGrading', ['database_date' => $event->database_date]);
    }
}
