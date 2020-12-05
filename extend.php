<?php

use Flarum\User\Event\Registered;
use Flarum\User\Event\LoggedIn;
use Flarum\User\User;
use Illuminate\Events\Dispatcher;

function setPreferences($event) {
    $preferenceFileContent = file_get_contents(__DIR__ . '/preferences.json');
    $userPreferences = json_decode($preferenceFileContent, true);

    foreach($userPreferences as $type => $preference) {
        foreach($preference as $eventName => $status) {
            if ($type == "custom") {
                /* Set preference with absolute name */
                $event->user->setPreference($eventName, $status);
            } else {
                /* Set preferences for emails and alerts */
                $event->user->setPreference(
                    User::getNotificationPreferenceKey($eventName, $type),
                    $status
                );
            }
        }
    }
}

return [
    function (Dispatcher $events) {
        /*
        * Set notification preferences for new registered users
        */
        $events->listen(Registered::class, function (Registered $event) {
            setPreferences($event);
            $event->user->save();
        });
        
        /*
        * Set notification preferences on next login
        */
        $events->listen(LoggedIn::class, function (LoggedIn $event) {
            setPreferences($event);
            $event->user->save();
        });
    }
];
