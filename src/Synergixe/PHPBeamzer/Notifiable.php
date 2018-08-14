<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.7
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer;

use Illuminate\Notifications\Notifiable as BaseNotifiable;

trait Notifiable {

    use BaseNotifiable;
    
    /* Custom Notifiable trait for Models - e.g User */

    /**
     * Get the entity's notifications.
     *
     * via relationship
     */
    public function notifications(){
    
        return $this->morphMany(SynNotification::class, 'notifiable')
                            ->orderBy('created_at', 'desc');
    }
}
