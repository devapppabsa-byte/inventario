<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * phpunit.xml deja comentadas las lineas de sqlite, asi que la suite corre
     * contra la base de datos real. RefreshDatabase ejecuta migrate:fresh y la
     * borra por completo, asi que aqui se bloquea cualquier test que lo use.
     *
     * La validacion va ANTES de parent::setUp() porque ahi es donde Laravel
     * dispara las migraciones del trait.
     */
    protected function setUp(): void
    {
        $this->refreshApplication();
        $this->detenerSiUsaRefreshDatabase();

        parent::setUp();
    }

    private function detenerSiUsaRefreshDatabase(): void
    {
        $usadas = class_uses_recursive(static::class);
        if (!in_array(\Illuminate\Foundation\Testing\RefreshDatabase::class, $usadas, true)) {
            return;
        }

        $base = config('database.default');
        $nombre = (string) config("database.connections.{$base}.database");
        $esSqlite = $base === 'sqlite' || str_contains($nombre, ':memory:');

        if ($esSqlite) {
            return;
        }

        throw new RuntimeException(
            "BLOQUEO DE SEGURIDAD: " . static::class . " usa RefreshDatabase y la conexion '{$base}' apunta a la base '{$nombre}', que es real.\n"
            . "RefreshDatabase ejecuta migrate:fresh y deja la base vacia.\n"
            . "Usa DatabaseTransactions (revierte al terminar) o configura una base de pruebas en phpunit.xml."
        );
    }
}
