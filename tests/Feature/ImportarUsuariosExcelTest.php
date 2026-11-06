<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Services\XlsxReader;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ImportarUsuariosExcelTest extends TestCase
{
    use DatabaseTransactions;

    private const ENCABEZADOS_NUEVOS = [
        'nombre', 'area', 'planta', 'telefono', 'extension',
        'correo_para_mostrar', 'correo_para_entrar', 'password',
    ];

    protected function tearDown(): void
    {
        // Red de seguridad: la transicion ya revierte, pero la base es real.
        User::where('email', 'like', '%@importar.test')->orWhere('email', 'like', '%@ejemplo.com')->delete();
        User::where('correo', 'like', '%@importar.test')->orWhere('correo', 'like', '%@ejemplo.com')->delete();
        parent::tearDown();
    }

    public function test_formato_nuevo_separa_el_correo_de_mostrar_del_de_entrada(): void
    {
        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Ana Lopez', 'Sistemas', 'Planta 1', '5550001', '101', 'ana.mostrar@ejemplo.com', 'ana.login@importar.test', 'Ana2026'],
        ]);

        $resp->assertSessionHas('agregado');
        $resp->assertSessionHasNoErrors();

        $ana = User::where('email', 'ana.login@importar.test')->first();
        $this->assertNotNull($ana, 'el correo de entrada debe ser el email de login');
        $this->assertSame('ana.mostrar@ejemplo.com', $ana->correo, 'el correo visible va aparte');
        $this->assertSame('Sistemas', $ana->puesto);
        $this->assertSame('5550001', $ana->celular, 'telefono debe mapearse a celular');
        $this->assertSame('101', $ana->extension);
        $this->assertSame(0, (int) $ana->jefe);
        $this->assertSame(0, (int) $ana->resguardo_firmado);
        $this->assertTrue(Hash::check('Ana2026', $ana->password));
    }

    public function test_el_formato_anterior_sigue_funcionando(): void
    {
        $resp = $this->importar([
            ['name', 'puesto', 'planta', 'celular', 'extension', 'email', 'password'],
            ['Luis Ruiz', 'Compras', 'Planta 2', '5550002', '102', 'luis@importar.test', 'Luis2026'],
        ]);

        $resp->assertSessionHas('agregado');
        $resp->assertSessionHasNoErrors();

        $luis = User::where('email', 'luis@importar.test')->first();
        $this->assertNotNull($luis);
        $this->assertSame('luis@importar.test', $luis->correo, 'con una sola columna de correo ambos quedan iguales');
        $this->assertSame('Compras', $luis->puesto);
        $this->assertSame('5550002', $luis->celular);
    }

    public function test_el_correo_que_falta_hereda_del_otro(): void
    {
        $this->importar([
            ['nombre', 'area', 'planta', 'telefono', 'extension', 'correo_para_mostrar', 'password'],
            ['Sin Entrar', 'Sistemas', 'Planta 1', '5550001', '101', 'solo.mostrar@importar.test', 'Aa2026'],
        ])->assertSessionHasNoErrors();

        $this->importar([
            ['nombre', 'area', 'planta', 'telefono', 'extension', 'correo_para_entrar', 'password'],
            ['Sin Mostrar', 'Sistemas', 'Planta 1', '5550001', '101', 'solo.entrar@importar.test', 'Bb2026'],
        ])->assertSessionHasNoErrors();

        $a = User::where('email', 'solo.mostrar@importar.test')->first();
        $this->assertSame('solo.mostrar@importar.test', $a->correo, 'sin correo de entrada, el visible se usa para entrar');

        $b = User::where('email', 'solo.entrar@importar.test')->first();
        $this->assertSame('solo.entrar@importar.test', $b->correo, 'sin correo visible, el de entrada se muestra');
    }

    public function test_un_duplicado_en_la_base_cancela_toda_la_importacion(): void
    {
        User::forceCreate([
            'name' => 'Existente', 'email' => 'ya.existe@importar.test', 'correo' => 'otro@ejemplo.com',
            'password' => bcrypt('Aa2026'), 'puesto' => 'Sistemas', 'planta' => 'P1', 'celular' => '',
            'extension' => '', 'jefe' => 0, 'resguardo_firmado' => 0,
        ]);

        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Nueva Uno', 'Sistemas', 'Planta 1', '5550001', '101', 'n1@ejemplo.com', 'nuevo1@importar.test', 'Cc2026'],
            ['Repetido', 'Sistemas', 'Planta 1', '5550002', '102', 'n2@ejemplo.com', 'ya.existe@importar.test', 'Dd2026'],
        ]);

        $resp->assertSessionHasErrors('archivo');
        $resp->assertSessionMissing('agregado');
        $this->assertStringContainsString('ya.existe@importar.test', session('errors')->first('archivo'));

        $this->assertSame(0, User::where('email', 'nuevo1@importar.test')->count(), 'no debe crearse ninguna fila');
        $this->assertSame(1, User::where('email', 'ya.existe@importar.test')->count(), 'no debe duplicarse el existente');
    }

    public function test_un_duplicado_dentro_del_archivo_cancela_toda_la_importacion(): void
    {
        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Uno', 'Sistemas', 'Planta 1', '5550001', '101', 'u1@ejemplo.com', 'mismo@importar.test', 'Ee2026'],
            ['Otro', 'Sistemas', 'Planta 1', '5550002', '102', 'u2@ejemplo.com', 'otro@importar.test', 'Ff2026'],
            ['Gemela', 'Sistemas', 'Planta 1', '5550003', '103', 'u3@ejemplo.com', 'mismo@importar.test', 'Gg2026'],
        ]);

        $mensaje = session('errors')->first('archivo');
        $this->assertStringContainsString('Fila 4', $mensaje, 'debe señalar la fila repetida');
        $this->assertStringContainsString('fila 2', $mensaje, 'debe señalar dónde estaba antes');
        $this->assertSame(0, User::where('email', 'otro@importar.test')->count(), 'una fila repetida invalida todo');
    }

    public function test_el_duplicado_ignora_mayusculas_y_espacios(): void
    {
        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Uno', 'Sistemas', 'Planta 1', '5550001', '101', 'u1@ejemplo.com', '  Persona@Importar.Test ', 'Hh2026'],
            ['Dos', 'Sistemas', 'Planta 1', '5550002', '102', 'u2@ejemplo.com', 'persona@importar.test', 'Ii2026'],
        ]);

        $resp->assertSessionHasErrors('archivo');
        $this->assertSame(0, User::where('email', 'like', '%persona@importar.test')->count());
    }

    public function test_una_fila_incompleta_cancela_toda_la_importacion(): void
    {
        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Completa', 'Sistemas', 'Planta 1', '5550001', '101', 'c@ejemplo.com', 'completa@importar.test', 'Jj2026'],
            ['Sin Contrasena', 'Sistemas', 'Planta 1', '5550002', '102', 's@ejemplo.com', 'sinclave@importar.test', ''],
        ]);

        $resp->assertSessionHasErrors('archivo');
        $this->assertStringContainsString('Fila 3', session('errors')->first('archivo'));
        $this->assertStringContainsString('contraseña', session('errors')->first('archivo'));
        $this->assertSame(0, User::where('email', 'completa@importar.test')->count());
    }

    public function test_un_correo_invalido_cancela_toda_la_importacion(): void
    {
        $resp = $this->importar([
            self::ENCABEZADOS_NUEVOS,
            ['Sin Arroba', 'Sistemas', 'Planta 1', '5550001', '101', 'x@ejemplo.com', 'esto-no-es-correo', 'Kk2026'],
        ]);

        $resp->assertSessionHasErrors('archivo');
        $this->assertStringContainsString('no es un correo válido', session('errors')->first('archivo'));
        $this->assertSame(0, User::where('name', 'Sin Arroba')->count());
    }

    public function test_si_faltan_columnas_requeridas_el_error_menciona_la_plantilla(): void
    {
        $resp = $this->importar([
            ['nombre', 'area'],
            ['Ana', 'Sistemas'],
        ]);

        $resp->assertSessionHasErrors('archivo');
        $mensaje = session('errors')->first('archivo');
        $this->assertStringContainsString('password', $mensaje);
        $this->assertStringContainsString('correo de entrada o para mostrar', $mensaje);
        $this->assertStringContainsString('correo_para_entrar', $mensaje);
    }

    public function test_la_plantilla_se_descarga_como_xlsx_valido_y_se_puede_leer(): void
    {
        $resp = $this->actingAs($this->admin(), 'admin')->get(route('usuarios.plantilla'));

        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('plantilla_usuarios.xlsx', $resp->headers->get('Content-Disposition'));

        $ruta = $resp->baseResponse->getFile()->getPathname();
        $filas = XlsxReader::leer($ruta);

        $this->assertSame(self::ENCABEZADOS_NUEVOS, array_map('trim', array_values($filas[1])));
        $this->assertCount(3, $filas, 'encabezado mas dos filas de ejemplo');
    }

    public function test_solo_un_admin_puede_importar(): void
    {
        $this->get(route('usuarios.plantilla'))->assertRedirect();
    }

    private function importar(array $filas)
    {
        $bytes = self::xlsx($filas);

        return $this->actingAs($this->admin(), 'admin')
            ->post(route('usuarios.excel'), [
                'archivo' => UploadedFile::fake()->createWithContent('usuarios.xlsx', $bytes),
            ]);
    }

    private function admin(): Admin
    {
        return Admin::first() ?: Admin::forceCreate([
            'nombre'   => 'Admin Importar',
            'email'    => 'admin.importar@test.com',
            'password' => bcrypt('secret'),
        ]);
    }

    private static function xlsx(array $filas): string
    {
        $zip = new \ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx') ?: '/tmp/xlsx_tmp';
        if ($zip->open($tmp, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('No se pudo crear el xlsx de prueba');
        }

        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rel = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $sheetData = '<sheetData>';
        foreach ($filas as $r => $fila) {
            $celdas = '';
            foreach (array_values($fila) as $c => $valor) {
                $celdas .= sprintf(
                    '<c r="%s%d" t="inlineStr"><is><t>%s</t></is></c>',
                    chr(65 + $c),
                    $r + 1,
                    htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES)
                );
            }
            $sheetData .= '<row r="' . ($r + 1) . '">' . $celdas . '</row>';
        }
        $sheetData .= '</sheetData>';

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="' . $ns . '" xmlns:r="' . $rel . '">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/worksheets/sheet1.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="' . $ns . '">' . $sheetData . '</worksheet>');
        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . $rel . '/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . $rel . '/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>');
        $zip->close();

        return (string) file_get_contents($tmp);
    }
}
