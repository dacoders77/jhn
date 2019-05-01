<?php

namespace App\Listeners;

use App\Classes\LogToFile;
use App\Events\ClearCache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WarmUpCache
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
     * @param  ClearCache  $event
     * @return void
     */
    public function handle(ClearCache $event)
    {
        //LogToFile::add(__FILE__, 'JOJO');
        /*if (isset($event->cache_keys) && count($event->cache_keys)) {
            foreach ($event->cache_keys as $cache_key) {
                // generate cache for this key
                // warm_up_cache($cache_key)
            }
        }*/
    }
}
