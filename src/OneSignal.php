<?php

namespace Yassi\OneSignal;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class OneSignal extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-one-signal', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-one-signal', __DIR__ . '/../dist/css/tool.css');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('nova-one-signal::navigation');
    }
}