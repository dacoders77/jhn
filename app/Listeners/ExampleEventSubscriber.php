<?php
// app/Listeners/ExampleEventSubscriber.php
namespace App\Listeners;

class ExampleEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function sendEmailNotification($event) {
        \App\Classes\LogToFile::add(__FILE__, 'send email');
    }
    /**
     * Handle user logout events.
     */
    public function warmUpCache($event) {
        \App\Classes\LogToFile::add(__FILE__, 'warm up cache');
    }
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'App\Listeners\ExampleEventSubscriber@sendEmailNotification'
        );

        $events->listen(
            'App\Events\ClearCache',
            'App\Listeners\ExampleEventSubscriber@warmUpCache'
        );
    }
}