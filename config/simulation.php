<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulation Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, mcmaco runs as a "shop simulator": bots visit listings,
    | chat with sellers, place fake orders — all marked with is_simulated=true
    | so they never pollute real production data.
    |
    | This is the dual-mode product described in Roadmap §02: one codebase,
    | three audiences (real store / demo / distributable CMS). The toggle
    | is intentionally config-driven (not database) so it can be cached
    | and checked without a DB hit in middleware.
    |
    */

    'enabled' => env('MCMACO_SIMULATION', false),

    /*
    | Active operating mode (AppMode enum):
    |   - real       : production store, no simulation
    |   - simulation : demo mode, only bot activity
    |   - dual       : real + simulation running side by side
    | Controlled via MCMACO_MODE so it can be set per-environment without
    | touching the codebase. Fresh installs default to 'real' for safety.
    */
    'mode' => env('MCMACO_MODE', 'real'),

    /*
    | Intensity presets — controls how much activity the simulation
    | generates per cron run. Used by the mcmaco:simulate:run command.
    | Values are "events per run" (one run = 5 minutes by default).
    */
    'intensity' => [
        'low' => [
            'visits_per_run'   => 5,
            'chats_per_run'    => 1,
            'orders_per_run'   => 1,
        ],
        'medium' => [
            'visits_per_run'   => 15,
            'chats_per_run'    => 3,
            'orders_per_run'   => 2,
        ],
        'high' => [
            'visits_per_run'   => 40,
            'chats_per_run'    => 8,
            'orders_per_run'   => 5,
        ],
    ],

    'default_intensity' => env('MCMACO_SIMULATION_INTENSITY', 'medium'),

    /*
    | Number of fake users SimulatedUserSeeder creates on a fresh install.
    | Higher = more realistic, but slower to seed.
    */
    'bot_count' => env('MCMACO_SIMULATION_BOTS', 50),

    /*
    | Avatar provider. DiceBear is free, no API key required.
    | Styles: https://www.dicebear.com/styles/
    */
    'avatar_style' => env('MCMACO_BOT_AVATAR_STYLE', 'avataaars'),

    /*
    | Pause between cron runs — short enough to feel alive, long enough
    | to not hammer the DB. 5 min is the minimum Laravel scheduler allows.
    */
    'tick_minutes' => env('MCMACO_SIMULATION_TICK', 5),

];
