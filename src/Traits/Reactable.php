<?php

namespace Hkp22\Laravel\Reactions\Traits;

use Illuminate\Database\Eloquent\Builder;
use Hkp22\Laravel\Reactions\Models\Reaction;
use Hkp22\Laravel\Reactions\Contracts\ReactsInterface;

trait Reactable
{
    /**
     * Collection of reactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * Add reaction.
     *
     * @param  mixed         $reactionType
     * @param  mixed         $user
     * @return Reaction|bool
     */
    public function react($reactionType, $user = null)
    {
        $user = $this->getUser($user);

        if ($user) {
            return $user->reactTo($this, $reactionType);
        }

        return false;
    }

    /**
    * Remove reaction.
    *
    * @param  mixed $user
    * @return bool
    */
    public function removeReaction($user = null)
    {
        $user = $this->getUser($user);

        if ($user) {
            return $user->removeReactionFrom($this);
        }

        return false;
    }

    /**
     * Toggle Reaction
     *
     * @param  mixed $reactionType
     * @param  mixed $user
     * @return void
     */
    public function toggleReaction($reactionType, $user = null)
    {
        $user = $this->getUser($user);

        if ($user) {
            $user->toggleReactionOn($this, $reactionType);
        }
    }

    /**
     * Check is reacted by user.
     *
     * @param  mixed $user
     * @return bool
     */
    public function isReactBy($user = null, $type = null)
    {
        $user = $this->getUser($user);

        if ($user) {
            return $user->isReactedOn($this, $type);
        }

        return false;
    }

    /**
     * Check is reacted by user.
     *
     * @param  mixed $user
     * @return bool
     */
    public function getIsReactedAttribute()
    {
        return $this->isReactBy();
    }

    /**
     * Get user model.
     *
     * @param  mixed $user
     * @return void
     */
    protected function getUser($user = null)
    {
        if (!$user) {
            return auth()->user();
        }

        if ($user instanceof ReactsInterface) {
            return $user;
        }

        return null;
    }

    /**
     * Fetch records that are reacted by a given user.
     *
     * @todo think about method name
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $type
     * @param  null|int|ReactsInterface              $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereReactedBy(Builder $query, $userId = null, $type = null)
    {
        $user = $this->getUser($userId);
        $userId = ($user) ? $user->getKey() : $userId;

        return $query->whereHas('reactions', function ($innerQuery) use ($userId, $type) {
            $innerQuery->where('user_id', $userId);

            if($type) {
                $innerQuery->where('type', $type);
            }
        });
    }
}
