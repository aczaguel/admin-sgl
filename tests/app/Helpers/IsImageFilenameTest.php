<?php

namespace Tests\App\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Data-provider unit tests for is_image_filename() (task 1.3).
 *
 * is_image_filename(string $name): bool reports whether a filename names a
 * browser-renderable image. The extension is the substring after the FINAL
 * '.' in the trimmed filename; it must be one of png/jpg/jpeg/gif/webp/bmp/svg
 * compared case-insensitively. Empty/whitespace-only names, names without a
 * '.', and leading-dot-only names (".png") are not images.
 *
 * _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_
 *
 * @internal
 */
final class IsImageFilenameTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('filestorage');
    }

    /**
     * Extension matrix + edge cases.
     *
     * @return array<string, array{0:string,1:bool}> [filename, expected]
     */
    public static function imageFilenameProvider(): array
    {
        return [
            // --- Req 7.1: the seven renderable image extensions -> true ---
            'png'  => ['foto.png', true],
            'jpg'  => ['foto.jpg', true],
            'jpeg' => ['foto.jpeg', true],
            'gif'  => ['foto.gif', true],
            'webp' => ['foto.webp', true],
            'bmp'  => ['foto.bmp', true],
            'svg'  => ['foto.svg', true],

            // --- Req 7.2: non-image extensions -> false -------------------
            'pdf'  => ['documento.pdf', false],
            'xml'  => ['data.xml', false],
            'doc'  => ['carta.doc', false],
            'txt'  => ['notas.txt', false],
            'zip'  => ['paquete.zip', false],
            'jpgx' => ['foto.jpgx', false],

            // --- Req 7.3: no '.' character -> false -----------------------
            'no extension'          => ['comprobante', false],
            'no extension long'     => ['archivo_sin_punto_12472', false],

            // --- Req 7.4: empty / whitespace-only -> false ----------------
            'empty string'    => ['', false],
            'spaces only'     => ['   ', false],
            'tabs & newlines' => ["\t\n\r ", false],

            // --- Req 7.5: only '.' is the first character -> false --------
            'leading dot only png'  => ['.png', false],
            'leading dot only jpg'  => ['.jpg', false],
            'hidden file no ext'    => ['.hidden', false],

            // --- Req 7.6: case-insensitive comparison ---------------------
            'uppercase JPG'   => ['foto.JPG', true],
            'mixed case PnG'  => ['foto.PnG', true],
            'uppercase JPEG'  => ['foto.JPEG', true],
            'mixed case WebP' => ['foto.WebP', true],
            'uppercase SVG'   => ['icono.SVG', true],

            // --- extra: final extension wins over earlier segments --------
            'multi-dot image'     => ['archivo.final.png', true],
            'multi-dot non-image' => ['imagen.png.pdf', false],

            // --- extra: surrounding whitespace is trimmed -----------------
            'trailing spaces image' => ['foto.png   ', true],
            'leading spaces image'  => ['   foto.png', true],
        ];
    }

    /**
     * @dataProvider imageFilenameProvider
     */
    public function testIsImageFilename(string $filename, bool $expected): void
    {
        $this->assertSame($expected, is_image_filename($filename));
    }
}
