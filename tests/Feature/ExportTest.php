<?php

namespace Tests\Feature;

use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->states();
        $estate = $this->estate();
        $house = $this->building($estate);
        $floor = $this->floor($estate);
        $room = $this->room($estate);

        $this->apartment($house, $floor, $room, ['number' => '1.01', 'order' => 1, 'tenant_id' => $this->tenant()->id, 'state_id' => State::RENTED]);
        $this->apartment($house, $floor, $room, ['number' => '1.02', 'order' => 2, 'size_balcony' => null]);
        $this->tenant(['firstname' => 'Ohne', 'name' => 'Wohnung']);

        $koro = $this->estate(['domain' => 'kornhaus-roetelstrasse', 'description' => 'Kornhaus-/Rötelstrasse']);
        $this->apartment($this->building($koro, ['street' => 'Kornhausstrasse 48']), $floor, $room, ['number' => 'H1_101', 'size_patio' => '6.4', 'size_loggia' => '6.3', 'size_balcony' => null]);

        $this->actingAs($this->user());
    }

    /**
     * The downloaded sheet as rows, and whether row 1 is bold
     */
    private function sheet(string $uri, string $name, string $estate = 'eglistrasse'): array
    {
        $response = $this->get($uri)->assertOk();
        $this->assertMatchesRegularExpression(
            '/^attachment; filename="liegenschaft-' . $estate . '-' . $name . '\d{2}-\d{2}-\d{4}-\d{2}:\d{2}:\d{2}\.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );
        // Not sent, so Excel's temp file stays: remove it here
        $file = $response->baseResponse->getFile()->getPathname();
        $sheet = IOFactory::load($file)->getActiveSheet();
        unlink($file);

        // Bold: the first and the last header cell
        $bold = $sheet->getStyle('A1')->getFont()->getBold() && $sheet->getStyle($sheet->getHighestColumn() . '1')->getFont()->getBold();

        return [$sheet->toArray(), $bold];
    }

    public function testApartments()
    {
        [$rows, $bold] = $this->sheet('/export/objekte', 'objekte');

        $this->assertTrue($bold);
        $this->assertSame(['Adresse', 'Lage', 'Nummer', 'Mietzins', 'Zimmer', 'M2', 'Terrasse', 'Sitzplatz', 'Balkon', 'Status', 'Mieter'], $rows[0]);
        // Only the current estate, by order descending
        $this->assertCount(3, $rows);
        $this->assertEquals(['Eglistrasse 1', '1. OG links', '1.02', 1700, '3.5', 85.5, '–', '–', '–', 'Frei', ' '], $rows[1]);
        $this->assertEquals(['Eglistrasse 1', '1. OG links', '1.01', 1700, '3.5', 85.5, '–', '–', 12, 'Vermietet', 'Erika Muster'], $rows[2]);
    }

    public function testApartmentsOfAnEstateWithLoggias()
    {
        config(['estates.current' => 'kornhaus-roetelstrasse']);

        [$rows, $bold] = $this->sheet('/export/objekte', 'objekte', 'kornhaus-roetelstrasse');

        $this->assertTrue($bold);
        $this->assertSame(['Adresse', 'Lage', 'Nummer', 'Mietzins', 'Zimmer', 'M2', 'Balkon', 'Loggia', 'Sitzplatz', 'Status', 'Mieter'], $rows[0]);
        $this->assertCount(2, $rows);
        $this->assertEquals(['Kornhausstrasse 48', '1. OG links', 'H1_101', 1700, '3.5', 85.5, '–', 6.3, 6.4, 'Frei', ' '], $rows[1]);
    }

    public function testTenants()
    {
        [$rows, $bold] = $this->sheet('/export/mieter', 'mieter');

        $this->assertTrue($bold);
        $this->assertSame([
            ['Vorname', 'Name', 'Telefon', 'E-Mail', 'Adresse', 'Wohnung'],
            ['Erika', 'Muster', '044 000 00 00', 'erika@example.invalid', 'Eglistrasse 1', '1.01 / 1. OG links'],
        ], $rows);
    }
}
