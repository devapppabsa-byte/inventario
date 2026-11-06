<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Reporte;
use App\Models\Seguimiento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reportes de solicitudes de soporte: filtro por estado, paginacion y export PDF.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea).
 */
class SoporteReporteExportTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;
    private $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::first();
        $this->usuario = User::create([
            'name' => 'Usuario Soporte ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'correo' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Prueba',
            'planta' => 'P1',
            'extension' => '100',
            'celular' => 'No hay celular',
            'jefe' => 0,
            'resguardo_firmado' => 0,
            'activo' => 1,
        ]);
    }

    public function test_la_exportacion_exige_sesion_de_admin()
    {
        $this->get(route('admin.soporte.exportar'))->assertRedirect();
        $this->actingAs($this->usuario)->get(route('admin.soporte.exportar'))->assertRedirect();
    }

    public function test_el_filtro_por_estado_separa_pendientes_de_completados()
    {
        $pendiente = $this->crearReporte('descripcion del pendiente', 'pendiente');
        $completado = $this->crearReporte('descripcion del completado', 'completado', 4, 'todo bien');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.soporte', $this->filtros(['estado' => 'pendiente'])));

        $response->assertStatus(200);
        $response->assertSee('descripcion del pendiente');
        $response->assertDontSee('descripcion del completado');
        $this->assertStringContainsString($pendiente->descripcion, $response->getContent());
        $this->assertStringNotContainsString($completado->descripcion, $response->getContent());
    }

    public function test_los_contadores_reflejan_los_filtros()
    {
        $this->crearReporte('pendiente uno', 'pendiente');
        $this->crearReporte('pendiente dos', 'pendiente');
        $this->crearReporte('completado uno', 'completado', 5);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.soporte', $this->filtros(['estado' => 'pendiente'])));
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringContainsString('Pendientes', $html);
        // Con el filtro de pendientes, los completados quedan fuera del conteo.
        preg_match_all('/display-4 fw-bold text-dark">(\d+)</', $html, $m);
        $this->assertNotEmpty($m[1], 'No se encontraron los contadores');
        $this->assertContains('2', $m[1], 'El conteo de pendientes deberia ser 2');
        $this->assertContains('0', $m[1], 'El conteo de completados deberia ser 0');
    }

    public function test_el_pdf_se_descarga_y_respeta_los_filtros()
    {
        $this->crearReporte('pendientexyz123', 'pendiente');
        $this->crearReporte('completadaxyz789', 'completado', 5, 'excelente');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.soporte.exportar', $this->filtros(['estado' => 'pendiente'])));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));

        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF', $pdf, 'El archivo debe ser un PDF real');

        // Solo debe viajar la solicitud pendiente, no la completada.
        $this->assertTrue($this->pdfContiene($pdf, 'pendientexyz123'), 'El PDF debe incluir la pendiente');
        $this->assertFalse($this->pdfContiene($pdf, 'completadaxyz789'), 'El PDF no debe incluir la completada');
    }

    public function test_el_pdf_incluye_calificacion_y_comentarios_de_seguimiento()
    {
        $reporte = $this->crearReporte('ticketxyz', 'completado', 4, 'observacionxyz');
        Seguimiento::create([
            'usuario' => 'TecnicoXYZ',
            'fecha' => Carbon::now()->format('Y-m-d H:i'),
            'reporte_id' => $reporte->id,
            'comentario' => 'comentarioxyz',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.soporte.exportar', $this->filtros()));
        $pdf = $response->getContent();

        $this->assertTrue($this->pdfContiene($pdf, 'ticketxyz'), 'Debe incluir la solicitud');
        $this->assertTrue($this->pdfContiene($pdf, '4 / 5'), 'Debe incluir la calificacion');
        $this->assertTrue($this->pdfContiene($pdf, 'observacionxyz'), 'Debe incluir las observaciones');
        $this->assertTrue($this->pdfContiene($pdf, 'comentarioxyz'), 'Debe incluir el comentario de seguimiento');
        $this->assertTrue($this->pdfContiene($pdf, 'TecnicoXYZ'), 'Debe incluir quien hizo el seguimiento');
    }

    public function test_el_pdf_avisa_si_hay_demasiadas_solicitudes()
    {
        $this->crearReporte('una', 'pendiente');
        $this->crearReporte('dos', 'pendiente');

        // El limite viene de config/soporte.php; con 1 simulamos que se paso.
        config(['soporte.export_limite' => 1]);

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.soporte', $this->filtros()))
            ->get(route('admin.soporte.exportar', $this->filtros()));

        $response->assertRedirect(route('admin.soporte', $this->filtros()));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('maximo es 1', session('error'));
    }

    public function test_el_detalle_muestra_calificacion_y_observaciones()
    {
        $reporte = $this->crearReporte('detalle con calificacion', 'completado', 5, 'quedo perfecto');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('detalle.reporte.admin', $reporte->id));

        $response->assertStatus(200);
        $response->assertSee('Calificación');
        $response->assertSee('quedo perfecto');
    }

    public function test_la_lista_pagina()
    {
        for ($i = 0; $i < 30; $i++) {
            $reporte = $this->crearReporte('solicitud ' . $i, 'pendiente');
            // created_at tiene granularidad de segundos: los 30 caerian en el
            // mismo instante y el orden dependeria del desempate de MySQL.
            $reporte->created_at = Carbon::now()->subMinutes(30 - $i);
            $reporte->save();
        }

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.soporte', $this->filtros()));
        $response->assertStatus(200);

        // 25 por pagina y orden descendente: la mas reciente entra y la mas
        // antigua se queda en la segunda pagina.
        $response->assertSee('solicitud 29');
        $response->assertDontSee('solicitud 0');
        $this->assertStringContainsString('Mostrando', $response->getContent());
    }

    /**
     * Los tests siempre filtran por su propio usuario: la base real ya tiene
     * solicitudes de otras pruebas manuales y hay que aislar los conteos.
     */
    private function filtros($extra = [])
    {
        return array_merge(['usuario' => $this->usuario->id], $extra);
    }

    private function crearReporte($descripcion, $status, $calificacion = null, $observaciones = null)
    {
        $reporte = Reporte::create([
            'dispositivo' => 'computadora',
            'descripcion' => $descripcion,
            'status' => $status,
            'calificacion' => $calificacion,
            'observaciones' => $observaciones,
            'fecha_reporte' => Carbon::now()->format('Y-m-d'),
            'prioridad' => 'Alta',
            'fecha_solucion' => $status == 'completado' ? Carbon::now()->format('Y-m-d') : 'pendiente',
            'detalles_solucion' => 'pendiente',
            'user_id' => $this->usuario->id,
        ]);

        $reporte->load('user');

        return $reporte;
    }

    /**
     * dompdf escribe el texto en UTF-16BE y comprime los flujos, asi que se
     * inflan y se busca la cadena en esa codificacion. Se usan tokens sin
     * espacios para que no dependan de donde dompdf parta la linea.
     *
     * Solo se prueban UTF-8 y UTF-16BE: buscar en UTF-16LE daria falsos
     * positivos porque el mismo patron aparece en el BE desplazado un byte.
     */
    private function pdfContiene($pdf, $texto)
    {
        $flujos = $this->flujosInflados($pdf);
        foreach ([$texto, iconv('UTF-8', 'UTF-16BE', $texto)] as $variante) {
            if (is_string($variante) && $variante !== '' && strpos($flujos, $variante) !== false) {
                return true;
            }
        }

        return false;
    }

    private function flujosInflados($pdf)
    {
        $salida = '';
        if (!preg_match_all('/stream(.*?)endstream/s', $pdf, $flujos)) {
            return '';
        }
        foreach ($flujos[1] as $flujo) {
            $datos = trim($flujo);
            $inflado = @gzuncompress($datos);
            $salida .= is_string($inflado) ? $inflado : $datos;
        }

        return $salida;
    }
}
