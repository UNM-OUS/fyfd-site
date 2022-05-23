<?php

namespace DigraphCMS_Plugins\unmous\ous_regalia;

use DigraphCMS\Config;
use DigraphCMS\Plugins\AbstractPlugin;
use DigraphCMS\UI\UserMenu;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use Envms\FluentPDO\Queries\Select;
use Envms\FluentPDO\Query;
use PDO;

class Regalia extends AbstractPlugin
{
    public static function getPersonInfo(string $for)
    {
        return static::people()
            ->where('identifier = ?', [$for])
            ->fetch();
    }

    public static function people(): Select
    {
        return static::query()
            ->from('person');
    }

    public static function institution(int $id)
    {
        return static::allInstitutions()
            ->where('institution.id = ?', [$id])
            ->fetch();
    }

    public static function preset(int $id)
    {
        return static::allPresets()
            ->where('preset.id = ?', [$id])
            ->fetch();
    }

    public static function field(int $id)
    {
        $field = static::fields()
            ->where('field.id = ?', [$id])
            ->fetch();
        if (!$field) return null;
        return $field;
    }

    protected static function pdo(): PDO
    {
        static $pdo;
        if (!$pdo) {
            $pdo = new PDO(
                Config::get('regalia.db.dsn') ?? static::localDSN(),
                Config::get('regalia.db.user'),
                Config::get('regalia.db.pass')
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }

    protected static function localDSN(): string
    {
        $file = realpath(__DIR__ . '/regalia.sqlite');
        return "sqlite:$file";
    }

    public static function query(): Query
    {
        return new Query(static::pdo());
    }

    public static function allFields(): Select
    {
        return static::query()
            ->from('field')
            ->leftJoin('jostens_field ON field.jostens_id = jostens_field.id')
            ->select('jostens_field.*')
            ->select('field.id as id');
    }

    public static function fields(): Select
    {
        return static::allFields()
            ->where('(field_deprecated <> 1 AND deprecated <> 1)');
    }

    public static function allInstitutions(): Select
    {
        return static::query()
            ->from('institution')
            ->leftJoin('jostens_institution ON institution.jostens_id = jostens_institution.id')
            ->select('institution.id as id')
            ->select('jostens_institution.institution_name as jostens_name')
            ->select('jostens_institution.institution_city as jostens_city')
            ->select('jostens_institution.institution_state as jostens_state')
            ->select('jostens_institution.institution_color_lining1 as color_lining')
            ->select('jostens_institution.institution_color_chevron1 as color_chevron');
    }

    public static function allPresets(): Select
    {
        return static::query()
            ->from('preset')
            ->leftJoin('field on preset.field = field.id')
            ->leftJoin('jostens_field on field.jostens_id = jostens_field.id')
            ->select('preset.id as id')
            ->select('preset.label as label')
            ->select('field.id as field_id')
            ->select('field.label as field_label')
            ->select('field.deprecated as field_deprecated')
            ->select('jostens_field.field_name as jostens_name')
            ->select('jostens_field.id as jostens_id')
            ->select('jostens_field.field_deprecated as jostens_deprecated')
            ->order('weight ASC, preset.label ASC');
    }

    public static function presets(): Select
    {
        return static::allPresets()
            ->where('preset.deprecated <> 1')
            ->where('(field.deprecated IS NULL OR field.deprecated <> 1)')
            ->where('(jostens_field.field_deprecated IS NULL OR jostens_field.field_deprecated <> 1)');
    }

    public static function institutions(): Select
    {
        return static::allInstitutions()
            ->where('(institution_deprecated <> 1 AND deprecated <> 1)');
    }

    public static function onUserMenu_user(UserMenu $menu)
    {
        $menu->addURL(new URL('/~regalia/'));
    }

    public static function onStaticUrlPermissions_regalia(URL $url)
    {
        if ($url->route() == 'regalia/global_settings') return Permissions::inMetaGroup('regalia__admin');
        else return Permissions::inMetaGroup('regalia__edit');
    }
}
