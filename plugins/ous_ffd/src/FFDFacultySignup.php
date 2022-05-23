<?php

namespace DigraphCMS_Plugins\unmous\ous_ffd;

use DateTime;
use DigraphCMS\Content\Page;
use DigraphCMS\DB\DB;
use DigraphCMS\Digraph;
use DigraphCMS\Session\Session;
use DigraphCMS\UI\Format;
use DigraphCMS\UI\Notifications;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use DigraphCMS\Users\User;

class FFDFacultySignup extends Page
{
    const DEFAULT_SLUG = "faculty_signup";

    public function cancelRSVP(string $uuid)
    {
        DB::query()->update(
            'ffd_faculty_rsvp',
            [
                'cancelled' => time(),
                'cancelled_by' => Session::uuid()
            ]
        )
            ->where('page_uuid = ?', [$this->uuid()])
            ->where('uuid = ?', [$uuid])
            ->execute();
    }

    public function uncancelRSVP(string $uuid)
    {
        DB::query()->update(
            'ffd_faculty_rsvp',
            [
                'cancelled' => null,
                'cancelled_by' => null
            ]
        )
            ->where('page_uuid = ?', [$this->uuid()])
            ->where('uuid = ?', [$uuid])
            ->execute();
    }

    public function rsvp(string $uuid)
    {
        $result = DB::query()->from('ffd_faculty_rsvp')
            ->where('page_uuid = ?', [$this->uuid()])
            ->where('uuid = ?', [$uuid])
            ->fetch();
        if ($result && $result['accommodations']) $result['accommodations'] = json_decode($result['accommodations'], true);
        return $result;
    }

    public function newRSVP(string $for, string $firstName, string $lastName, bool $regaliaRequested, ?array $accommodations): string
    {
        $uuid = Digraph::uuid('rsvp');
        DB::query()->insertInto(
            'ffd_faculty_rsvp',
            [
                'uuid' => $uuid,
                'page_uuid' => $this->uuid(),
                '`for`' => $for,
                'first_name' => strip_tags($firstName),
                'last_name' => strip_tags($lastName),
                'accommodations' => $accommodations ? json_encode($accommodations) : null,
                'regalia_requested' => $regaliaRequested,
                'created' => time(),
                'created_by' => Session::uuid(),
                'updated' => time(),
                'updated_by' => Session::uuid()
            ]
        )->execute();
        return $uuid;
    }

    public function updateRSVP(string $uuid, string $firstName, string $lastName, bool $regaliaRequested, ?array $accommodations)
    {
        DB::query()->update(
            'ffd_faculty_rsvp',
            [
                'first_name' => strip_tags($firstName),
                'last_name' => strip_tags($lastName),
                'accommodations' => $accommodations ? json_encode($accommodations) : null,
                'regalia_requested' => $regaliaRequested,
                'updated' => time(),
                'updated_by' => Session::uuid()
            ]
        )
            ->where('page_uuid = ?', [$this->uuid()])
            ->where('uuid = ?', [$uuid])
            ->execute();
    }

    /**
     * Locate the RSVP for a given NetID
     *
     * @param string $netID
     * @return array|false
     */
    public function rsvpFor(string $netID)
    {
        return DB::query()->from('ffd_faculty_rsvp')
            ->where('page_uuid = ?', [$this->uuid()])
            ->where('`for` = ?', [$netID])
            ->limit(1)
            ->fetch();
    }

    public function permissions(URL $url, ?User $user = null): ?bool
    {
        if (in_array($url->action(), ['_sign_up', '_edit_rsvp'])) return !!Session::user();
        elseif ($url->actionPrefix() == 'rsvp') return !!Session::user();
        else return parent::permissions($url, $user);
    }

    public function embedCard(): string
    {
        ob_start();
        echo "<div class='card card--light'>";
        if ($this->open() || Permissions::inMetaGroup('signup__edit')) {
            printf(
                '<a href="%s" class="button button--safe" style="display:block;">%s</a>',
                $this->url(),
                $this->name()
            );
            printf(
                '<div><small>Signup deadline: %s</small></div>',
                Format::datetime($this->end())
            );
        } elseif ($this->ended()) {
            Notifications::printWarning($this->name() . " ended " . Format::datetime($this->end()));
        } elseif ($this->notStarted()) {
            Notifications::printWarning($this->name() . " opens " . Format::datetime($this->start()));
        } else {
            Notifications::printError($this->name() . " is temporarily closed. Please check back later.");
        }
        echo "</div>";
        return ob_get_clean();
    }

    public function notStarted(): bool
    {
        return new DateTime() < $this->start();
    }

    public function ended(): bool
    {
        return new DateTime() >= $this->end();
    }

    public function closed(): bool
    {
        return false;
    }

    public function open(): bool
    {
        return !($this->notStarted() || $this->ended() || $this->closed());
    }

    public function start(): DateTime
    {
        return $this->_datetime('start');
    }

    public function end(): DateTime
    {
        return $this->_datetime('end');
    }

    public function setStart(DateTime $datetime)
    {
        $this->_setDatetime('start', $datetime);
        return $this;
    }

    public function setEnd(DateTime $datetime)
    {
        $this->_setDatetime('end', $datetime);
        return $this;
    }

    public function routeClasses(): array
    {
        return ['ffd_faculty_signup', '_any'];
    }
}
